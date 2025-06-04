<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoinSetting;
use App\Models\CoinTransaction;
use Illuminate\Http\Request;

class CoinController extends Controller
{
    public function transactions(Request $request)
    {
        $services = CoinTransaction::select('service')->distinct()->orderBy('service')->get();

        $query = CoinTransaction::orderByDesc('updated_at');

        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by service
        if ($request->filled('service')) {
            $query->where('service', $request->service);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $transactions = $query->paginate(50);

        return view('admin.coins.index', compact('transactions', 'services'));
    }

    public function settings()
    {
        $settings = cache()->remember('coin_settings', 3600, function () {
            return CoinSetting::all();
        });

        return view('admin.coins.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'settings.coin_rates.value.*.coins' => 'required|integer|min:1',
            'settings.coin_rates.value.*.price' => 'required|numeric|min:0',
            'settings.coin_usage.value.*'       => 'required|numeric|min:0',
            'settings.coin_rewards.value.*'     => 'required|numeric|min:0',
        ]);

        foreach ($request->settings as $key => $setting) {
            // Find the existing setting by key
            $existingSetting = CoinSetting::where('key', $key)->first();

            if ($existingSetting) {
                $existingSetting->update([
                    'value' => $setting['value'],
                ]);
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Coin settings updated successfully!',
        ]);

    }
}
