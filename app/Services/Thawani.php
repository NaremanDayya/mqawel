<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Talks to the Thawani checkout API (Oman). The keys and the test/live switch come from .env
 * (see config/services.php), never from the code.
 */
class Thawani
{
    public const URLS = [
        'test' => 'https://uatcheckout.thawani.om',
        'live' => 'https://checkout.thawani.om',
    ];

    /**
     * "live" only when it is asked for in so many words: anything else stays on the test gateway,
     * so a typo can never charge a real card by accident.
     */
    public function mode(): string
    {
        return config('services.thawani.mode') === 'live' ? 'live' : 'test';
    }

    public function baseUrl(): string
    {
        return self::URLS[$this->mode()];
    }

    public function isConfigured(): bool
    {
        return filled(config('services.thawani.secret_key')) && filled(config('services.thawani.publishable_key'));
    }

    /**
     * Open a checkout session; returns its id, or null when Thawani could not be reached or refused.
     *
     * @param  array<string, mixed>  $data
     */
    public function createSession(array $data): ?string
    {
        $response = $this->post('/api/v1/checkout/session', $data);

        return $response?->json('data.session_id');
    }

    /**
     * Where to send the customer to pay for a session.
     */
    public function paymentUrl(string $sessionId): string
    {
        return $this->baseUrl().'/pay/'.$sessionId.'?key='.config('services.thawani.publishable_key');
    }

    /**
     * The session as Thawani reports it right now (payment_status, total_amount...), or null.
     *
     * @return array<string, mixed>|null
     */
    public function session(string $sessionId): ?array
    {
        try {
            $response = $this->request()->get($this->baseUrl().'/api/v1/checkout/session/'.$sessionId);
        } catch (ConnectionException $e) {
            report($e);

            return null;
        }

        return $response->successful() ? $response->json('data') : null;
    }

    /**
     * True only when Thawani itself says this session is paid, and — when it reports an amount —
     * that the amount is the one we asked for (in baisa; 1 OMR = 1000 baisa).
     *
     * @param  array<string, mixed>|null  $session
     */
    public function isPaid(?array $session, int $expectedBaisa): bool
    {
        if (! $session || ($session['payment_status'] ?? null) !== 'paid') {
            return false;
        }

        return ! isset($session['total_amount']) || (int) $session['total_amount'] === $expectedBaisa;
    }

    private function request(): PendingRequest
    {
        return Http::withHeaders(['thawani-api-key' => (string) config('services.thawani.secret_key')])
            ->acceptJson()
            ->timeout(20);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function post(string $path, array $data): ?\Illuminate\Http\Client\Response
    {
        try {
            $response = $this->request()->post($this->baseUrl().$path, $data);
        } catch (ConnectionException $e) {
            report($e);

            return null;
        }

        return $response->successful() ? $response : null;
    }
}
