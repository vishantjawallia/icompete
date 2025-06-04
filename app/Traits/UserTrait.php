<?php

namespace App\Traits;

trait UserTrait
{
    // Returns an array representation of the user object with selected attributes
    private function userObject($user)
    {
        $data = [
            'id'           => $user->id,
            'first_name'   => $user->first_name,
            'last_name'    => $user->last_name,
            'username'     => $user->username,
            'email'        => $user->email,
            'phone'        => $user->phone,
            'gender'       => $user->gender,
            'bio'          => $user->bio,
            'email_verify' => $user->email_verify,
            'image'        => ($user->image)
                    ? my_asset($user->image)
                    : my_asset('users/default.jpg'),
            'role'         => $user->role,
            'status'       => $user->status,
            'created_at'   => $user->created_at,
            'push_token'   => $user->push_token,
        ];

        if ($user->role == 'organizer') {
            $data['organization_name'] = $user->organization_name;
            $data['social_links'] = $user->social_links;
        }
        // withdrawal
        $data['withdraw'] = [
            'paypal_email' => $user->paypal_email,
            'bank_details' => $user->bank_details,
        ];

        return $data;
    }

    // Returns the notification data object
    public function notifyObject($data)
    {
        return $data;
    }
}
