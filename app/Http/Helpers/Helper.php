<?php

use App\Mail\Sendmail;
use App\Models\Admin;
use App\Models\AdminNotify;
use App\Models\CoinBalance;
use App\Models\EmailTemplate;
use App\Models\Notify;
use App\Models\NotifyTemplate;
use App\Models\Setting;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\NotificationService;

if (! function_exists('static_asset')) {
    /**
     * Generate a URL for a static asset in the public/assets directory.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    function static_asset($path, $secure = null)
    {
        if (php_sapi_name() == 'cli-server') {
            return app('url')->asset('assets/' . $path, $secure);
        }

        return app('url')->asset('public/assets/' . $path, $secure);
    }
}

// Return file uploaded via uploader
if (! function_exists('my_asset')) {
    /**
     * Generate a URL for a file uploaded via uploader in the public/uploads directory.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    function my_asset($path, $secure = null)
    {
        if (php_sapi_name() == 'cli-server') {
            return app('url')->asset('uploads/' . $path, $secure);
        }

        return app('url')->asset('public/uploads/' . $path, $secure);
    }
}

// Return temporary files
if (! function_exists('temp_asset')) {
    /**
     * Generate a URL for a file uploaded via uploader in the temp directory.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    function temp_asset($path, $secure = null)
    {
        if (php_sapi_name() == 'cli-server') {
            return app('url')->asset('temp/' . $path, $secure);
        }

        return app('url')->asset('public/temp/' . $path, $secure);
    }
}

// Get a setting value by key from the settings table
if (! function_exists('get_setting')) {
    /**
     * Get a setting value by key from the settings table.
     *
     * @param  string  $key
     * @return mixed
     */
    function get_setting($key = null)
    {
        $settings = Cache::get('Setting');

        if (! $settings) {
            $settings = Setting::first();
            Cache::put('Setting', $settings, 30000);
        }

        if ($key) {
            return @$settings->$key;
        }

        return $settings;
    }
}

