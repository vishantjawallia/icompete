<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Str;

class MonnifyService
{
    protected $apiKey;

    protected $secretKey;

    protected $contractCode;

    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.monnify.api_key');
        $this->secretKey = config('services.monnify.secret_key');
        $this->contractCode = config('services.monnify.contract_code');
        $this->baseUrl = 'https://sandbox.monnify.com/api';
    }

    // Method to obtain access token
    public function getAccessToken()
    {
        $credentials = base64_encode("{$this->apiKey}:{$this->secretKey}");

        $response = Http::withHeaders([
            'Authorization' => "Basic {$credentials}",
        ])->post("{$this->baseUrl}/v1/auth/login");

        if ($response->successful()) {
            return $response->json()['responseBody']['accessToken'];
        }

        throw new \Exception('Unable to retrieve Monnify access token.');
    }

    // Method to initiate a payment
    public function createPayment($amount, $details, $currency = 'NGN')
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)->post("{$this->baseUrl}/v1/merchant/transactions/init-transaction", [
            'amount'             => $amount,
            'customerName'       => $details['name'],
            'customerEmail'      => $details['email'],
            'paymentReference'   => (string) Str::uuid(),
            'contractCode'       => $this->contractCode,
            'currencyCode'       => $currency,
            'paymentMethods'     => ['ACCOUNT_TRANSFER'],
            'paymentDescription' => $details['description'],
            'redirectUrl'        => route('monnify.success'),
            'metaData'           => [
                'reference' => $details['reference'] ?? null,
            ],
        ]);

        return $response->json();

        if ($response->successful()) {
            return $response->json()['responseBody']['checkoutUrl'];
        }

        throw new \Exception('Unable to initialize Monnify payment.');
    }

    /**
     * Validate webhook hash
     */
    public function validateWebhookHash(array $payload): bool
    {
        $receivedHash = request()->header('monnify-signature');
        $calculatedHash = hash_hmac('sha512', json_encode($payload), $this->secretKey);

        return hash_equals($calculatedHash, $receivedHash);
    }

    /**
     * Get transaction status
     *
     * @throws Exception
     */
    public function getTransactionStatus(string $transactionReference): array
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)->get("{$this->baseUrl}/v2/merchant/transactions/query?paymentReference={$transactionReference}");

        return $response->json();
    }

    // create bank transfer request
    public function initiateTransfer(array $payload)
    {
        $accessToken = $this->getAccessToken();
        $response = Http::withToken($accessToken)->post("{$this->baseUrl}/v2/disbursements/single", $payload);

        return $response->json();
    }

    // Email OTP For Transfers
    public function authorizeTransfer(array $payload)
    {
        $accessToken = $this->getAccessToken();
        $response = Http::withToken($accessToken)->post("{$this->baseUrl}/v2/disbursements/authorize-transfer", $payload);

        return $response->json();
    }

    // fetch banks
    public function fetchBanks()
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)->get("{$this->baseUrl}/v1/banks");

        return $response->json();
    }

    public function validateName($data)
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)->get("{$this->baseUrl}/v1/disbursements/account/validate", $data);

        return $response->json();
    }
}
