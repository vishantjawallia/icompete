<?php

namespace App\Http\Controllers;

use App\Models\CoinPayment;
use App\Services\FlutterwaveService;
use App\Services\PayPalService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $payPalService;

    protected $flutterService;

    public function __construct(PayPalService $payPalService, FlutterwaveService $flutterService)
    {
        $this->payPalService = $payPalService;
        $this->flutterService = $flutterService;

    }

    // paypal init
    public function initPaypal($details)
    {
        $amount = $details['amount'];
        $details['returnUrl'] = route('paypal.success');
        $details['cancelUrl'] = route('paypal.cancel');

        try {
            $payment = $this->payPalService->createPayment($amount, 'USD', $details);
            // Redirect to PayPal for payment approval
            foreach ($payment['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return response()->json([
                        'status'  => 'success',
                        'gateway' => 'paypal',
                        'message' => 'Payment Link generated successfully',
                        'link'    => $link['href'],
                    ], 200);
                }
            }

            return response()->json([
                'status'  => 'error',
                'gateway' => 'paypal',
                'message' => 'Payment not initiated',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'gateway' => 'paypal',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function initFlutter($details)
    {
        $amount = $details['amount'];
        $currency = 'USD'; // or any other currency you are using

        // return error if amount is less than 1
        if ($amount < 1) {
            return response()->json([
                'status'  => 'error',
                'gateway' => 'flutterwave',
                'message' => 'Amount cannot be less than 1' . $currency,
            ], 500);
        }

        try {
            $payment = $this->flutterService->createPayment($amount, $details, $currency);

            // Redirect to Flutterwave for payment approval
            if (isset($payment['data']['link'])) {
                return response()->json([
                    'status'  => 'success',
                    'gateway' => 'flutterwave',
                    'message' => 'Payment Link generated successfully',
                    'link'    => $payment['data']['link'],
                ], 200);
            }

            return response()->json([
                'status'  => 'error',
                'gateway' => 'flutterwave',
                'message' => 'Payment not initiated',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'gateway' => 'flutterwave',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // paypal success
    public function paypalSuccess(Request $request)
    {
        $orderId = $request->input('token');

        if (empty($orderId)) {
            return $this->failedPayment('Invalid Payment');
        }

        try {
            // First, verify the order details
            $orderDetails = $this->payPalService->getOrderDetails($orderId);
            // save response to log
            $logFile = 'logs/paypal_log.txt';
            $logMessage = json_encode($orderDetails, JSON_PRETTY_PRINT);
            file_put_contents($logFile, $logMessage, FILE_APPEND);

            // Verify the order is in a valid state
            if ($orderDetails['status'] == 'APPROVED') {
                // confirm payment
                $code = $orderDetails['purchase_units'][0]['custom_id'] ?? null;

                return $this->completePayment($code, $orderDetails);
            }

            return $this->failedPayment('Payment was not approved.');

        } catch (\Exception $e) {
            return $this->failedPayment('We could not process your payment. Please try again or contact support.');
        }
    }

    // flutterwave callback
    public function flutterSuccess(Request $request)
    {
        $status = request()->status;

        if ($status == 'cancelled') {
            return $this->failedPayment('Payment Was Cancelled');
        }
        // get transaction Id
        $transactionID = request()->transaction_id;
        $response = $this->flutterService->getTransactionStatus($transactionID);
        // log response
        $logFile = 'logs/flutterwave_log.txt';
        $logMessage = json_encode($response, JSON_PRETTY_PRINT);
        file_put_contents($logFile, $logMessage, FILE_APPEND);

        if ($response['status'] == 'success' && $response['data']['status'] == 'successful') {

            $code = $response['data']['tx_ref'] ?? null;

            return $this->completePayment($code, $response);
        }

        return $this->failedPayment('Payment was not approved.');
    }

    private function completePayment($code, $response)
    {
        // get coin payment
        $payment = CoinPayment::whereReference($code)->first();

        if (! $payment) {
            return to_route('pay.error')->withMessage('This Payment does not exist ');
        }

        if ($payment->status == 'completed') {
            return to_route('pay.success')->withMessage('Payment was completed successfully');
        }
        $c = app(CoinController::class);

        return $c->completePurchase($payment, $response);
    }

    private function failedPayment($message)
    {
        return to_route('pay.error')->withMessage($message);
    }
}
