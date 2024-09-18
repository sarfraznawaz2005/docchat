<?php

namespace Package\DocTalk\LLM;

class OllamaProvider extends BaseLLMProvider
{
    use OpenAICompatibleTrait;

    public function __construct(string $apiKey, string $model, array $options = [], int $retries = 1)
    {
        parent::__construct($apiKey, 'http://127.0.0.1:11434/v1/', $model, $options, $retries);
    }
}
