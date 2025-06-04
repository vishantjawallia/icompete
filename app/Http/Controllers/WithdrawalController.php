<?php

namespace App\Http\Controllers;

use App\Models\CoinBalance;
use App\Models\Withdrawal;
use App\Traits\ApiResponse;
use App\Traits\WithdrawalTrait;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    use ApiResponse, WithdrawalTrait;

    /**
     * Handle a withdrawal request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $minWithdrawal = sys_setting('min_withdraw') ?? 10;
        $maxWithdrawal = sys_setting('max_withdraw') ?? 500;
        $request->validate([
            'amount' => 'required|numeric|min:' . $minWithdrawal . '|max:' . $maxWithdrawal,
            'method' => 'required|string|in:paypal,bank_transfer,gift_card',
        ], [
            'method.required' => 'Please select a withdrawal method.',
            'method.in'       => 'Please choose either PayPal or Bank Transfer.',
        ]);
        $user = \Auth::user();
        $userCoin = CoinBalance::firstOrCreate(['user_id' => $user->id]);
        $balance = $userCoin->balance ?? 0;
        $coinRate = $this->calculateCoins($request->amount);

        // validate identity
        if (! $user->isVerified() && $user->withdrawals()->count() === 0) {
            return $this->errorResponse('Please complete identity verification before your first cash-out', 403);
        }

        if ($balance < $coinRate) {
            return $this->errorResponse('Insufficient balance.', 403);
        }

        if ($request->amount < $minWithdrawal || $request->amount > $maxWithdrawal) {
            return $this->errorResponse("Withdrawal amount must be between $minWithdrawal and $maxWithdrawal.", 403);
        }

        // check if withdrawal is enabled or disabled
        if (sys_setting('withdrawal_status') != 1) {
            return $this->errorResponse('Withdrawals are temporarily disabled. Please try again later.', 503);
        }
        // Check for pending withdrawal
        $pendingWithdrawal = Withdrawal::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingWithdrawal) {
            return $this->errorResponse('You already have a pending withdrawal request.', 403);
        }

        // Check weekly withdrawal limit
        $lastWeekWithdrawal = Withdrawal::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subWeek())->whereNotIn('status', ['canceled'])
            ->exists();

        if ($lastWeekWithdrawal) {
            return $this->errorResponse('You can only withdraw once per week.', 403);
        }

        // First Cash-Out Delay: New users must wait 7-14 days before their first withdrawal.
        if ($user->created_at > now()->subDays(10)) {
            return $this->errorResponse('New users must wait 7-14 days before making their first withdrawal.', 403);
        }

        // check payment details
        $paymentDetails = $this->getPaymentDetails($request->method, $user);

        if (! $paymentDetails) {

            return $this->errorResponse('Payment details not provided for : ' . custom_text($request->method), 403);
        }

        $feeRate = 5; // percentage
        $fee = ($request->amount * $feeRate) / 100;
        $finalAmount = $request->amount - $fee;

        // Rule 12: Fraud detection check
        if ($this->detectFraudulentActivity($user, $request->amount)) {
            return $this->errorResponse('Withdrawal flagged for review', 403);
        }
        // approve small withdrawals
        $status = $request->amount <= 100 ? 'processing' : 'pending';

        // Deduct balance
        $user->coins->decrement('balance', $coinRate);
        $withdrawal = Withdrawal::create([
            'user_id'         => $user->id,
            'amount'          => $finalAmount,
            'coins'           => $coinRate,
            'method'          => $request->method,
            'payment_details' => $paymentDetails,
            'status'          => $status,
            'code'            => getTrx(6),
            'fee'             => $fee,
            'fee_rate'        => $feeRate,
            'newbal'          => $balance - $coinRate,
        ]);
        //  Send notification to admin
        notifyAdmin('ADMIN_WITHDRAW_REQUEST', [
            'username'       => $user->username,
            'amount'         => $withdrawal->amount,
            'coin_amount'    => $coinRate,
            'withdraw_code'  => $withdrawal->code,
            'request_date'   => show_datetime($withdrawal->created_at),
            'payment_method' => $withdrawal->method,
            'user_balance'   => $balance - $coinRate,
            'review_link'    => route('admin.withdrawal.history') . "?search=$withdrawal->id",
            'link'           => route('admin.withdrawal.history') . "?search=$withdrawal->id",

        ], [
            'user_id'     => $user->id,
            'amount'      => $withdrawal->amount,
            'coin_amount' => $coinRate,
            'withdraw_id' => $withdrawal->id,
            'type'        => 'ADMIN_WITHDRAWAL',
        ]);

        return $this->successResponse('Withdrawal submitted successfully.', $withdrawal);
    }

    public function index(Request $request)
    {
        $user = \Auth::user();
        $pp = $request->count ?? 30;
        $page = $request->input('page', 1);
        $query = Withdrawal::where('user_id', $user->id)->orderBy('created_at', 'desc');
        $status = $request->status;

        if ($status) {
            $validStatuses = ['pending', 'approved', 'processing', 'completed', 'rejected','canceled'];

            if (in_array($request->status, $validStatuses)) {
                $query->whereStatus($request->status);
            } else {
                return $this->errorResponse('Invalid status provided.', 400);
            }
        }

        if ($request->search) {
            $query->search($request->search);
        }

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn ($item) => $this->withdrawObject($item),
            $pp,
        );

        return $this->paginatedResponse('Withdrawal retrived', $objectData, $result);
    }

    public function show($id)
    {
        $user = \Auth::user();

        $withdrawal = Withdrawal::where('user_id', $user->id)->find($id);

        if (! $withdrawal) {
            return $this->notFoundResponse('Withdrawal request not found.');
        }

        return $this->successResponse('Withdrawal details', $this->withdrawObject($withdrawal));
    }

    public function cancel($id)
    {
        $user = \Auth::user();

        $withdrawal = Withdrawal::where('user_id', $user->id)->find($id);

        if (! $withdrawal) {
            return $this->notFoundResponse('Withdrawal not found.');
        }

        if ($withdrawal->status != 'pending' && $withdrawal->status != 'processing') {
            return $this->errorResponse('Only Pending withdrawals can be canceled.', 404);
        }

        if ($withdrawal->created_at->diffInHours(now()) > 2) {
            return $this->errorResponse('Cancellation window expired');
        }
        $withdrawal->update([
            'status'      => 'canceled',
            'canceled_at' => now(),
        ]);

        // Refund the user
        $user->coins->increment('balance', $withdrawal->coins);

        return $this->successResponse('Withdrawal canceled.');
    }

    // withdrawal details
    public function details(Request $request)
    {
        $user = $request->user();
        $data = [
            'paypal_email' => $user->paypal_email,
            'bank_details' => $user->bank_details,
        ];

        return $this->successResponse('Withdrawal Details', $data);
    }

    // withdrawal settings
    public function updateSettings(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:paypal,bank_transfer',
        ], [
            'type.required' => 'Payment type is required',
            'type.in'       => 'Payment type must be either paypal or bank transfer',
        ]);
        $user = \Auth::user();

        if ($request->type == 'paypal') {
            // validate request
            $request->validate([
                'paypal_email' => 'email|required',
            ]);
            $user->paypal_email = $request->paypal_email;
            $user->save();

            return $this->successResponse('Paypal Account Added Successfully'); // HTTP 200 OK
        }

        if ($request->type == 'bank_transfer') {
            $request->validate([
                'account_name'   => 'required|string',
                'bank_name'      => 'required|string',
                'bank_code'      => 'nullable|string',
                'account_number' => 'required|numeric',
            ]);
            // account object
            $data = [
                'account_name'   => $request['account_name'],
                'bank_name'      => $request['bank_name'],
                'account_number' => $request['account_number'],
                'bank_code'      => $request['bank_code'],
            ];
            $user->bank_details = $data;
            $user->save();

            return $this->successResponse('Bank Details Added Successfully');
        }
    }
}
