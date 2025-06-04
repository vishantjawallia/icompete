<?php

namespace App\Http\Controllers;

use App\Models\CoinBalance;
use App\Models\CoinPayment;
use App\Models\CoinSetting;
use App\Models\CoinTransaction;
use App\Traits\ApiResponse;
use Auth;
use Illuminate\Http\Request;
use Purify;

class CoinController extends Controller
{
    use ApiResponse;

    /**
     * Fetch coin details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $settings = cache()->remember('coin_settings', 3600, function () {
            return CoinSetting::all();
        });

        $coinRates = $settings->where('key', 'coin_rates')->first()->value ?? [];
        $coinUsage = $settings->where('key', 'coin_usage')->first()->value ?? [];
        $coinReward = $settings->where('key', 'coin_rewards')->first()->value ?? [];

        return response()->json([
            'status'  => 'success',
            'message' => 'Coin details',
            'data'    => [
                'prices'  => $coinRates,
                'usage'   => $coinUsage,
                'rewards' => $coinReward,
            ],
        ]);
    }

    /**
     * Fetch user balance
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function balance(Request $request)
    {
        $user = Auth::user();
        $userCoin = CoinBalance::firstOrCreate(['user_id' => $user->id]);
        $data = [
            'balance'      => ($userCoin->balance),
            'total_earned' => ($userCoin->total_earned),
        ];

        return $this->successResponse('Balance fetched successfully', $data);
    }

    /**
     * Fetch coin earnings history
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $user = Auth::user();

        $result = $user->coinTransaction()->latest()->paginate(50);

        $objectData = $result->getCollection()->transform(function ($transaction) {
            return [
                'id'          => $transaction->id,
                'user_id'     => $transaction->user_id,
                'code'        => $transaction->code,
                'coins'       => format_number($transaction->coins),
                'amount'      => format_number($transaction->amount),
                'service'     => $transaction->service,
                'type'        => $transaction->type,
                'description' => $transaction->description,
                'balance'     => $transaction->newbal,
                'created_at'  => $transaction->created_at,
            ];
        });

        return $this->paginatedResponse('Coin Transactions', $objectData, $result);
    }

    /**
     * Handle coin purchase
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchase(Request $request)
    {
        // validate request
        $validated = $request->validate([
            'amount'  => 'numeric|required|in:100,500,1000,5000,10000,20000',
            'gateway' => 'required|string|in:paypal,flutterwave',
        ], [
            'amount.in' => 'Amount is invalid. Select from 100, 500, 1000, 5000, 10000 or 20000 coins',
        ]);
        // purify request
        $req = Purify::clean($validated);
        $price = $this->coinRates($req['amount']);

        if (! $price) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid coin amount provided',
            ], 400);
        }
        // create tempory transaction
        $user = Auth::user();
        $ref = getTrx(13);
        $payment = CoinPayment::create([
            'user_id'    => $user->id,
            'gateway'    => $req['gateway'],
            'coins'      => $req['amount'],
            'amount'     => $price,
            'reference'  => $ref,
            'status'     => 'pending',
            'expires_at' => now()->addDays(5),
        ]);
        $details['amount'] = $price;
        $details['name'] = $user->full_name;
        $details['phone'] = $user->phone;
        $details['email'] = $user->email;
        $details['reference'] = $ref;
        $details['description'] = "Payment for {$request->amount} coins";
        $details['gateway'] = $req['gateway'];
        // update payment
        $payment->update(['data' => $details]);
        // payment gateways
        $c = app(PaymentController::class);

        if ($request->gateway == 'paypal') {
            return $c->initPaypal($details);
        } elseif ($request->gateway == 'flutterwave') {
            return $c->initFlutter($details);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Unable to initiate payment',
        ], 400);
    }

    // finalize payment after webhook
    public function completePurchase($payment, $response = null)
    {
        if ($payment->status == 'completed') {
            return to_route('pay.success')->withMessage('Payment was completed successfully');
        }
        // get user
        $user = $payment->user;
        $coin = $user->coins;
        // update payment
        $payment->status = 'completed';
        $payment->response = $response;
        $payment->save();
        // create coin transaction
        $transaction = CoinTransaction::create([
            'user_id'     => $payment->user_id,
            'coins'       => $payment->coins,
            'amount'      => $payment->amount,
            'type'        => 'credit',
            'service'     => 'purchase',
            'gateway'     => $payment->gateway,
            'code'        => $payment->reference,
            'response'    => $response,
            'description' => $payment->data['description'],
            'oldbal'      => $coin->balance,
            'newbal'      => $coin->balance + $payment->coins,
        ]);
        // update coin balance and total earned
        creditUser($coin, $payment->coins);
        $coin->increment('total_earned', $payment->coins);
        // send notification
        sendNotification('COIN_PURCHASE', $user, [
            'username'       => $user->username,
            'name'           => $user->full_name,
            'coin_amount'    => $transaction->coins,
            'amount'         => $transaction->amount,
            'transaction_id' => $transaction->code,
            'coin_balance'   => $transaction->newbal,
        ], [
            'user_id'        => $user->id,
            'transaction_id' => $transaction->id,
            'type'           => 'COIN_PURCHASE',
        ]);
        // admin notification
        notifyAdmin('ADMIN_PAYMENT_RECEIVED', [
            'username'         => $user->username,
            'amount'           => $transaction->amount,
            'coins'            => $transaction->coins,
            'transaction_id'   => $transaction->code,
            'payment_method'   => $payment->gateway,
            'payment_date'     => show_datetime($transaction->created_at),
            'description'      => $transaction->description,
            'transaction_link' => route('admin.coin.transactions') . '?search=' . $transaction->id,
            'link'             => route('admin.coin.transactions') . '?search=' . $transaction->id,
        ], [
            'user_id'        => $user->id,
            'transaction_id' => $transaction->id,
            'type'           => 'ADMIN_NEW_PAYMENT',
        ]);

        // return to success page
        return to_route('pay.success')->withMessage('Payment was completed successfully');

    }

    /**
     * Retrieve the price for a given number of coins.
     *
     * @param  int  $coin  The number of coins to get the price for.
     * @return float|null The price corresponding to the given number of coins, or null if not found.
     */
    private function coinRates($coin)
    {
        $settings = cache()->remember('coin_settings', 3600, function () {
            return CoinSetting::all();
        });

        $coinRates = $settings->where('key', 'coin_rates')->first()->value ?? [];

        foreach ($coinRates as $rate) {
            if ($rate['coins'] == $coin) {
                return $rate['price'];
            }
        }

    }
}
