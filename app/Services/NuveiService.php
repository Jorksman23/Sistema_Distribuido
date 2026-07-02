<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class NuveiService
{
    protected string $clientId;
    protected string $serverKey;
    protected string $initUrl;
    protected int $expiration;

    public function __construct()
    {
        $config = config('payments.nuvei_config');

        $this->clientId   = $config['client_id'] ?? '';
        $this->serverKey  = $config['server_key'] ?? '';
        $this->initUrl    = $config['init_url'] ?? 'https://noccapi-stg.paymentez.com/linktopay/init_order/';
        $this->expiration = (int) ($config['expiration'] ?? 36000);
    }

    /**
     * Construye el Auth-Token que exige Nuvei en el header.
     *
     * Algoritmo (verificado en doc oficial de Paymentez/Nuvei):
     *   1. unix_timestamp = timestamp actual en segundos
     *   2. uniq_token_string = server_key + unix_timestamp
     *   3. uniq_token_hash = SHA-256(uniq_token_string) en hexadecimal
     *   4. auth_token = base64( "client_id;unix_timestamp;uniq_token_hash" )
     */
    protected function generarAuthToken(): string
    {
        $timestamp       = (string) time();
        $uniqTokenString = $this->serverKey . $timestamp;
        $uniqTokenHash   = hash('sha256', $uniqTokenString); // hex por defecto

        $plain = "{$this->clientId};{$timestamp};{$uniqTokenHash}";

        return base64_encode($plain);
    }

    /**
     * Genera el enlace de pago (Link to Pay) en Nuvei.
     *
     * @param array  $orderData   Datos de la orden y montos:
     *                            - dev_reference (string): tu codigoOrden
     *                            - description   (string)
     *                            - amount        (float): total a pagar
     *                            - vat           (float): monto del IVA
     *                            - tax_percentage(float): % de IVA
     *                            - taxable_amount(float): base gravable
     * @param array  $userData    Datos del cliente:
     *                            - id, email, name, last_name
     * @param array  $urls        success_url, failure_url, pending_url, review_url
     *
     * @return array  ['success' => bool, 'payment_url' => string|null,
     *                 'order_id' => string|null, 'error' => string|null, 'raw' => array]
     */
    public function generarLinkPago(array $orderData, array $userData, array $urls): array
    {
        $payload = [
            'user' => [
                'id'        => (string) ($userData['id'] ?? ''),
                'email'     => $userData['email'] ?? '',
                'name'      => $userData['name'] ?? '',
                'last_name' => $userData['last_name'] ?? '',
            ],
            'order' => [
                'dev_reference'     => (string) $orderData['dev_reference'],
                'description'       => $orderData['description'] ?? 'Compra web',
                'amount'            => round((float) $orderData['amount'], 2),
                'vat'               => round((float) ($orderData['vat'] ?? 0), 2),
                'tax_percentage'    => round((float) ($orderData['tax_percentage'] ?? 0), 2),
                'taxable_amount'    => round((float) ($orderData['taxable_amount'] ?? 0), 2),
                'installments_type' => 0,
                'currency'          => 'USD',
            ],
            'configuration' => [
                'partial_payment'         => false,
                'expiration_time'         => $this->expiration,
                'allowed_payment_methods' => ['All'],
                'success_url'             => $urls['success_url'] ?? '',
                'failure_url'             => $urls['failure_url'] ?? '',
                'pending_url'             => $urls['pending_url'] ?? '',
                'review_url'              => $urls['review_url'] ?? '',
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Auth-Token'   => $this->generarAuthToken(),
                'Content-Type' => 'application/json',
            ])->timeout(20)->post($this->initUrl, $payload);

            $json = $response->json() ?? [];

            // Log para depuración (sin exponer credenciales)
            Log::info('Nuvei init_order response', [
                'status'        => $response->status(),
                'dev_reference' => $orderData['dev_reference'],
                'body'          => $json,
            ]);

            // Respuesta exitosa: trae data.payment.payment_url
            $paymentUrl = $json['data']['payment']['payment_url'] ?? null;
            $orderId    = $json['data']['order']['id'] ?? null;

            if ($response->successful() && $paymentUrl) {
                return [
                    'success'     => true,
                    'payment_url' => $paymentUrl,
                    'order_id'    => $orderId,
                    'error'       => null,
                    'raw'         => $json,
                ];
            }

            // Falló: devolvemos el detalle que mande Nuvei
            return [
                'success'     => false,
                'payment_url' => null,
                'order_id'    => null,
                'error'       => $json['detail'] ?? 'Respuesta inesperada de Nuvei',
                'raw'         => $json,
            ];
        } catch (Throwable $e) {
            Log::error('Nuvei init_order exception: ' . $e->getMessage(), [
                'dev_reference' => $orderData['dev_reference'] ?? null,
            ]);

            return [
                'success'     => false,
                'payment_url' => null,
                'order_id'    => null,
                'error'       => 'Error de conexión con la pasarela: ' . $e->getMessage(),
                'raw'         => [],
            ];
        }
    }
}
