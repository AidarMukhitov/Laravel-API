<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * AIService provides AI-powered sentiment analysis via OpenAI API.
 *
 * Graceful fallback: if the API is unavailable, times out, or the key is missing,
 * the method returns "neutral" and logs the error — never throwing to the caller.
 */
class AIService
{
    protected const DEFAULT_SENTIMENT = 'neutral';
    protected const DEFAULT_MODEL = 'gpt-4o-mini';
    protected const TIMEOUT_SECONDS = 10;

    protected ?string $apiKey;
    protected string $model;
    protected Client $httpClient;

    public function __construct(?Client $httpClient = null)
    {
        $this->apiKey = env('OPENAI_API_KEY');
        $this->model = env('OPENAI_MODEL', self::DEFAULT_MODEL);
        $this->httpClient = $httpClient ?? new Client([
            'timeout' => self::TIMEOUT_SECONDS,
            'connect_timeout' => 5,
        ]);
    }

    /**
     * Analyze the sentiment of a comment.
     *
     * Returns one of: "positive", "negative", "neutral".
     * Never throws — always returns a string (falls back to "neutral").
     *
     * @param  string  $comment  The user's comment text
     * @param  string  $name     The user's name (used for personalization)
     * @return string Sentiment label
     */
    public function analyzeSentiment(string $comment, string $name = ''): string
    {
        // If no API key is configured, skip and return default
        if (empty($this->apiKey)) {
            Log::channel('ai')->warning(
                'OpenAI API key not configured. Returning default sentiment.',
                ['default' => self::DEFAULT_SENTIMENT]
            );

            return self::DEFAULT_SENTIMENT;
        }

        try {
            $response = $this->httpClient->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => $this->buildMessages($comment, $name),
                    'temperature' => 0.0, // Deterministic output
                    'max_tokens' => 20,
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            // Extract and normalize the sentiment
            $raw = trim($body['choices'][0]['message']['content'] ?? '');

            return $this->normalizeSentiment($raw);
        } catch (RequestException $e) {
            $this->logError('Request error during sentiment analysis', $e);
        } catch (GuzzleException $e) {
            $this->logError('HTTP error during sentiment analysis', $e);
        } catch (\Throwable $e) {
            $this->logError('Unexpected error during sentiment analysis', $e);
        }

        return self::DEFAULT_SENTIMENT;
    }

    /**
     * Build the system + user messages for the OpenAI call.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildMessages(string $comment, string $name): array
    {
        $greeting = $name ? " from {$name}" : '';

        return [
            [
                'role' => 'system',
                'content' => 'You are a sentiment analysis assistant. Respond with ONLY one word: positive, negative, or neutral. Nothing else.',
            ],
            [
                'role' => 'user',
                'content' => "Analyze the sentiment of this contact form comment{$greeting}:\n\n\"{$comment}\"\n\nRespond with only: positive, negative, or neutral.",
            ],
        ];
    }

    /**
     * Normalize the raw AI response to one of the allowed labels.
     */
    protected function normalizeSentiment(string $raw): string
    {
        $lower = strtolower(trim($raw));

        if (str_contains($lower, 'positive')) {
            return 'positive';
        }

        if (str_contains($lower, 'negative')) {
            return 'negative';
        }

        return self::DEFAULT_SENTIMENT;
    }

    /**
     * Log the error and return nothing (caller handles fallback).
     */
    protected function logError(string $message, \Throwable $e): void
    {
        Log::channel('ai')->error($message, [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
        ]);
    }
}
