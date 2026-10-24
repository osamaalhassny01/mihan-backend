<?php
interface AIProviderInterface
{
    public function name(): string;
    public function analyzeProblem(string $text): array;
    public function chat(array $messages, array $options = []): string;
    public function testConnection(): array;
}
