<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The single way stock moves.
 *
 * Every path used to touch products.quantity on its own — checkout, the payment callback,
 * customer cancel, the admin order screens, quotations, subscriptions — mostly with
 * $product->decrement('quantity'), which is a query-builder update: it fires no model events, so
 * Product::saving() never re-derived stock_status (a sold-out product kept its "in stock" badge on
 * the grid while the product page said Out of Stock), it never set stock_update_date, and it never
 * wrote inventory_history, so a table built to explain movements only ever saw manual edits.
 *
 * Everything now goes through here: one save() per movement (so the status hook runs and the
 * low-stock notifications fire), one history row with the before/after it actually applied, and a
 * refusal instead of a negative shelf when the caller did not ask to oversell.
 */
class StockService
{
    /** History action types the Stock History screen filters on. */
    public const STOCK_IN = 'stock_in';
    public const STOCK_OUT = 'stock_out';
    public const ADJUSTMENT = 'adjustment';

    /**
     * Take stock off the shelf.
     *
     * @param  bool  $allowNegative  Set by callers that have already taken money (a payment
     *                               callback cannot refuse): the movement is applied and logged,
     *                               the shelf is allowed to go negative so the shortage is visible.
     * @return int  quantity actually deducted
     *
     * @throws InsufficientStockException when the shelf cannot fill the request
     */
    public function deduct(
        Product $product,
        int $quantity,
        ?string $reason = null,
        bool $allowNegative = false,
        ?int $performedBy = null
    ): int {
        $quantity = (int) $quantity;

        if ($quantity <= 0) {
            return 0;
        }

        $this->refresh($product, true);

        $available = (int) $product->quantity;

        if ($available < $quantity) {
            if (! $allowNegative) {
                throw new InsufficientStockException($product, $quantity, $available);
            }

            Log::warning('Stock taken below zero', [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'available' => $available,
                'requested' => $quantity,
                'reason' => $reason,
            ]);
        }

        $this->move($product, $available - $quantity, self::STOCK_OUT, -$quantity, $reason ?? 'Stock issued', $performedBy);

        return $quantity;
    }

    /**
     * Put stock back (cancel, refund, removed order line, returned goods).
     *
     * @return int quantity actually returned
     */
    public function restore(
        Product $product,
        int $quantity,
        ?string $reason = null,
        ?int $performedBy = null
    ): int {
        $quantity = (int) $quantity;

        if ($quantity <= 0) {
            return 0;
        }

        $this->refresh($product);

        $this->move($product, (int) $product->quantity + $quantity, self::STOCK_IN, $quantity, $reason ?? 'Stock returned', $performedBy);

        return $quantity;
    }

    /**
     * Set the shelf to an absolute number and record the real difference.
     *
     * @return int signed change actually applied
     */
    public function setQuantity(
        Product $product,
        int $quantity,
        ?string $reason = null,
        ?int $performedBy = null
    ): int {
        $quantity = max(0, (int) $quantity);

        $this->refresh($product);

        $before = (int) $product->quantity;
        $change = $quantity - $before;

        $this->move($product, $quantity, self::ADJUSTMENT, $change, $reason ?? 'Stock set manually', $performedBy);

        return $change;
    }

    /**
     * Check a basket before it is turned into an order.
     *
     * @param  array<int, array{product: Product|null, quantity: int}>  $lines
     * @return array<int, string> human readable shortfalls, empty when everything fits
     */
    public function shortfalls(array $lines): array
    {
        $short = [];

        foreach ($lines as $line) {
            $product = $line['product'] ?? null;
            $wanted = (int) ($line['quantity'] ?? 0);

            if (! $product) {
                $short[] = 'A product in the basket is no longer available.';
                continue;
            }

            if ($wanted > (int) $product->quantity) {
                $short[] = "{$product->name}: {$product->quantity} left, {$wanted} requested.";
            }
        }

        return $short;
    }

    /**
     * Did this order ever take stock off the shelf?
     *
     * COD orders take it when they are placed, gateway orders when the payment lands. An unpaid
     * gateway order that is cancelled never took any, so returning it would invent stock.
     */
    public function orderHoldsStock(\App\Models\Order $order): bool
    {
        return $order->payment_method === 'cod' || $order->payment_status === 'paid';
    }

    /**
     * Return every line of an order to the shelf.
     *
     * @return int units returned
     */
    public function restoreOrder(\App\Models\Order $order, string $reason, ?int $performedBy = null): int
    {
        $order->loadMissing('items');
        $returned = 0;

        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $product = Product::find($item->product_id);
            if (! $product) {
                continue;
            }

            $returned += $this->restore($product, (int) $item->quantity, $reason, $performedBy);
        }

        return $returned;
    }

    /**
     * Take every line of an order off the shelf (used when an order comes back from cancelled).
     *
     * @return int units taken
     */
    public function deductOrder(\App\Models\Order $order, string $reason, bool $allowNegative = false, ?int $performedBy = null): int
    {
        $order->loadMissing('items');
        $taken = 0;

        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $product = Product::find($item->product_id);
            if (! $product) {
                continue;
            }

            $taken += $this->deduct($product, (int) $item->quantity, $reason, $allowNegative, $performedBy);
        }

        return $taken;
    }

    /**
     * Re-read the shelf from the database before computing a movement.
     *
     * @param  bool  $lock  take a write lock when we are inside a transaction (checkout does)
     */
    private function refresh(Product $product, bool $lock = false): void
    {
        if (! $product->exists) {
            return;
        }

        $query = DB::table('products')->where('id', $product->id);

        if ($lock && DB::transactionLevel() > 0) {
            $query->lockForUpdate();
        }

        $current = $query->value('quantity');

        if ($current !== null && (int) $current !== (int) $product->quantity) {
            // The instance someone handed us is stale (loaded before an update, or reused from a
            // collection). Arithmetic on a stale number is how a deduction silently becomes a
            // different deduction, so take the value that is actually on the shelf.
            $product->quantity = (int) $current;
            $product->syncOriginalAttribute('quantity');
        }
    }

    /**
     * Apply one movement: quantity + stock_update_date on the model (so Product::saving()
     * derives stock_status and the low-stock notifications fire), then the history row.
     */
    private function move(
        Product $product,
        int $after,
        string $actionType,
        int $change,
        string $reason,
        ?int $performedBy
    ): void {
        $before = (int) $product->quantity;

        $product->quantity = $after;
        $product->stock_update_date = now();
        $product->save();

        DB::table('inventory_history')->insert([
            'product_id' => $product->id,
            'action_type' => $actionType,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'quantity_change' => $change,
            'reason' => mb_substr($reason, 0, 255),
            'performed_by' => $performedBy ?? Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
