<?php

namespace App\Services;

use App\Exceptions\AffiliatePayoutException;
use App\Models\ActivityLog;
use App\Models\AffiliateWithdrawal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * The one place an affiliate payout changes hands.
 *
 * Before this service existed the same two movements were implemented three times
 * (AffiliateController, AffiliateWithdrawalController and its bulk loop) and the copies disagreed:
 * approving debited `balance` but never touched `pending_balance`, while rejecting credited
 * `balance` back and also decremented `pending_balance` — so rejecting a payout that had never been
 * reserved invented the money (+400 on a 1000 balance) and left `pending_balance` negative.
 *
 * The rule, in one sentence: **a payout is paid out of a bucket that must hold it, and a rejection
 * only returns what was actually held.**
 *
 * `pending_balance` is the reservation bucket. Nothing writes it today (there is no
 * affiliate-initiated request flow yet), so in practice it is zero and every payout comes out of
 * `balance`. The reservation branch is implemented anyway, in the one place, so that the day a
 * request form moves money into `pending_balance` this service already does the right thing:
 * approve consumes the hold, reject gives it back, and neither can drive a bucket negative.
 */
class AffiliatePayoutService
{
    /**
     * Approve a pending withdrawal and pay it out.
     *
     * @throws AffiliatePayoutException
     */
    public function approve(int $withdrawalId, ?string $note = null, ?string $transactionId = null): AffiliateWithdrawal
    {
        return DB::transaction(function () use ($withdrawalId, $note, $transactionId) {
            /** @var AffiliateWithdrawal $withdrawal */
            $withdrawal = AffiliateWithdrawal::whereKey($withdrawalId)->lockForUpdate()->first();

            if (! $withdrawal) {
                throw new AffiliatePayoutException('That payout no longer exists.');
            }

            $this->assertPending($withdrawal, 'approved');
            $this->assertAmount($withdrawal);

            $affiliate = $withdrawal->affiliate()->lockForUpdate()->first();

            if (! $affiliate) {
                throw new AffiliatePayoutException('This payout has no affiliate attached, so it cannot be paid.');
            }

            $amount = (float) $withdrawal->amount;
            $pending = (float) $affiliate->pending_balance;
            $balance = (float) $affiliate->balance;

            if ($pending >= $amount) {
                // A reservation was held for this payout: consume the hold, leave balance alone.
                $affiliate->pending_balance = round($pending - $amount, 2);
            } else {
                if ($balance < $amount) {
                    $bucket = $pending > 0
                        ? sprintf('only ৳%s of it is reserved and ৳%s sits in the balance', number_format($pending, 2), number_format($balance, 2))
                        : sprintf('their balance is ৳%s', number_format($balance, 2));

                    throw new AffiliatePayoutException(sprintf(
                        'Cannot pay ৳%s: %s. Top up the affiliate balance first, or reject the payout.',
                        number_format($amount, 2),
                        $bucket
                    ));
                }
                $affiliate->balance = round($balance - $amount, 2);
                // Release any partial hold so the pending figure cannot drift.
                $affiliate->pending_balance = round(max(0, $pending - $amount), 2);
            }

            $affiliate->save();

            $withdrawal->status = 'approved';
            $withdrawal->processed_at = now();
            if ($note !== null && $note !== '') {
                $withdrawal->admin_note = $note;
            }
            $withdrawal->save();

            ActivityLog::adminLog('Affiliate payout approved', $withdrawal, Auth::user(), [
                'affiliate_id' => $affiliate->id,
                'amount' => $amount,
                'paid_from' => $pending >= $amount ? 'reserved (pending_balance)' : 'balance',
                'balance_before' => $balance,
                'balance_after' => (float) $affiliate->balance,
                'pending_after' => (float) $affiliate->pending_balance,
                'transaction_id' => $transactionId,
            ]);

            return $withdrawal->fresh();
        });
    }

    /**
     * Reject a pending withdrawal. Money only moves when a reservation was actually held.
     *
     * @throws AffiliatePayoutException
     */
    public function reject(int $withdrawalId, ?string $note = null): AffiliateWithdrawal
    {
        return DB::transaction(function () use ($withdrawalId, $note) {
            /** @var AffiliateWithdrawal $withdrawal */
            $withdrawal = AffiliateWithdrawal::whereKey($withdrawalId)->lockForUpdate()->first();

            if (! $withdrawal) {
                throw new AffiliatePayoutException('That payout no longer exists.');
            }

            $this->assertPending($withdrawal, 'rejected');
            $this->assertAmount($withdrawal);

            $affiliate = $withdrawal->affiliate()->lockForUpdate()->first();
            $amount = (float) $withdrawal->amount;
            $returned = 0.0;

            if ($affiliate) {
                $pending = (float) $affiliate->pending_balance;

                // Only a hold that is actually there can be given back. This is the line that was
                // missing: the old code credited `balance` unconditionally.
                if ($pending >= $amount) {
                    $affiliate->pending_balance = round($pending - $amount, 2);
                    $affiliate->balance = round((float) $affiliate->balance + $amount, 2);
                    $affiliate->save();
                    $returned = $amount;
                }
            }

            $withdrawal->status = 'rejected';
            $withdrawal->processed_at = now();
            if ($note !== null && $note !== '') {
                $withdrawal->admin_note = $note;
            }
            $withdrawal->save();

            ActivityLog::adminLog('Affiliate payout rejected', $withdrawal, Auth::user(), [
                'affiliate_id' => $affiliate->id ?? null,
                'amount' => $amount,
                'reserved_returned' => $returned,
                'balance_after' => $affiliate ? (float) $affiliate->balance : null,
                'pending_after' => $affiliate ? (float) $affiliate->pending_balance : null,
            ]);

            return $withdrawal->fresh();
        });
    }

    private function assertPending(AffiliateWithdrawal $withdrawal, string $verb): void
    {
        if ($withdrawal->status !== 'pending') {
            throw new AffiliatePayoutException(sprintf(
                'This payout is already %s, so it cannot be %s.',
                $withdrawal->status,
                $verb
            ));
        }
    }

    private function assertAmount(AffiliateWithdrawal $withdrawal): void
    {
        if ((float) $withdrawal->amount <= 0) {
            throw new AffiliatePayoutException('This payout has no amount to move.');
        }
    }
}
