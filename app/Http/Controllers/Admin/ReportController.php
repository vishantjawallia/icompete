<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\LoginHistory;
use App\Models\Notify;
use App\Models\Vote;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function voteHistory(Request $request)
    {
        $query = Vote::latest();

        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }
        $votes = $query->paginate(50);

        return view('admin.reports.votes', compact('votes'));
    }

    public function loginHistory(Request $request)
    {
        $title = 'User Login History';
        $query = LoginHistory::latest();

        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }
        $logs = $query->paginate(50);

        return view('admin.reports.logins', compact('logs', 'title'));
    }

    public function loginIpHistory($ip)
    {
        $title = 'Login by - ' . $ip;
        $logs = LoginHistory::where('ip_address', $ip)->orderBy('id', 'desc')->with('user')->paginate(getPaginate());

        return view('admin.reports.logins', compact('title', 'logs', 'ip'));
    }

    public function loginHistoryDelete($id)
    {
        $log = LoginHistory::findOrFail($id);
        $log->delete();

        return back()->withSuccess('Log deleted successfully');
    }

    // referral commissions
    public function commissions(Request $request)
    {
        $query = CoinTransaction::whereService('referral')->orderByDesc('updated_at');

        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }
        $transactions = $query->paginate(50);

        return view('admin.reports.referrals', compact('transactions'));
    }

    // notification history
    public function notificationHistory(Request $request)
    {
        $query = Notify::orderByDesc('updated_at');

        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }
        $logs = $query->paginate(50);

        return view('admin.reports.notifications', compact('logs'));
    }

    public function notificationDelete($id)
    {
        $log = Notify::findOrFail($id);
        $log->delete();

        return back()->withSuccess('Notification deleted successfully');
    }
}
