<?php

namespace App\Livewire;

use App\Models\CoinPayment;
use App\Models\CoinTransaction;
use App\Models\Contest;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use App\Models\Vote;
use App\Models\Withdrawal;
use App\Traits\AdminDashboardTrait;
use Livewire\Component;

class AdminDashboard extends Component
{
    use AdminDashboardTrait;

    public $selectedTransaction = null;

    public function render()
    {
        $users = User::OrderByDesc('id')->limit(10)->get();
        $contests = Contest::orderByDesc('created_at')->whereStatus('pending')->limit(20)->get();
        $transactions = CoinTransaction::with('user')->orderByDesc('id')->limit(20)->get();

        return view('livewire.admin-dashboard', [
            'users'           => $users,
            'contests'        => $contests,
            'transactions'    => $transactions,
            'voteData'        => $this->voteStats(),
            'stats'           => $this->dashboardStats(),
            'votingChartData' => $this->getVotingData(),
            'revenueData'     => $this->getRevenueData(),
        ]);
    }

    private function voteStats()
    {
        $voteStats = Vote::selectRaw('
            SUM(quantity) as count,
            SUM(amount) as coins,
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN quantity ELSE 0 END) as today
        ')->first();

        return [
            'count'  => $voteStats->count ?? 0,
            'coins'  => $voteStats->coins ?? 0,
            'amount' => convertCoinsToFiat($voteStats->coins),
            'today'  => $voteStats->today ?? 0,
        ];
    }

    private function dashboardStats()
    {
        $userStats = User::where('status', 'active')
            ->selectRaw("
            COUNT(*) as total_users,
            SUM(CASE WHEN role = 'organizer' THEN 1 ELSE 0 END) as organizers_count,
            SUM(CASE WHEN role = 'voter' THEN 1 ELSE 0 END) as voters_count,
            SUM(CASE WHEN role = 'contestant' THEN 1 ELSE 0 END) as contestants_count
        ")->first();

        $usersCount = $userStats->total_users;
        $organizersCount = $userStats->organizers_count;
        $votersCount = $userStats->voters_count;
        $contestantsCount = $userStats->contestants_count;
        $contestStats = Contest::selectRaw("
            COUNT(*) as total_contests,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_contests
        ")->first();
        $contestsCount = $contestStats->total_contests;
        $pendingContests = $contestStats->pending_contests;
        $coinTrx = CoinTransaction::where('service', 'purchase')->count();
        $coinDeposit = CoinPayment::whereStatus('completed')->sum('amount');
        $withdrawStats = Withdrawal::selectRaw("
            SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as withdrawals,
            SUM(CASE WHEN status IN ('pending', 'processing') THEN amount ELSE 0 END) as pending_withdrawals
        ")->first();
        $withdrawals = $withdrawStats->withdrawals;
        $pendingWithdrawals = $withdrawStats->pending_withdrawals;
        $postCount = Post::count();
        $votesCount = Vote::sum('quantity');
        $commentCount = PostComment::count();

        return compact(
            'usersCount',
            'organizersCount',
            'votersCount',
            'contestantsCount',
            'contestsCount',
            'pendingContests',
            'coinTrx',
            'coinDeposit',
            'withdrawals',
            'pendingWithdrawals',
            'postCount',
            'votesCount',
            'commentCount'
        );
    }
}
