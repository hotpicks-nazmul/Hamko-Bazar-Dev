<?php

namespace App\Services;

use App\Exceptions\RefundException;
use App\Models\CustomerWallet;
use App\Models\Order;
use App\Models\Refund;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The refund chain, in one place.
 *
 * The module used to be a viewer: nothing created a refund, approval flipped the order to
 * "refunded" before any money moved, every transition was unguarded (approve reopened a rejection,
 * process skipped approval, reject walked a processed refund backwards), the amount was never
 * compared to the order, and a refund returned no stock and credited no wallet.
 *
 * Rules implemented here:
 *   request   customer asks on a delivered order, once, inside the configured window, for at most
 *             what is still refundable; auto-approved when the shop says so
 *   approve   pending → approved. Labels only — the order is NOT touched here
 *   reject    pending → rejected, note required
 *   process   approved → processed. This is the money step: the order becomes refunded/refunded,
 *             the stock it was holding goes back on the shelf, and store-credit refunds pay the
 *             customer's wallet with a ledger row
 */
class RefundService
{
    public function isEnabled(): bool
    {
        return Setting::get('enable_refunds', '0') === '1';
    }

    public function windowDays(): int
    {
        return max(1, (int) Setting::get('refund_within_days', 30));
    }

    /** What is still refundable on an order: its total minus every refund that is not rejected. */
    public function remainingFor(Order $order): float
    {
        $used = (float) Refund::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'approved', 'processed'])
            ->sum('refund_amount');

        return max(0.0, round((float) $order->total - $used, 2));
    }

    /** The one refund that is still in flight on this order, if any. */
    public function openRefundFor(Order $order): ?Refund
    {
        return Refund::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'approved'])
            ->latest('id')
            ->first();
    }

    /**
     * May this order be refunded right now, and why not if not.
     *
     * @return array{ok: bool, reason: ?string, remaining: float}
     */
    public function canRequest(Order $order): array
    {
        $remaining = $this->remainingFor($order);

        if (! $this->isEnabled()) {
            return ['ok' => false, 'reason' => 'Refunds are not enabled for this shop.', 'remaining' => $remaining];
        }

        if ($order->status !== 'delivered') {
            return ['ok' => false, 'reason' => 'A refund can be requested once the order has been delivered.', 'remaining' => $remaining];
        }

        if ($open = $this->openRefundFor($order)) {
            return ['ok' => false, 'reason' => "Refund {$open->refund_number} is already open for this order.", 'remaining' => $remaining];
        }

        if ($remaining <= 0) {
            return ['ok' => false, 'reason' => 'This order has already been refunded in full.', 'remaining' => 0.0];
        }

        $days = $this->windowDays();
        $from = $order->delivered_at ?: $order->updated_at;
        if ($from && $from->copy()->addDays($days)->isPast()) {
            return ['ok' => false, 'reason' => "The refund window of {$days} days from delivery has passed.", 'remaining' => $remaining];
        }

        return ['ok' => true, 'reason' => null, 'remaining' => $remaining];
    }

    /**
     * Open a refund request. Throws RefundException with the customer-facing reason.
     *
     * The amount is computed here from the order and its earlier refunds — never taken from the
     * request, which is how a 10× refund was possible before.
     */
    public function request(Order $order, ?int $userId, string $reason, ?string $details = null): Refund
    {
        if (! array_key_exists($reason, Refund::getReasonOptions())) {
            throw new RefundException('Please choose a valid refund reason.');
        }

        $check = $this->canRequest($order);
        if (! $check['ok']) {
            throw new RefundException($check['reason']);
        }

        $auto = Setting::get('auto_approve_refunds', '0') === '1';

        return Refund::create([
            'order_id' => $order->id,
            'user_id' => $userId,
            'refund_amount' => $check['remaining'],
            'reason' => $reason,
            'reason_details' => $details,
            'status' => $auto ? 'approved' : 'pending',
            'processed_at' => $auto ? now() : null,
        ]);
    }

    /** pending → approved. Labels only; the order's money state is untouched until process(). */
    public function approve(Refund $refund, ?string $note = null, ?int $adminId = null): void
    {
        if (! $refund->canTransitionTo('approved') || $refund->status !== 'pending') {
            throw new RefundException("A refund that is {$refund->status} cannot be approved.");
        }

        $total = (float) ($refund->order->total ?? 0);
        if ((float) $refund->refund_amount <= 0) {
            throw new RefundException('The refund amount must be greater than zero.');
        }
        if ($total > 0 && (float) $refund->refund_amount > $total) {
            throw new RefundException('The refund amount cannot be more than the order total.');
        }

        $refund->update([
            'status' => 'approved',
            'admin_note' => $this->appendNote($refund->admin_note, $note),
            'processed_at' => now(),
            'processed_by' => $adminId,
        ]);
    }

    /** pending → rejected. A note is mandatory: the customer is told why. */
    public function reject(Refund $refund, string $note, ?int $adminId = null): void
    {
        if (! $refund->canTransitionTo('rejected') || $refund->status !== 'pending') {
            throw new RefundException("A refund that is {$refund->status} cannot be rejected.");
        }

        $note = trim($note);
        if ($note === '') {
            throw new RefundException('A reason is required to reject a refund.');
        }

        $refund->update([
            'status' => 'rejected',
            'admin_note' => $this->appendNote($refund->admin_note, $note),
            'processed_at' => now(),
            'processed_by' => $adminId,
        ]);
    }

    /**
     * approved → processed. The money step: order refunded, stock returned, wallet credited when
     * the shop refunds as store credit.
     */
    public function process(Refund $refund, ?string $note = null, ?int $adminId = null): void
    {
        if (! $refund->canTransitionTo('processed') || $refund->status !== 'approved') {
            throw new RefundException("A refund that is {$refund->status} cannot be processed — approve it first.");
        }

        DB::transaction(function () use ($refund, $note, $adminId) {
            $refund->update([
                'status' => 'processed',
                'admin_note' => $this->appendNote($refund->admin_note, $note),
                'processed_at' => now(),
                'processed_by' => $adminId,
            ]);

            $order = $refund->order;
            if (! $order) {
                return;
            }

            // One implementation for the status move and its stock consequence: the order stops
            // holding what it held, so the shelf gets it back (and a second refund on the same
            // order finds nothing left to return).
            app(OrderStatusService::class)->apply($order, 'refunded', 'refunded');

            if (Setting::get('refund_method', 'original_payment') === 'store_credit' && $order->user_id) {
                $this->creditWallet((int) $order->user_id, (float) $refund->refund_amount, $refund);
            }
        });
    }

    /** Put the refund into the customer's wallet, with the ledger row the schema always expected. */
    protected function creditWallet(int $userId, float $amount, Refund $refund): void
    {
        if ($amount <= 0) {
            return;
        }

        $user = User::lockForUpdate()->find($userId);
        if (! $user) {
            return;
        }

        $newBalance = (float) $user->wallet_balance + $amount;
        $user->wallet_balance = $newBalance;
        $user->save();

        CustomerWallet::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => 'credit',
            'source' => 'refund',
            'description' => 'Refund ' . $refund->refund_number . ' for order ' . ($refund->order->order_number ?? ''),
            'reference_id' => $refund->refund_number,
            'balance_after' => $newBalance,
        ]);
    }

    /** Append a note without inventing blank lines when it is empty. */
    protected function appendNote(?string $existing, ?string $note): ?string
    {
        $note = trim((string) $note);
        if ($note === '') {
            return $existing;
        }

        $existing = trim((string) $existing);

        return $existing === '' ? $note : $existing . "\n" . $note;
    }
}
