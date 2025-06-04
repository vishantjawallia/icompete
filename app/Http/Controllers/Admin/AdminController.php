<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminNotify;
use App\Traits\AdminDashboardTrait;
use Artisan;
use Auth;
use Cache;
use Hash;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    use AdminDashboardTrait;

    public function index()
    {
        return view('admin.index');
    }

    public function adminStaffs()
    {
        $admins = Admin::all();

        return view('admin.staffs', compact('admins'));
    }

    // Notifications
    public function notifications()
    {
        // update all notifs view as 1
        $notifs = AdminNotify::orderBy('read_at')->orderByDesc('created_at');
        // $notifs->update(['read_at' => now()]);
        $notifs = $notifs->paginate(100);

        return view('admin.notifications.index', compact('notifs'));
    }

    public function notificationRead($id)
    {
        $notification = AdminNotify::find($id);

        if (! $notification) {
            return response()->json(['status' => 'error', 'message' => 'Notification not found.'], 404);
        }
        $notification->update(['read_at' => now()]);
        $response = ['status' => 'success', 'message' => 'Notification marked as read.'];

        // if notification has url, add it to the response
        if ($notification->url) {
            $response['url'] = $notification->url;
        }

        return response()->json($response);
    }

    public function notificationOpen($id)
    {
        $notification = AdminNotify::find($id);

        if (! $notification) {
            return response()->json(['status' => 'error', 'message' => 'Notification not found.']);
        }

        $notification->update(['read_at' => now()]);

        if ($notification->url) {
            return response()->json(['status' => 'success', 'url' => $notification->url]);
        }

        // No URL associated, return success with no URL
        return response()->json(['status' => 'success']);
    }

    public function notificationDelete($id)
    {
        $notification = AdminNotify::find($id);

        if (! $notification) {
            return response()->json(['status' => 'error', 'message' => 'Notification not found.'], 404);
        }
        $notification->delete();
        $response = ['status' => 'success', 'message' => 'Notification deleted successfuly.'];

        return response()->json($response);
    }

    public function readAllNotification()
    {
        AdminNotify::whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['status' => 'success']);
    }

    public function ajaxNotifications()
    {
        if (!request()->ajax()) {
            return redirect()->route('admin.dashboard');
        }
        $adminId = auth('admin')->id();
        $cacheKey = "admin_{$adminId}_notifications";
        // Cache notifications per admin
        $notifications = Cache::remember($cacheKey, now()->addMinutes(60), function () {
            return AdminNotify::whereNull('read_at')
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id'               => $notification->id,
                        'title'            => $notification->title ?? ucfirst($notification->type),
                        'message'          => $notification->message,
                        'url'              => $notification->url,
                        'updated_at_human' => $notification->updated_at->diffForHumans(),
                    ];
                });
        });

        // Cache unread count separately
        $unreadCount = Cache::remember("{$cacheKey}_unread_count", now()->addMinutes(60), function () {
            return AdminNotify::whereNull('read_at')->count();
        });

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    public function logout()
    {
        auth('admin')->logout();

        return to_route('login');
    }

    public function profile()
    {
        $user = Auth::guard('admin')->user();

        return view('admin.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->password != null) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => __('Profile Updated Successfully.'),
        ]);
    }

    /**
     * Clear System cache.
     *
     * @return \Illuminate\Http\Response
     */
    public function clearCache(Request $request)
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return back()->withSuccess('Cache Cleared Successfully.');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function serverInfo()
    {
        $requirements = config('system.extensions');

        $results = [];
        // Check the requirements
        foreach ($requirements as $type => $extensions) {
            if (strtolower($type) == 'php') {
                foreach ($requirements[$type] as $extensions) {
                    $results['extensions'][$type][$extensions] = true;

                    if (! extension_loaded($extensions)) {
                        $results['extensions'][$type][$extensions] = false;

                        $results['errors'] = true;
                    }
                }
            }
        }

        // If the current php version doesn't meet the requirements
        if (version_compare(PHP_VERSION, config('system.php_version')) == -1) {
            $results['errors'] = true;
        }

        $server = $_SERVER;

        return view('admin.server', compact('results', 'server'));
    }
}
