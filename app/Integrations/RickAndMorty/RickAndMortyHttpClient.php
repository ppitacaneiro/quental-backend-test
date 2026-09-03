<?php

namespace App\Integrations\RickAndMorty;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class RickAndMortyHttpClient implements RickAndMortyClient
{
    private const BASE_URL = 'https://rickandmortyapi.com/api';

    private const RATE_LIMIT_STATUS = 429;

    private function client(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->timeout(10)
            ->retry(
                3,
                fn (int $attempt, \Exception $exception) => $this->retryDelay($attempt, $exception),
                fn (\Exception $exception) => $this->shouldRetry($exception)
            );
    }

    private function shouldRetry(\Exception $exception): bool
    {
        return $exception instanceof ConnectionException
            || ($exception instanceof RequestException
                && $exception->response->status() === self::RATE_LIMIT_STATUS);
    }

    private function retryDelay(int $attempt, \Exception $exception): int
    {
        if ($exception instanceof RequestException
            && $exception->response->status() === self::RATE_LIMIT_STATUS) {
            $retryAfter = $exception->response->header('Retry-After');

            if (is_numeric($retryAfter)) {
                return (int) $retryAfter * 1000;
            }
        }

        // backoff exponencial cuando no hay cabecera Retry-After
        return 200 * (2 ** ($attempt - 1));
    }

    public function getCharacters(int $page = 1): array
    {
        return $this->client()
            ->get('/character', ['page' => $page])
            ->throw()
            ->json();
    }

    public function getEpisodes(int $page = 1): array
    {
        return $this->client()
            ->get('/episode', ['page' => $page])
            ->throw()
            ->json();
    }

    public function getLocations(int $page = 1): array
    {
        return $this->client()
            ->get('/location', ['page' => $page])
            ->throw()
            ->json();
    }
}