<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    // Settings
    public function settings()
    {
        return view('admin.withdrawals.settings');
    }

    public function pending(Request $request)
    {
        $query = Withdrawal::whereIn('status', ['pending', 'processing'])->orderByDesc('id');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->search($search);
        }

        $withdraw = $query->paginate(30);

        return view('admin.withdrawals.index', [
            'title'     => 'Pending Withdrawals',
            'withdraws' => $withdraw,
        ]);
    }

    public function history(Request $request)
    {
        $query = Withdrawal::orderByDesc('id');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->search($search);
        }
        $type = $request->input('type') ?? '';

        if ($request->has('type') && $type != null) {
            $query->whereStatus($type);
        }
        $title = ucfirst($type) . ' Withdrawals';
        $withdraw = $query->paginate(30);

        return view('admin.withdrawals.index', [
            'title'     => $title,
            'withdraws' => $withdraw,
        ]);
    }

    public function approve($id)
    {
        $withdraw = Withdrawal::findOrFail($id);

        // return error if it's approved.
        if (! in_array($withdraw->status, ['pending', 'processing'])) {
            return back()->withError('You can not approve this withdrawal');
        }
        // complete withdrawals
        $this->completeWithdrawal($withdraw);

        return back()->withSuccess('Withdrawal was approved successfully');
    }

    // reject withdrawal
    public function reject($id)
    {
        $withdraw = Withdrawal::findOrFail($id);

        // return error if it's not pending or processing.
        if (! in_array($withdraw->status, ['pending', 'processing'])) {
            return back()->withError('You can not reject this Withdraw Request');
        }
        // update withdrawal status
        $withdraw->update(['status' => 'rejected']);
        $user = $withdraw->user;
        // refund user;
        $userCoin = $user->coins;
        $userCoin->balance += $withdraw->coins;
        $userCoin->save();
        $msg = 'Your withdrawal of ' . format_price($withdraw->amount) . ' was not successful';
        // send notification
        sendNotification(
            'WITHDRAW_REJECTED',
            $user,
            [
                'name'          => $user->name,
                'username'      => $user->username,
                'amount'        => format_price($withdraw->amount),
                'coins'         => ($withdraw->coins),
                'method'        => ($withdraw->payment_method),
                'withdraw_date' => show_datetime($withdraw->updated_at),
                'withdraw_code' => ($withdraw->code),
                'reject_reason' => '',
            ],
            [
                'user_id'       => $user->id,
                'withdrawal_id' => $withdraw->id,
                'amount'        => format_price($withdraw->amount),
                'coins'         => ($withdraw->coins),
                'type'          => 'WITHDRAWAL_REJECTED',
            ]

        );

        return back()->withSuccess('Withdrawal was rejected successfully');
    }

    private function completeWithdrawal($withdraw)
    {
        $withdraw->update(['status' => 'completed', 'approval_date' => now()]);
        $user = $withdraw->user;
        $userCoin = $user->coins;
        // transaction
        $ref = getTrx(13);
        $transaction = CoinTransaction::create([
            'user_id'     => $user->id,
            'coins'       => $withdraw->coins,
            'amount'      => $withdraw->amount,
            'type'        => 'debit',
            'service'     => 'withdrawal',
            'code'        => $ref,
            'response'    => null,
            'description' => 'Successful withdrawal of ' . format_price($withdraw->amount),
            'newbal'      => $userCoin->balance,
            'oldbal'      => $userCoin->balance,
        ]);
        // send notification
        sendNotification(
            'WITHDRAW_APPROVED',
            $user,
            [
                'name'            => $user->name,
                'username'        => $user->username,
                'amount'          => format_price($withdraw->amount),
                'fee'             => format_price($withdraw->fee),
                'coins'           => ($withdraw->coins),
                'method'          => ($withdraw->payment_method),
                'withdraw_date'   => show_datetime($withdraw->updated_at),
                'withdraw_code'   => ($withdraw->code),
                'processing_time' => '2',
            ],
            [
                'user_id'       => $user->id,
                'withdrawal_id' => $withdraw->id,
                'amount'        => format_price($withdraw->amount),
                'coins'         => ($withdraw->coins),
                'type'          => 'WITHDRAWAL_APPROVED',
            ]
        );
    }
}
