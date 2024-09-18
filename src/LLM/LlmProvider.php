<?php

namespace Package\DocTalk\LLM;

interface LlmProvider
{
    public function embed(array $texts, string $embeddingModel): array|string;

    public function chat(string $message, bool $stream = false): mixed;
}
