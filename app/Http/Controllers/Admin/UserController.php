<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoinBalance;
use App\Models\CoinTransaction;
use App\Models\User;
use App\Services\NotificationService;
use Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::orderByDesc('id');

        if ($request->has('search')) {
            $query->searchUser($request->search);
        }
        $users = $query->paginate(getPaginate());
        $type = 'all';
        $title = __('All Users');

        return view('admin.users.index', compact('users', 'title', 'type'));
    }

    public function active(Request $request)
    {
        $query = User::whereStatus('active')->orderByDesc('id');

        if ($request->has('search')) {
            $query->searchUser($request->search);
        }
        $users = $query->paginate(getPaginate());
        $type = 'active';
        $title = __('Active Users');

        return view('admin.users.index', compact('users', 'title', 'type'));
    }

    public function banned(Request $request)
    {
        $query = User::where('status', 'banned')->orderByDesc('id');

        if ($request->has('search')) {
            $query->searchUser($request->search);
        }
        $users = $query->paginate(getPaginate());
        $type = 'banned';
        $title = __('Banned Users');

        return view('admin.users.index', compact('users', 'title', 'type'));
    }

    public function emailVerified(Request $request)
    {
        $query = User::where('email_verify', 1)->orderByDesc('id');

        if ($request->has('search')) {
            $query->searchUser($request->search);
        }
        $users = $query->paginate(getPaginate());
        $type = 'everified';
        $title = __('Email Verified Users');

        return view('admin.users.index', compact('users', 'title', 'type'));
    }

    public function emailUnverified(Request $request)
    {
        $query = User::where('email_verify', 0)->orderByDesc('id');

        if ($request->has('search')) {
            $query->searchUser($request->search);
        }
        $users = $query->paginate(getPaginate());
        $type = 'eunverified';
        $title = __('Email Unverified Users');

        return view('admin.users.index', compact('users', 'title', 'type'));
    }

    // Contestant users
    public function contestants(Request $request)
    {
        $query = User::where('role', 'contestant')->orderByDesc('id');

        if ($request->has('search')) {
            $query->searchUser($request->search);
        }
        $users = $query->paginate(getPaginate());
        $type = 'contestant';
        $title = __('Contestant Users');

        return view('admin.users.index', compact('users', 'title', 'type'));
    }

    // Organizer users
    public function organizers(Request $request)
    {
        $query = User::where('role', 'organizer')->orderByDesc('id');

        if ($request->has('search')) {
            $query->searchUser($request->search);
        }
        $users = $query->paginate(getPaginate());
        $type = 'organizer';
        $title = __('Organizer Users');

        return view('admin.users.index', compact('users', 'title', 'type'));
    }

    // Voter users
    public function voters(Request $request)
    {
        $query = User::where('role', 'voter')->orderByDesc('id');

        if ($request->has('search')) {
            $query->searchUser($request->search);
        }
        $users = $query->paginate(getPaginate());
        $type = 'voter';
        $title = __('Voter Users');

        return view('admin.users.index', compact('users', 'title', 'type'));
    }

    // User details
    public function view($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.details', compact('user'));
    }

    // Update user
    public function update($id, Request $request)
    {
        $user = User::findorFail($id);
        $validator = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|email|max:255|unique:users,email,' . $user->id,
            'username'   => 'required|string|max:25|unique:users,username,' . $user->id,
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string|max:255',
            'gender'     => 'required|string|max:105',
            'bio'        => 'nullable|string|max:255',
            'password'   => 'nullable|string|min:8',
        ]);
        $user->email_verify = $request->email_verify ?? 0;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->phone = $request->phone;
        $user->bio = $request->bio;
        $user->gender = $request->gender;

        if ($request->password != null) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return redirect()->back()->withSuccess(__('User updated Successfully.'));
    }

    // User settings
    public function settings()
    {
        return view('admin.users.settings');
    }

    // Update user balance
    public function updateBalance(Request $request, $id)
    {
        $user = User::findorFail($id);
        $request->validate([
            'amount'  => 'required|numeric|min:1',
            'message' => 'required|string',
            'act'     => 'required|in:add,sub',
        ]);
        $reference = getTrx(13);
        $coin = CoinBalance::firstOrCreate(['user_id' => $user->id]);

        if ($request->act == 'add') {
            // Create coin transaction
            $transaction = CoinTransaction::create([
                'user_id'     => $user->id,
                'coins'       => $request->amount,
                'amount'      => 0,
                'type'        => 'credit',
                'service'     => 'purchase',
                'gateway'     => 'system',
                'code'        => $reference,
                'description' => $request->message,
                'oldbal'      => $coin->balance,
                'newbal'      => $coin->balance + $request->amount,
            ]);
            // update coin balance and total earned
            creditUser($coin, $request->amount);
            $coin->increment('total_earned', $request->amount);
            // Send notification
            $title = 'Credit Notification ';
            $message = "Credit of {$request->amount} " . $transaction->description;
            sendUserNotification($user->id, $title, $message, null, 1);
            $mesg = 'User Balance Added Successfully.';
        } elseif ($request->act == 'sub') {
            // create transaction
            $transaction = CoinTransaction::create([
                'user_id'     => $user->id,
                'coins'       => $request->amount,
                'amount'      => 0,
                'type'        => 'debit',
                'service'     => 'spend',
                'gateway'     => 'system',
                'code'        => $reference,
                'description' => $request->message,
                'oldbal'      => $coin->balance,
                'newbal'      => $coin->balance - $request->amount,
            ]);
            // update coin balance and total earned
            debitUser($coin, $request->amount);
            $coin->decrement('total_earned', $request->amount);
            // Send Notification
            $title = 'Debit Notification ';
            $message = "Debit of {$request->amount} " . $transaction->description;
            sendUserNotification($user->id, $title, $message, null, 1);
            $mesg = 'User Balance Deducted Successfully.';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'url'     => route('admin.users.view', $user->id),
                'message' => $mesg,
            ], 200);
        }

        return redirect()->back()->withSuccess($mesg);
    }

    // Send email
    public function sendemail($id, Request $request)
    {
        $request->validate([
            'subject' => 'required',
            'message' => 'required|string',
        ]);
        $user = User::findorFail($id);
        general_email($user->email, $request->subject, $request->message);
        $msg = 'Email sent successfully';

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => $msg,
            ], 200);
        }

        return back()->withSuccess('Email Sent Successfully');
    }

    // send App notification
    public function sendNotification(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'body'  => 'required|string',
        ]);
        $user = User::findorFail($id);
        // save to notifications
        $user->notifys()->create([
            'title'   => $request->title,
            'message' => $request->body,
        ]);
        // send through FCM
        $ns = new NotificationService();
        $ns->sendCustom($user, [
            'title'   => $request->title,
            'message' => $request->body,
        ], [
            'push',
        ]);
        $msg = 'Notification sent successfully';

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => $msg,
            ], 200);
        }

        return back()->withSuccess($msg);
    }

    public function userLogin($id)
    {
        $user = User::findOrFail(($id));

        auth('web')->login($user, false);

        return redirect()->route('user.dashboard');
    }

    // ban user
    public function ban($id)
    {
        $user = User::findorFail($id);
        $user->status = 'banned';
        $user->save();
        //  send banned email to user
        sendNotification(
            'USER_BAN',
            $user,
            [
                'username'   => $user->username,
                'name'       => $user->name,
                'ban_reason' => 'Suspicious Activities',
            ]
        );

        return back()->withSuccess('User Banned Successfully');
    }

    public function unban($id)
    {
        $user = User::findorFail($id);
        $user->status = 'active';
        $user->save();
        // send unbanned email to user>??
        sendNotification(
            'USER_UNBAN',
            $user,
            [
                'username' => $user->username,
                'name'     => $user->name,
            ]
        );

        return back()->withSuccess('User Reactivated Successfully');
    }
}
