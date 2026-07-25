<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Restore the authenticated user from the order before processing callbacks.
     * SSLCommerz POST callbacks arrive cross-site without the session cookie
     * due to SameSite=Lax. We log the user back in from the order data.
     */
    private function restoreUserFromOrder(string $orderNumber): ?Order
    {
        $order = Order::where('order_number', $orderNumber)->first();
        if ($order && $order->user_id) {
            // Log the user in for this request only (no cookie, no session persistence)
            Auth::onceUsingId($order->user_id);
        }
        return $order;
    }

    /**
     * Create bKash payment (called from checkout frontend JS).
     */
    public function bkashCreate(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        
        $result = $this->paymentService->createBkashPayment($order);
        
        return response()->json($result);
    }

    /**
     * Execute bKash payment after callback.
     */
    public function bkashExecute(Request $request)
    {
        $result = $this->paymentService->executeBkashPayment($request->paymentID);

        if (isset($result['transactionStatus']) && $result['transactionStatus'] === 'Completed') {
            $order = Order::where('order_number', $request->order_number ?? $result['merchantInvoiceNumber'])->first();
            if ($order) {
                $order->update([
                    'payment_status' => 'paid',
                    'transaction_id' => $result['trxID'] ?? $request->paymentID,
                ]);
                $this->clearCartAndStock($order);
                Log::info('bKash payment completed', [
                    'order_number' => $order->order_number,
                    'trxID' => $result['trxID'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'transactionStatus' => 'Completed',
                    'order_id' => $order->id,
                ]);
            }
        }

        return response()->json($result);
    }

    /**
     * bKash callback — user returns from bKash app.
     */
    public function bkashCallback(Request $request, $order_number)
    {
        $paymentID = $request->paymentID;
        $status = $request->status;

        Log::info('bKash callback received', [
            'order_number' => $order_number,
            'paymentID' => $paymentID,
            'status' => $status,
        ]);

        $order = $this->restoreUserFromOrder($order_number);

        if (!$order) {
            Log::error('bKash callback: Order not found', ['order_number' => $order_number]);
            return redirect()->route('checkout.cancel')->with('error', 'Order not found.');
        }

        // If bKash reports success, execute the payment to capture funds
        if ($status === 'success' && $paymentID) {
            $result = $this->paymentService->executeBkashPayment($paymentID);

            if (isset($result['transactionStatus']) && $result['transactionStatus'] === 'Completed') {
                $order->update([
                    'payment_status' => 'paid',
                    'transaction_id' => $result['trxID'] ?? $paymentID,
                ]);
                $this->clearCartAndStock($order);

                Log::info('bKash payment success', [
                    'order_number' => $order_number,
                    'trxID' => $result['trxID'] ?? null,
                ]);

                return redirect()->route('checkout.success', $order->id);
            }

            // Execute didn't complete — query status to double-check
            if (isset($result['error'])) {
                $queryResult = $this->paymentService->queryBkashPayment($paymentID);
                if (isset($queryResult['transactionStatus']) && $queryResult['transactionStatus'] === 'Completed') {
                    $order->update([
                        'payment_status' => 'paid',
                        'transaction_id' => $queryResult['trxID'] ?? $paymentID,
                    ]);
                    $this->clearCartAndStock($order);
                    return redirect()->route('checkout.success', $order->id);
                }
                Log::error('bKash execute failed after success callback', [
                    'order_number' => $order_number,
                    'execute_result' => $result,
                    'query_result' => $queryResult ?? null,
                ]);
            }
        }

        // Payment failed or cancelled
        Log::warning('bKash payment not completed', [
            'order_number' => $order_number,
            'status' => $status,
            'paymentID' => $paymentID,
        ]);

        return redirect()->route('checkout.cancel')->with('error', 'Payment was not completed. Please try again.');
    }

    /**
     * Query bKash payment status.
     */
    public function bkashQuery(Request $request)
    {
        $result = $this->paymentService->queryBkashPayment($request->paymentID);
        return response()->json($result);
    }

    /**
     * Initiate SSLCommerz payment (called from checkout frontend JS).
     */
    public function sslcommerzCreate(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        
        $result = $this->paymentService->createSslcommerzPayment($order);
        
        return response()->json($result);
    }

    /**
     * SSLCommerz success callback.
     */
    public function sslcommerzSuccess(Request $request)
    {
        Log::info('SSLCommerz success callback', $request->all());

        $order = $this->restoreUserFromOrder($request->tran_id);

        if (!$order) {
            Log::error('SSLCommerz success: Order not found', ['tran_id' => $request->tran_id]);
            return redirect()->route('checkout.cancel')->with('error', 'Order not found.');
        }

        // Validate IPN hash for security when available
        if ($request->has('verify_hash') && $request->has('verify_key')) {
            if (!$this->paymentService->validateSslcommerzIpn($request->all())) {
                Log::warning('SSLCommerz hash validation failed', ['tran_id' => $request->tran_id]);
            }
        }

        if ($request->status === 'VALID') {
            // Only process if not already paid
            if ($order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'transaction_id' => $request->bank_tran_id,
                ]);
                $this->clearCartAndStock($order);
            }
            
            Log::info('SSLCommerz payment success', [
                'order_number' => $order->order_number,
                'bank_tran_id' => $request->bank_tran_id,
            ]);
            
            return redirect()->route('checkout.success', $order->id);
        }

        Log::warning('SSLCommerz success with non-VALID status', [
            'status' => $request->status,
            'tran_id' => $request->tran_id,
        ]);

        return redirect()->route('checkout.cancel')->with('error', 'Payment was not validated.');
    }

    /**
     * SSLCommerz fail callback.
     */
    public function sslcommerzFail(Request $request)
    {
        Log::warning('SSLCommerz payment failed', [
            'tran_id' => $request->tran_id,
            'error' => $request->error,
        ]);

        $this->restoreUserFromOrder($request->tran_id);

        return redirect()->route('checkout.index')->with('error', 'Payment failed. Please try again.');
    }

    /**
     * SSLCommerz cancel callback.
     */
    public function sslcommerzCancel(Request $request)
    {
        Log::info('SSLCommerz payment cancelled', ['tran_id' => $request->tran_id]);

        $this->restoreUserFromOrder($request->tran_id);

        return redirect()->route('home')->with('info', 'Payment was cancelled. You can retry checkout anytime.');
    }

    /**
     * SSLCommerz IPN (Instant Payment Notification) handler.
     */
    public function sslcommerzIpn(Request $request)
    {
        Log::info('SSLCommerz IPN received', $request->all());

        $order = Order::where('order_number', $request->tran_id)->first();

        if (!$order) {
            Log::error('SSLCommerz IPN: Order not found', ['tran_id' => $request->tran_id]);
            return response('ORDER_NOT_FOUND');
        }

        // Validate hash for security
        if ($request->has('verify_hash') && $request->has('verify_key')) {
            if (!$this->paymentService->validateSslcommerzIpn($request->all())) {
                Log::error('SSLCommerz IPN hash validation failed', ['tran_id' => $request->tran_id]);
                return response('HASH_VALIDATION_FAILED');
            }
        }

        // Only update if not already paid (prevent double processing)
        if ($order->payment_status !== 'paid' && $request->status === 'VALID') {
            $order->update([
                'payment_status' => 'paid',
                'transaction_id' => $request->bank_tran_id,
            ]);
            $this->clearCartAndStock($order);

            Log::info('SSLCommerz IPN processed', [
                'order_number' => $order->order_number,
                'bank_tran_id' => $request->bank_tran_id,
            ]);
        }

        return response('OK');
    }

    /**
     * Clear the user's cart and decrement product stock after successful payment.
     */
    private function clearCartAndStock(Order $order): void
    {
        try {
            $cart = Cart::where('user_id', $order->user_id)->first();
            if ($cart && !$cart->isEmpty()) {
                $cartItems = $cart->items ?? [];
                $cart->clear();

                foreach ($cartItems as $item) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->decrement('quantity', $item['quantity']);
                    }
                }

                Log::info('Cart cleared after payment', [
                    'order_number' => $order->order_number,
                    'items_count' => count($cartItems),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to clear cart after payment', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
