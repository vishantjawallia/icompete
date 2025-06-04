<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\SettingsTrait;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use SettingsTrait;

    /**
     * Display the settings index page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.settings.index');
    }

    /**
     * Display the payment settings page.
     *
     * @return \Illuminate\View\View
     */
    public function payment()
    {
        return view('admin.settings.payment');
    }

    /**
     * Display the system settings page.
     *
     * @return \Illuminate\View\View
     */
    public function system()
    {
        return view('admin.settings.system');
    }

    /**
     * Display the email settings page.
     *
     * @return \Illuminate\View\View
     */
    public function email()
    {
        return view('admin.settings.email');
    }

    public function update(Request $request)
    {
        $this->updateSettings($request);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Settings Updated Successfully',
            ], 200);
        }

        return redirect()->back()->with('success', __('Settings Updated Successfully.'));
    }

    public function systemUpdate(Request $request)
    {
        $this->systemSetUpdate($request);

        return 1;
    }

    public function storeSettings(Request $request)
    {
        $this->updateSystemSettings($request);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Settings Updated Successfully',
            ], 200);
        }

        return redirect()->back()->withSuccess(__('Settings Updated Successfully.'));
    }

    public function envkeyUpdate(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Settings Updated Successfully',
            ], 200);
        }

        return back()->withSuccess('Settings updated successfully');
    }
}