// Get a system setting value by key from the system_settings table
if (! function_exists('sys_setting')) {
    /**
     * Get a system setting value by key from the system_settings table.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function sys_setting($key, $default = null)
    {
        $settings = Cache::get('SystemSettings');

        if (! $settings) {
            $settings = SystemSetting::all();
            Cache::put('SystemSettings', $settings, 30000);
        }
        $setting = $settings->where('name', $key)->first();

        return $setting == null ? $default : $setting->value;
    }
}

// formats currency
if (! function_exists('format_price')) {
    function format_price($price)
    {
        $fomated_price = number_format($price, 2);
        $currency = get_setting('currency');

        return $currency . $fomated_price;
    }
}

// NGN Currency Formats
if (! function_exists('ngnformat_price')) {
    function ngnformat_price($price)
    {
        $fomated_price = number_format($price, 2);
        $currency = '₦';

        return $currency . $fomated_price;
    }
}

function sym_price($price)
{
    $fomated_price = number_format($price, 2);
    $currency = get_setting('currency_code');

    return $currency . ' ' . $fomated_price;
}
function format_number($price, $place = 2)
{
    $fomated_price = number_format($price, $place);

    return $fomated_price;
}

// NGN Currency Formats
if (! function_exists('ngnformat_price')) {
    function ngnformat_price($price)
    {
        $fomated_price = number_format($price, 2);
        $currency = '₦';

        return $currency . $fomated_price;
    }
}
// Trim text and append ellipsis if needed
function textTrim($string, $length = null)
{
    // Set default length to 100 if not provided
    if (empty($length)) {
        $length = 100;
    }

    // Use Str::limit to trim the string and append ellipsis if needed
    return Str::limit($string, $length, '...');
}

// Trim text without appending ellipsis
function text_trimer($string, $length = null)
{
    // Set default length to 100 if not provided
    if (empty($length)) {
        $length = 100;
    }

    return Str::limit($string, $length);
}

// Generate a URL-friendly "slug" from a given string
function slug($string)
{
    // Use Str::slug to generate a URL-friendly slug
    return Illuminate\Support\Str::slug($string);
}

// Create a unique slug for a given name and model
function uniqueSlug($name, $model)
{
    // Generate a slug from the provided name
    $slug = Str::slug($name);

    // Check if the generated slug already exists in the model's table
    $allSlugs = checkRelatedSlugs($slug, $model);

    if (! $allSlugs->contains('slug', $slug)) {
        // If the slug is unique, return it
        return $slug;
    }
    // If the slug already exists, append a number to make it unique
    $i = 1;
    do {
        $newSlug = $slug . '-' . $i;

        if (! $allSlugs->contains('slug', $newSlug)) {
            return $newSlug;
        }
        $i++;
    } while (true);
}

// Check for existing slugs related to the provided slug and model
function checkRelatedSlugs($slug, $model)
{
    // Use DB::table to query the model's table for slugs starting with the provided slug
    return DB::table($model)->where('slug', 'LIKE', $slug . '%')->get();
}

// Generate a random alphanumeric string of a specified length
function getTrx($length = 15)
{
    $characters = 'ABCDEFGHJKMNOPQRSTUVWXYZ123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0';
    $charactersLength = strlen($characters);
    $randomString = '';
    // Generate a random string by selecting characters from the given set
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

function getTrans($prefix, $len = 15)
{
    $characters = 'ABCDEFGHJKMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $len; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $prefix . '_' . $randomString;
}
// Round the given amount to a specified number of decimal places
function getAmount($amount, $length = 2)
{
    $amount = round($amount, $length);

    // Ensure the returned amount is treated as a numeric value
    return $amount + 0;
}

// Format and display a datetime using Carbon library
function show_datetime($date, $format = 'Y-m-d h:ia')
{
    return \Carbon\Carbon::parse($date)->format($format);
}

// Format and display a datetime using Carbon library
function show_date($date, $format = 'Y-m-d')
{
    return \Carbon\Carbon::parse($date)->format($format);
}
function trans_date($date, $format = 'M d, Y')
{
    return \Carbon\Carbon::parse($date)->format($format);
}

// Format and display a time
function show_time($date, $format = 'h:ia')
{
    return \Carbon\Carbon::parse($date)->format($format);
}
function campaignDate($date, $format = 'M, d')
{
    return \Carbon\Carbon::parse($date)->format($format);
}
function diffForHumans($date)
{
    $lang = session()->get('lang');
    \Carbon\Carbon::setlocale($lang);

    return \Carbon\Carbon::parse($date)->diffForHumans();
}

function custom_text($string)
{
    return ucfirst(str_replace('_', ' ', $string));
}

function getNumber($length = 6)
{
    // if ($length == 6) {
    //     return 123456;
    // }
    $characters = '1234567890';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}
function getPaginate()
{
    return 50;
}
function paginateLinks($data)
{
    return $data->appends(request()->all())->links();
}

function getUserStatus($status)
{
    switch ($status) {
        case 'active':
            return '<span class="badge bg-success badge-sm">' . __('active') . '</span>';
        case 'inactive':
            return '<span class="badge bg-warning badge-sm">' . __('inactive') . '</span>';
        case 'banned':
            return '<span class="badge bg-danger badge-sm">' . __('banned') . '</span>';
        default:
            return '<span class="badge bg-danger badge-sm">' . __('banned') . '</span>';
    }
}

function get_status($status)
{
    switch ($status) {
        case 1:
            return '<span class="badge bg-success badge-sm">Enabled</span>';
            break;
        case 0:
            return '<span class="badge bg-danger badge-sm">Disabled</span>';
            break;
        case 2:
            return '<span class="badge bg-danger badge-sm">Disabled</span>';
            break;
        default:
            return '<span class="badge bg-warning badge-sm">unknown</span>';
    }
}

function trxStatus($status)
{
    switch ($status) {
        case 1:
            return '<span class="badge bg-success badge-sm">' . __('successful') . '</span>';
        case 2:
            return '<span class="badge bg-warning badge-sm">' . __('processing') . '</span>';
        case 3:
            return '<span class="badge bg-danger badge-sm">' . __('failed') . '</span>';
        default:
            return '<span class="badge bg-danger badge-sm">' . __('reversed') . '</span>';
    }
}
// withdrawal status
function withdrawStatus($status)
{
    switch ($status) {
        case 'approved':
            return '<span class="badge bg-success badge-sm">' . __('approved') . '</span>';
        case 'pending':
            return '<span class="badge bg-warning badge-sm">' . __('pending') . '</span>';
        case 'completed':
            return '<span class="badge bg-success badge-sm">' . __('completed') . '</span>';
        case 'canceled':
            return '<span class="badge bg-danger badge-sm">' . __('canceled') . '</span>';
        case 'processing':
            return '<span class="badge bg-info  badge-sm">' . __('processing') . '</span>';
        default:
            return '<span class="badge bg-danger badge-sm">' . __('reversed') . '</span>';
    }
}
function contestStatus($status)
{
    switch ($status) {
        case 'draft':
            return '<span class="badge bg-secondary badge-sm">' . __('draft') . '</span>';
        case 'active':
            return '<span class="badge bg-success badge-sm">' . __('active') . '</span>';
        case 'ongoing':
            return '<span class="badge bg-info badge-sm">' . __('ongoing') . '</span>';
        case 'completed':
            return '<span class="badge bg-success badge-sm">' . __('completed') . '</span>';
        case 'canceled':
            return '<span class="badge bg-danger badge-sm">' . __('cancelled') . '</span>';
        case 'pending':
            return '<span class="badge bg-warning badge-sm">' . __('pending') . '</span>';
        default:
            return '<span class="badge bg-secondary badge-sm">' . __('unknown') . '</span>';
    }
}
function submissionStatus($status)
{
    switch ($status) {
        case 'submitted':
            return '<span class="badge bg-info badge-sm">' . __('submitted') . '</span>';
        case 'approved':
            return '<span class="badge bg-success badge-sm">' . __('approved') . '</span>';
        case 'rejected':
            return '<span class="badge bg-danger badge-sm">' . __('rejected') . '</span>';
        case 'pending':
            return '<span class="badge bg-warning badge-sm">' . __('pending') . '</span>';
        case 'enabled':
            return '<span class="badge bg-success badge-sm">' . __('enabled') . '</span>';
        case 'disabled':
            return '<span class="badge bg-secondary badge-sm">' . __('disabled') . '</span>';
        default:
            return '<span class="badge bg-secondary badge-sm">' . __('unknown') . '</span>';
    }
}

// short code replacer
function shortCodeReplacer($shortCode, $replace_with, $template_string)
{
    return str_replace($shortCode, $replace_with, $template_string);
}
// Send general emails
function send_emails($email, $type, $shortCodes = [])
{
    $email_template = EmailTemplate::whereType($type)->first();

    if ($email_template == null) {
        return;
    }

    if ($email_template->status != 1) {
        return;
    }
    // update shotcodes with default ones
    $shortCodes['site_name'] = get_setting('name');
    $shortCodes['site_email'] = get_setting('email');
    $shortCodes['site_phone'] = get_setting('phone');
    $shortCodes['support_email'] = get_setting('email');
    $shortCodes['currency'] = get_setting('currency');
    $shortCodes['site_address'] = get_setting('address');
    $shortCodes['date'] = date('Y-m-d H:m:s');

    // replace message shortcodes
    $message = $email_template->content;
    foreach ($shortCodes as $code => $value) {
        $message = shortCodeReplacer('{' . $code . '}', $value, $message);
    }
    // subject
    $subject = $email_template->subject;
    foreach ($shortCodes as $code => $value) {
        $subject = shortCodeReplacer('{' . $code . '}', $value, $subject);
    }
    // title
    $title = $email_template->title;
    foreach ($shortCodes as $code => $value) {
        $title = shortCodeReplacer('{' . $code . '}', $value, $title);
    }

    // dd($subject, $message);
    // send email
    $data['subject'] = $subject;
    $data['message'] = $message;
    $data['view'] = 'emails.main';
    $data['title'] = $title;

    if (sys_setting('email_gateway') == 'php') {
        // send using php mail
        $mfName = env('MAIL_FROM_NAME');
        $mfEmail = env('MAIL_FROM_ADDRESS');

        try {
            $messageView = view('emails.main', compact('data'))->render();
            $headers = "From: $mfName <$mfEmail> \r\n";
            $headers .= "Reply-To: $mfName <$mfEmail> \r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";

            @mail($email, $subject, $messageView, $headers);
        } catch (\Exception $e) {
            // dd($e);
            \Log::error('Error sending email via PHP mail: ' . $e->getMessage());
        }
    } else {
        try {
            Mail::to($email)->send(new Sendmail($data));
        } catch (\Exception $e) {
            // dd($e);
            \Log::error('Error sending email via SMTP: ' . $e->getMessage());
        }
    }
}
// new
function send_email($email, $type, $shortCodes = [])
{
    $email_template = NotifyTemplate::whereType($type)->first();

    if ($email_template == null) {
        return;
    }

    if ($email_template->email_status != 1) {
        return;
    }
    // update shotcodes with default ones
    $shortCodes['site_name'] = get_setting('name');
    $shortCodes['site_email'] = get_setting('email');
    $shortCodes['site_phone'] = get_setting('phone');
    $shortCodes['support_email'] = get_setting('email');
    $shortCodes['currency'] = get_setting('currency');
    $shortCodes['site_address'] = get_setting('address');
    $shortCodes['date'] = date('Y-m-d H:m:s');

    // replace message shortcodes
    $message = $email_template->email_content;
    foreach ($shortCodes as $code => $value) {
        $message = shortCodeReplacer('{' . $code . '}', $value, $message);
    }
    // subject
    $subject = $email_template->email_subject;
    foreach ($shortCodes as $code => $value) {
        $subject = shortCodeReplacer('{' . $code . '}', $value, $subject);
    }
    // title
    $title = $email_template->title;
    foreach ($shortCodes as $code => $value) {
        $title = shortCodeReplacer('{' . $code . '}', $value, $title);
    }

    // dd($subject, $message);
    // send email
    $data['subject'] = $subject;
    $data['message'] = $message;
    $data['view'] = 'emails.main';
    $data['title'] = $title;

    if (sys_setting('email_gateway') == 'php') {
        // send using php mail
        $mfName = env('MAIL_FROM_NAME');
        $mfEmail = env('MAIL_FROM_ADDRESS');

        try {
            $messageView = view('emails.main', compact('data'))->render();
            $headers = "From: $mfName <$mfEmail> \r\n";
            $headers .= "Reply-To: $mfName <$mfEmail> \r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";

            @mail($email, $subject, $messageView, $headers);
        } catch (\Exception $e) {
            // dd($e);
            \Log::error('Error sending email via PHP mail: ' . $e->getMessage());
        }
    } else {
        try {
            Mail::to($email)->send(new Sendmail($data));
        } catch (\Exception $e) {
            // dd($e);
            \Log::error('Error sending email via SMTP: ' . $e->getMessage());
        }
    }
}
// send Email
function general_email($email, $sub, $mes, $title = null)
{
    // return $email;
    $data['subject'] = $sub;
    $data['message'] = $mes;
    $data['title'] = $title;
    $data['view'] = 'emails.main';

    if (sys_setting('email_gateway') == 'php') {
        // send using php mail
        $mfName = env('MAIL_FROM_NAME');
        $mfEmail = env('MAIL_FROM_ADDRESS');

        try {
            $messageView = view('emails.main', compact('data'))->render();
            $headers = "From: $mfName <$mfEmail> \r\n";
            $headers .= "Reply-To: $mfName <$mfEmail> \r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";

            @mail($email, $sub, $messageView, $headers);
        } catch (\Exception $e) {
            // dd($e);
            \Log::error('Error sending email via PHP mail: ' . $e->getMessage());
        }
    } else {
        try {
            Mail::to($email)->send(new Sendmail($data));
        } catch (\Exception $e) {
            // dd($e);
            \Log::error('Error sending email via SMTP: ' . $e->getMessage());
        }
    }
}
/**
 * Send a notification to a user
 *
 * @param  int  $user_id
 * @param  string  $title
 * @param  string  $message
 * @param  string|null  $link
 * @param  int  $push
 * @return void
 */
function sendUserNotification($user_id, $title, $message, $link = null, $push = 0)
{
    $user = User::find($user_id);

    $user->notifys()->create([
        'title'   => $title,
        'type'    => 'user',
        'message' => $message,
        'url'     => $link ?? null,
    ]);

    if ($push == 1) {
        $ns = new NotificationService();
        $ns->sendCustom($user, [
            'title'   => $title,
            'message' => $message,
        ], ['push']);
    }
}

/**
 * Send a notification to the admins
 *
 * @param  string  $title
 * @param  string  $message
 * @param  string|null  $link
 * @return void
 */
function sendAdminNotification(array $data, int $email = 0, int $push = 0)
{
    $title = $data['title'];
    $message = $data['message'];
    $link = $data['link'] ?? null;

    $notify = AdminNotify::create([
        'title'   => $title,
        'message' => $message,
        'url'     => $link ?? null,
    ]);

    $admins = Admin::all();

    foreach ($admins as $admin) {
        // send emails
        if ($email == 1) {
            $tEmail = "Hello {$admin->name}, ";
            $mes = "{$message}";

            if ($link) {
                $mes .= "</br> <a href='{$link}' class='btn'>View </a>";
            }
            general_email($admin->email, $title, $mes, $tEmail);
        }

        // send push notification
        if ($push == 1) {
            $ns = new NotificationService();
            $ns->sendAdminCustom($admin, [
                'title'   => $title,
                'message' => $message,
            ], ['push']);
        }
    }
    // clear cache
    Cache::forget('admin_notifications');
    Cache::forget('admin_notifications_unread_count');
}
function notifyAdmin(string $type, array $shortcodes, $custom = [])
{
    $ns = new NotificationService();
    $ns->sendAdmin($type, $shortcodes, $custom);

}
// notify
function sendNotification(string $type, User $user, array $shortcodes, $custom = [])
{
    $ns = new NotificationService();
    $ns->send($type, $user, $shortcodes, $custom);

}

// api user object
function apiUserObject($user)
{
    $data = [
        'id'                => $user->id,
        'first_name'        => $user->first_name,
        'last_name'         => $user->last_name,
        'email'             => $user->email,
        'username'          => $user->username,
        'phone'             => $user->phone,
        'gender'            => $user->gender,
        'bio'               => $user->bio,
        'image'             => ($user->image) ? my_asset($user->image) : my_asset('user/default.jpg'),
        'email_verify'      => $user->email_verify,
        'email_verified_at' => $user->email_verified_at,
        'role'              => $user->role,
        'status'            => $user->status,
        'created_at'        => $user->created_at,
    ];

    // withdrawal
    $data['withdraw'] = [
        'paypal_email' => $user->paypal_email,
        'bank_details' => $user->bank_details,
    ];

    return $data;
}

function moveImage($folder, $filePath)
{
    // Define the source file path
    $tempFile = public_path("temp/{$filePath}");

    // Check if the file exists in the temp directory
    if (! file_exists($tempFile)) {
        return; // Return null if the file doesn't exist
    }

    // Generate a unique file name
    $extension = pathinfo($filePath, PATHINFO_EXTENSION); // Get the file extension
    $fileName = now()->timestamp . '-' . Str::random(26) . '.' . $extension;

    // Define the target directory and file path
    $targetDir = public_path("uploads/{$folder}/");
    $targetFile = "{$targetDir}{$fileName}";

    // Ensure the target directory exists, create it if not
    if (! file_exists($targetDir)) {
        mkdir($targetDir, 0777, true); // Recursive directory creation
    }

    // Move the file to the target directory
    if (rename($tempFile, $targetFile)) {
        // Return the relative file path for storage
        return "{$folder}/{$fileName}";
    }

    // If the move operation fails, return null

}

// helpers for user balance debit/credit
function debitUser($wallet, $amount)
{
    DB::transaction(function () use ($wallet, $amount) {
        CoinBalance::where('id', $wallet->id)->lockForUpdate()->decrement('balance', $amount);
    });

    return true;
}
function creditUser($wallet, $amount)
{
    DB::transaction(function () use ($wallet, $amount) {
        $c = CoinBalance::where('id', $wallet->id)->lockForUpdate()->increment('balance', $amount);
    });

    return true;
}

function queryBuild($key, $value)
{
    $queries = request()->query();

    if (count($queries) > 0) {
        $delimeter = '&';
    } else {
        $delimeter = '?';
    }

    if (request()->has($key)) {
        $url = request()->getRequestUri();
        $pattern = "\?$key";
        $match = preg_match("/$pattern/", $url);

        if ($match != 0) {
            return preg_replace('~(\?|&)' . $key . '[^&]*~', "\?$key=$value", $url);
        }
        $filteredURL = preg_replace('~(\?|&)' . $key . '[^&]*~', '', $url);

        return $filteredURL . $delimeter . "$key=$value";
    }

    return request()->getRequestUri() . $delimeter . "$key=$value";
}

function getRealIP()
{
    $ip = $_SERVER['REMOTE_ADDR'];

    // Deep detect ip
    if (filter_var(@$_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED'];
    }

    if (filter_var(@$_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    }

    if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }

    if (filter_var(@$_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }

    if (filter_var(@$_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }

    if ($ip == '::1') {
        $ip = '127.0.0.1';
    }

    return $ip;
}

function getIpInfo()
{
    $ip = getRealIp();
    // $ip = '102.89.82.70';

    $xml = @simplexml_load_file('http://www.geoplugin.net/xml.gp?ip=' . $ip);

    $country = @$xml->geoplugin_countryName;
    $city = @$xml->geoplugin_city;
    $area = @$xml->geoplugin_areaCode;
    $code = @$xml->geoplugin_countryCode;
    $long = @$xml->geoplugin_longitude;
    $lat = @$xml->geoplugin_latitude;

    $data['country'] = $country ?? [];
    $data['city'] = $city ?? [];
    $data['area'] = $area ?? [];
    $data['code'] = $code ?? [];
    $data['long'] = $long ?? [];
    $data['lat'] = $lat ?? [];
    $data['ip'] = $ip;
    $data['time'] = date('Y-m-d h:i:s A');

    return $data;
}

function getGeoLocation($ip)
{
    $url = "http://ip-api.com/json/{$ip}";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data && $data['status'] === 'fail') {
        return; // If geolocation fails, return null
    }

    return $data;

    return [
        'city'    => $data['city'] ?? 'Unknown City',
        'country' => $data['country'] ?? 'Unknown Country',
    ];
}
function getBrowserDetails($userAgent)
{
    $browsers = [
        'Chrome'            => 'Chrome',
        'Firefox'           => 'Firefox',
        'Safari'            => 'Safari',
        'Opera'             => 'Opera',
        'Edge'              => 'Edge',
        'Internet Explorer' => 'MSIE',
        'Trident'           => 'Trident', // For IE 11
    ];

    foreach ($browsers as $browser => $key) {
        if (strpos($userAgent, $key) !== false) {
            return $browser;
        }
    }

    return 'Unknown Browser'; // If no match is found
}

function getOSDetails($userAgent)
{
    $oses = [
        'Windows' => 'Windows NT',
        'Mac'     => 'Macintosh',
        'Linux'   => 'Linux',
        'Android' => 'Android',
        'iOS'     => 'iPhone',
    ];

    foreach ($oses as $os => $key) {
        if (strpos($userAgent, $key) !== false) {
            return $os;
        }
    }

    return 'Unknown OS'; // If no match is found
}
function convertCurrencyToCoins($amount)
{
    // Calculate coins based on the rate: 10 currency units = 1000 coins
    return ($amount / 10) * 1000;
}

function convertCoinsToFiat($coins)
{
    // Calculate fiat currency amount from coins
    return ($coins / 1000) * 10;
}
