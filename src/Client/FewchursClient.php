<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Client;

use Fewchurs\Laravel\Events\RequestFailed;
use Fewchurs\Laravel\Exceptions\ConnectionException as FewchursConnectionException;
use Fewchurs\Laravel\Exceptions\FewchursException;
use Fewchurs\Laravel\Exceptions\ServerException;
use Fewchurs\Laravel\Identity\DeviceIdResolver;
use Fewchurs\Laravel\Support\Redactor;
use Fewchurs\Laravel\Support\Version;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

final class FewchursClient
{
    private ?string $deviceIdOverride = null;

    /**
     * @param  array<string, mixed>  $http
     */
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly array $http,
        private readonly DeviceIdResolver $identity,
    ) {
        if ($this->apiKey === '') {
            throw new RuntimeException('FEWCHURS_API_KEY is not set.');
        }

        if (! Str::startsWith($this->baseUrl, 'https://') && ! app()->environment('local', 'testing')) {
            throw new RuntimeException('FEWCHURS_BASE_URL must use https outside local development.');
        }
    }

    public function withDeviceId(?string $deviceId): self
    {
        $clone = clone $this;
        $clone->deviceIdOverride = $deviceId;

        return $clone;
    }

    public function deviceId(): string
    {
        return $this->deviceIdOverride ??= $this->identity->resolve(request());
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = [], bool $withDevice = true): array
    {
        return $this->send('get', $path, ['query' => array_filter($query, fn ($v) => $v !== null)], $withDevice, true);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function post(string $path, array $payload = [], bool $idempotent = false): array
    {
        return $this->send('post', $path, ['json' => (object) $payload], true, $idempotent);
    }

    /**
     * @return array<string, mixed>
     */
    public function delete(string $path): array
    {
        return $this->send('delete', $path, ['json' => (object) []], true, true);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function send(string $method, string $path, array $options, bool $withDevice, bool $retryable): array
    {
        $request = Http::asJson()
            ->acceptJson()
            ->withHeaders($this->headers($withDevice))
            ->withOptions(['verify' => (bool) ($this->http['verify'] ?? true)])
            ->timeout((int) ($this->http['timeout'] ?? 8))
            ->connectTimeout((int) ($this->http['connect_timeout'] ?? 4));

        if ($retryable) {
            $request = $request->retry(
                max(1, (int) ($this->http['retries'] ?? 2)),
                (int) ($this->http['retry_delay'] ?? 200),
                fn ($exception) => $exception instanceof ConnectionException,
                throw: false,
            );
        }

        $url = $this->url($path);

        try {
            $response = match ($method) {
                'get' => $request->get($url, $options['query'] ?? []),
                'post' => $request->post($url, $options['json'] ?? []),
                'delete' => $request->delete($url, $options['json'] ?? []),
                default => throw new RuntimeException("Unsupported method [{$method}]."),
            };
        } catch (ConnectionException $e) {
            throw $this->report(new FewchursConnectionException(
                'Could not reach the Fewchurs API.', 'connection', 0, $path, $e,
            ));
        }

        if ($response->failed()) {
            throw $this->report($this->toException($response, $path));
        }

        $decoded = $response->json();

        return is_array($decoded) ? $decoded : [];
    }

    private function toException(Response $response, string $path): FewchursException
    {
        $envelope = $response->json('error');
        $code = is_array($envelope) ? (string) ($envelope['code'] ?? '') : '';
        $message = is_array($envelope) ? (string) ($envelope['message'] ?? '') : '';

        if ($code === '') {
            return new ServerException(
                'Unexpected response from the Fewchurs API.', 'internal', $response->status(), $path,
            );
        }

        return FewchursException::fromEnvelope($code, $message, $response->status(), $path);
    }

    private function report(FewchursException $exception): FewchursException
    {
        $context = Redactor::scrubContext([
            'code' => $exception->errorCode,
            'status' => $exception->status,
            'endpoint' => $exception->endpoint,
            'device' => substr($this->deviceIdOverride ?? '', 0, 12),
        ]);

        Log::channel(config('fewchurs.logging.channel') ?: config('logging.default'))
            ->log($exception->isCritical() ? 'critical' : 'warning', 'Fewchurs request failed', $context);

        Event::dispatch(new RequestFailed($exception));

        return $exception;
    }

    /**
     * @return array<string, string>
     */
    private function headers(bool $withDevice): array
    {
        $headers = [
            'Authorization' => 'Bearer '.$this->apiKey,
            'X-Fewchurs-Sdk' => Version::userAgent(),
            'Accept-Language' => app()->getLocale(),
        ];

        if ($withDevice) {
            $headers['X-Device-Id'] = $this->deviceId();
        }

        return $headers;
    }

    private function url(string $path): string
    {
        return rtrim($this->baseUrl, '/').'/'.ltrim($path, '/');
    }
}
