<?php

namespace App\Services;

use GuzzleHttp\Client;

class BrevoMailer
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.brevo.com/v3/',
            'headers' => [
            'api-key' => env('BREVO_API_KEY'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            ]
        ]);
    }

    public function sendEmail(string $to, string $subject, string $htmlContent): array
    {
        $response = $this->client->post('smtp/email', [
            'json' => [
                'sender' => [
                    'email' => env('MAIL_FROM_ADDRESS'),
                    'name'  => env('MAIL_FROM_NAME'),
                ],
                'to' => [
                    ['email' => $to],
                ],
                'subject' => $subject,
                'htmlContent' => $htmlContent,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
