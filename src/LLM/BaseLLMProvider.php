<?php

namespace Package\DocTalk\LLM;

use Exception;
use Illuminate\Support\Facades\Log;

abstract class BaseLLMProvider implements LlmProvider
{
    protected string $apiKey;
    protected string $baseUrl;
    protected array $options;
    protected int $retries;
    public string $model;

    public function __construct(string $apiKey, string $baseUrl, string $model, array $options = [], int $retries = 1)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
        $this->model = $model;
        $this->options = $options;
        $this->retries = $retries;
    }

    /**
     * @throws Exception
     */
    protected function makeRequest(string $url, array $body, bool $stream = false, $useBearer = false, ?callable $callback = null): mixed
    {
        $headers = [
            'Content-Type: application/json',
        ];

        if ($useBearer) {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }

        for ($attempt = 0; $attempt < $this->retries; $attempt++) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, !$stream);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

            if ($stream) {
                curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use ($callback) {
                    static::getStreamingResponse($data, $callback);
                    return strlen($data);
                });
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            // dd($response);

            if ($httpCode >= 200 && $httpCode < 300) {
                return json_decode($response, true);
            }

            // If not successful and retries remain, sleep before retrying
            if ($attempt < $this->retries - 1) {
                sleep(1);
            } else {
                // Decode the response to get the error message
                $errorMessage = 'Unknown error';

                if ($response) {
                    $responseBody = json_decode($response, true);

                    if (json_last_error() === JSON_ERROR_NONE && isset($responseBody['error'])) {
                        $errorMessage = $responseBody['error']['message'] ?? $errorMessage;
                    }
                }

                $error = "Failed to get a successful response after $this->retries attempts. Error: $errorMessage";
                Log::error($error);

                throw new Exception($error);
            }
        }

        return 'Unknow Error';
    }

    protected function fixJson($json): string
    {
        $json = ltrim($json, '[,');
        $json = rtrim($json, '],');
        $json = rtrim($json, '],');

        $json = str_ireplace('data:', '', $json);

        return trim($json);
    }
}
