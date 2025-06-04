<?php

namespace App\Http\Controllers;

use App\Models\CoinBalance;
use App\Models\CoinTransaction;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Str;

class UtilityController extends Controller
{
    use ApiResponse;

    /**
     * Retrieve and return the application settings.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the settings data.
     */
    public function settings(Request $request)
    {
        // Retrieve settings from the database or configuration
        $settings = get_setting();

        // Prepare the settings data to be returned
        $data = [
            'title'         => $settings->title,
            'name'          => $settings->name,
            'about'         => $settings->description,
            'support_email' => $settings->email,
            'phone'         => $settings->phone,
            'address'       => $settings->address,
            'currency'      => $settings->currency,
            'currency_code' => $settings->currency_code,
            'logo'          => my_asset($settings->logo),
            'favicon'       => my_asset($settings->favicon),
            'social'        => [
                'facebook'  => $settings->facebook,
                'telegram'  => $settings->telegram,
                'whatsapp'  => $settings->whatsapp,
                'twitter'   => $settings->twitter,
                'instagram' => $settings->instagram,
            ],
        ];

        // Return the settings data as a JSON response
        return response()->json([
            'status'  => 'success',
            'message' => 'Settings retrieved successfully.',
            'data'    => $data,
        ], 200);
    }

    /**
     * Handle the image upload process.
     *
     * @param  Request  $request  The incoming HTTP request containing the image file.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the uploaded image path.
     */
    public function uploadImage(Request $request)
    {
        // Validate the incoming request to ensure an image file is provided and meets the criteria
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
        ], [
            'image.max' => 'The uploaded image must be of 5MB or less.',
        ]);

        // Retrieve the uploaded image file from the request
        $image = $request->file('image');

        // Generate a unique file name for the image
        $fileName = now()->timestamp . '-' . Str::random(20) . '.' . $image->getClientOriginalExtension();

        // Move the image to a temporary folder
        $image->move(public_path('temp'), $fileName);

        // Return the path of the uploaded image as a JSON response
        return response()->json([
            'status'  => 'success',
            'type'    => 'image',
            'message' => 'Image uploaded successfully.',
            'path'    => $fileName,
        ], 201);
    }

    /**
     * Handle the File upload process.
     *
     * @param  Request  $request  The incoming HTTP request containing the image file.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the uploaded file path.
     */
    public function uploadAssets(Request $request)
    {
        // Validate the incoming request to ensure an image file is provided and meets the criteria
        $request->validate([
            'type' => 'required|in:image,video',
            'file' => 'required|file',
        ]);

        if ($request->type == 'image') {
            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
            ], [
                'file.max' => 'The uploaded image must be of 5MB or less.',
            ]);

            $image = $request->file('file');

            $fileName = now()->timestamp . '-' . Str::random(20) . '.' . $image->getClientOriginalExtension();

            $image->move(public_path('temp'), $fileName);

            return response()->json([
                'status'  => 'success',
                'type'    => 'image',
                'message' => 'Image uploaded successfully.',
                'path'    => $fileName,
            ], 201);
        }

        if ($request->type == 'video') {
            return $this->uploadVideo($request);
        }

        // Return the path of the uploaded image as a JSON response
        return response()->json([
            'status'  => 'error',
            'message' => 'Invalid File type selected.',
        ], 400);
    }

    /**
     * Handle the video upload process.
     *
     * @param  Request  $request  The incoming HTTP request containing the video file.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the uploaded video path.
     */
    public function uploadVideo(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:mp4,mkv|max:10048',
        ], [
            'file.max' => 'The uploaded video must be 10MB or less.',
        ]);

        $video = $request->file('file');
        $fileName = now()->timestamp . '-' . Str::random(20) . '.' . $video->getClientOriginalExtension();
        $video->move(public_path('temp'), $fileName);

        return response()->json([
            'status'  => 'success',
            'message' => 'Video uploaded successfully.',
            'type'    => 'video',
            'path'    => $fileName,
        ], 201);
    }

    public function rewardAds(Request $request)
    {
        $request->validate([
            'reward_type'   => 'required|string|in:daily_login,ads_reward,challenge,others',
            'reward_amount' => 'required|integer|min:1', // amounnt of coins
            'ads_network'   => 'nullable|string',
        ]);
        // get user
        $user = \Auth::user();

        if ($user) {
            $coin = CoinBalance::firstOrCreate(['user_id' => $user->id]);
            $amount = $request->reward_amount ?? 5;
            $type = \Str::replace('_', ' ', ucfirst($request->reward_type));
            // create coin transaction
            $transaction = CoinTransaction::create([
                'user_id'     => $user->id,
                'coins'       => $amount,
                'amount'      => 0,
                'type'        => 'credit',
                'service'     => 'reward',
                'gateway'     => 'ads',
                'code'        => getTrx(13),
                'response'    => null,
                'description' => $type . ' Reward',
                'oldbal'      => $coin->balance,
                'newbal'      => $coin->balance + $amount,
            ]);
            // update coin balance and total earned
            creditUser($coin, $amount);
            $coin->increment('total_earned', $amount);
            // send push reward notification
            $ns = new NotificationService();
            $ns->sendCustom($user, [
                'title'   => 'Reward',
                'message' => 'You have received ' . $amount . ' coins for ' . $type,
            ], ['push','inapp']);

            return $this->successResponse('Reward given successfully');

        }

        return $this->successResponse('Reward given to no user');
    }
}
