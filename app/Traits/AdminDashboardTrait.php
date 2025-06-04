<?php

namespace App\Traits;

use App\Models\CoinPayment;
use App\Models\Vote;
use Carbon\Carbon;

trait AdminDashboardTrait
{
    public function getVotingData()
    {
        $currentDate = now();
        $votes = Vote::select('id', 'created_at', 'quantity')->get();

        $weeklyVotes = $this->getVotesForCurrentWeek($votes, $currentDate);
        $monthlyVotes = $this->getVotesForCurrentMonth($votes, $currentDate);
        $yearlyVotes = $this->getVotesForCurrentYear($votes, $currentDate);
        $last5YearsVotes = $this->getVotesForLast5Years($votes, $currentDate);

        $votingData = json_encode(
            [
                'weekly' => [
                    'categories' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    'data'       => $weeklyVotes,
                ],
                'monthly' => [
                    'categories' => ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],
                    'data'       => $monthlyVotes,
                ],
                'yearly' => [
                    'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    'data'       => $yearlyVotes,
                ],
                'all' => [
                    'categories' => array_keys($last5YearsVotes),
                    'data'       => array_values($last5YearsVotes),
                ],

            ]
        );

        return $votingData;
    }

    private function getVotesForCurrentWeek($votes, $currentDate)
    {
        $weekStart = $currentDate->startOfWeek();
        $weekEnd = $currentDate->endOfWeek();

        $weeklyData = array_fill(0, 7, 0); // 7 days, Monday-Sunday

        foreach ($votes as $vote) {
            $voteDate = Carbon::parse($vote->created_at);

            if ($voteDate->between($weekStart, $weekEnd)) {
                $dayIndex = $voteDate->dayOfWeekIso - 1; // Ensure Monday = 0, Sunday = 6
                $weeklyData[$dayIndex] += $vote->quantity;
            }
        }

        return $weeklyData;
    }

    private function getVotesForCurrentMonth($votes, $currentDate)
    {
        $monthStart = $currentDate->startOfMonth();
        $monthEnd = $currentDate->endOfMonth();

        $weeksInMonth = ceil($monthEnd->weekOfMonth); // Get actual weeks in this month
        $monthlyData = array_fill(0, $weeksInMonth, 0);

        foreach ($votes as $vote) {
            $voteDate = Carbon::parse($vote->created_at);

            if ($voteDate->between($monthStart, $monthEnd)) {
                $weekIndex = $voteDate->weekOfMonth - 1; // Zero-based index

                if (isset($monthlyData[$weekIndex])) {
                    $monthlyData[$weekIndex] += $vote->quantity;
                }
            }
        }

        return $monthlyData;
    }

    private function getVotesForCurrentYear($votes, $currentDate)
    {
        $yearStart = $currentDate->startOfYear();
        $yearEnd = $currentDate->endOfYear();

        $yearlyData = array_fill(0, 12, 0);

        foreach ($votes as $vote) {
            $voteDate = Carbon::parse($vote->created_at);

            if ($voteDate->between($yearStart, $yearEnd)) {
                $monthIndex = $voteDate->month - 1;
                $yearlyData[$monthIndex] += $vote->quantity;
            }
        }

        return $yearlyData;
    }

    private function getVotesForLast5Years($votes, $currentDate)
    {
        $endYear = $currentDate->year;
        $startYear = $endYear - 4;

        $yearlyData = array_fill_keys(range($startYear, $endYear), 0);

        foreach ($votes as $vote) {
            $voteYear = Carbon::parse($vote->created_at)->year;

            if (isset($yearlyData[$voteYear])) {
                $yearlyData[$voteYear] += $vote->quantity;
            }
        }

        return $yearlyData;
    }

    // revenue data
    public function getRevenueData()
    {
        $currentDate = now();
        $revenues = CoinPayment::select('id', 'created_at', 'amount')->get();

        $weeklyRevenue = $this->getRevenueForCurrentWeek($revenues, $currentDate);
        $monthlyRevenue = $this->getRevenueForCurrentMonth($revenues, $currentDate);
        $yearlyRevenue = $this->getRevenueForCurrentYear($revenues, $currentDate);
        $last5YearsRevenue = $this->getRevenueForLast5Years($revenues, $currentDate);

        $revenueData = json_encode([
            'weekly' => [
                'categories' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'data'       => [
                    'count' => $weeklyRevenue['count'],
                    'sum'   => $weeklyRevenue['sum'],
                ],
            ],
            'monthly' => [
                'categories' => ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],
                'data'       => [
                    'count' => $monthlyRevenue['count'],
                    'sum'   => $monthlyRevenue['sum'],
                ],
            ],
            'yearly' => [
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'data'       => [
                    'count' => $yearlyRevenue['count'],
                    'sum'   => $yearlyRevenue['sum'],
                ],
            ],
            'all' => $last5YearsRevenue,
        ]);

        return $revenueData;
    }

    private function getRevenueForCurrentWeek($revenues, $currentDate)
    {
        $weekStart = $currentDate->startOfWeek();
        $weekEnd = $currentDate->endOfWeek();

        $weeklyData = [
            'count' => array_fill(0, 7, 0),
            'sum'   => array_fill(0, 7, 0),
        ];

        foreach ($revenues as $revenue) {
            $revenueDate = Carbon::parse($revenue->created_at);

            if ($revenueDate->between($weekStart, $weekEnd)) {
                $dayIndex = $revenueDate->dayOfWeekIso - 1;
                $weeklyData['count'][$dayIndex] += 1;
                $weeklyData['sum'][$dayIndex] += $revenue->amount;
            }
        }

        return $weeklyData;
    }

    private function getRevenueForCurrentMonth($revenues, $currentDate)
    {
        $monthStart = $currentDate->startOfMonth();
        $monthEnd = $currentDate->endOfMonth();

        $weeksInMonth = ceil($monthEnd->weekOfMonth);
        $monthlyData = [
            'count' => array_fill(0, $weeksInMonth, 0),
            'sum'   => array_fill(0, $weeksInMonth, 0),
        ];

        foreach ($revenues as $revenue) {
            $revenueDate = Carbon::parse($revenue->created_at);

            if ($revenueDate->between($monthStart, $monthEnd)) {
                $weekIndex = $revenueDate->weekOfMonth - 1;

                if (isset($monthlyData['count'][$weekIndex])) {
                    $monthlyData['count'][$weekIndex] += 1;
                    $monthlyData['sum'][$weekIndex] += $revenue->amount;
                }
            }
        }

        return $monthlyData;
    }

    private function getRevenueForCurrentYear($revenues, $currentDate)
    {
        $yearStart = $currentDate->startOfYear();
        $yearEnd = $currentDate->endOfYear();

        $yearlyData = [
            'count' => array_fill(0, 12, 0),
            'sum'   => array_fill(0, 12, 0),
        ];

        foreach ($revenues as $revenue) {
            $revenueDate = Carbon::parse($revenue->created_at);

            if ($revenueDate->between($yearStart, $yearEnd)) {
                $monthIndex = $revenueDate->month - 1;
                $yearlyData['count'][$monthIndex] += 1;
                $yearlyData['sum'][$monthIndex] += $revenue->amount;
            }
        }

        return $yearlyData;
    }

    private function getRevenueForLast5Years($revenues, $currentDate)
    {
        $endYear = $currentDate->year;
        $startYear = $endYear - 4;

        $yearlyData = [
            'categories' => range($startYear, $endYear),
            'count'      => array_fill_keys(range($startYear, $endYear), 0),
            'sum'        => array_fill_keys(range($startYear, $endYear), 0),
        ];

        foreach ($revenues as $revenue) {
            $revenueYear = Carbon::parse($revenue->created_at)->year;

            if (isset($yearlyData['count'][$revenueYear])) {
                $yearlyData['count'][$revenueYear] += 1;
                $yearlyData['sum'][$revenueYear] += $revenue->amount;
            }
        }

        return [
            'categories' => array_values($yearlyData['categories']),
            'data'       => [
                'count' => array_values($yearlyData['count']),
                'sum'   => array_values($yearlyData['sum']),
            ],
        ];
    }
}
