<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FlutterwaveService
{
    protected $publicKey;

    protected $secretKey;

    protected $hashKey;

    protected $baseUrl;

    public function __construct()
    {
        $this->publicKey = config('services.flutterwave.public');
        $this->hashKey = config('services.flutterwave.hash');
        $this->secretKey = config('services.flutterwave.secret');
        $this->baseUrl = 'https://api.flutterwave.com/v3';
    }

    // Generate a payment link
    public function createPayment($amount, $details, $currency = 'USD')
    {
        $data = [
            'payment_options' => 'card,banktransfer,ussd,mobilemoneyghana',
            'amount'          => $amount,
            'email'           => $details['email'],
            'tx_ref'          => $details['reference'],
            'meta'            => $details,
            'currency'        => $currency,
            'redirect_url'    => route('flutter.success'),
            'customer'        => [
                'email'       => $details['email'],
                'phonenumber' => $details['phone'],
                'name'        => $details['name'],
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Content-Type'  => 'application/json',
        ])->post("{$this->baseUrl}/payments", $data);

        if ($response->successful()) {
            return $response->json();
        }

        return $response->json();
        // failed:

    }

    // Check the status of a payment
    public function getTransactionStatus($reference)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
        ])->get("{$this->baseUrl}/transactions/{$reference}/verify");

        return $response->json();
    }

    /**
     * Validate webhook signature
     *
     * @param  string  $signature
     */
    public function validateWebhookHash(array $payload): bool
    {
        $receivedHash = request()->header('verif-hash');
        $hash = config('services.flutterwave.hash');

        if (! $receivedHash || ($hash !== $receivedHash)) {
            // This request isn't from Flutterwave; discard
            return false;
        }

        return true;
    }
}
