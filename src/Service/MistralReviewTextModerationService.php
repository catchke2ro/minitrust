<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\ReviewTextModerationResult;
use Symfony\Component\HttpClient\HttpClient;

final readonly class MistralReviewTextModerationService implements ReviewTextModerationServiceInterface
{
    private const string API_URL = 'https://api.mistral.ai/v1/chat/completions';

    public function __construct(
        private string $apiKey,
        private string $model = 'ministral-3b-latest',
        private int $timeoutSeconds = 10,
    ) {
    }

    public function analyze(string $text): ReviewTextModerationResult
    {
        if ('' === trim($this->apiKey)) {
            throw new \RuntimeException('Missing MISTRAL_API_KEY environment variable.');
        }

        $payload = [
            'model' => $this->model,
            'temperature' => 0,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You classify user-generated review text for moderation. Always return valid JSON that matches the provided schema.',
                ],
                [
                    'role' => 'user',
                    'content' => $text,
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'review_moderation_result',
                    'schema' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['containsOffensive', 'containsExplicit', 'reason'],
                        'properties' => [
                            'containsOffensive' => ['type' => 'boolean'],
                            'containsExplicit' => ['type' => 'boolean'],
                            'reason' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
        ];

        $httpClient = HttpClient::create();

        try {
            $response = $httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => sprintf('Bearer %s', $this->apiKey),
                ],
                'json' => $payload,
                'timeout' => $this->timeoutSeconds,
            ]);
        } catch (\Throwable $e) {
            throw new \RuntimeException(sprintf('Mistral moderation request failed: %s', $e->getMessage()), previous: $e);
        }

        $statusCode = $response->getStatusCode();
        $responseBody = $response->getContent(false);
        $decodedResponse = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);

        if (200 !== $statusCode) {
            $apiMessage = $decodedResponse['error']['message'] ?? 'Unexpected API error';

            throw new \RuntimeException(sprintf('Mistral moderation API returned HTTP %d: %s', $statusCode, $apiMessage));
        }

        $content = $decodedResponse['choices'][0]['message']['content'] ?? null;
        if (!\is_string($content) || '' === trim($content)) {
            throw new \RuntimeException('Mistral moderation API returned an invalid content payload.');
        }

        $moderation = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return new ReviewTextModerationResult(
            containsOffensive: (bool) ($moderation['containsOffensive'] ?? false),
            containsExplicit: (bool) ($moderation['containsExplicit'] ?? false),
            reason: isset($moderation['reason']) ? (string) $moderation['reason'] : null,
        );
    }
}
