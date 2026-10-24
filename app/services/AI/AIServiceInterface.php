<?php
/**
 * AIServiceInterface - contract that any AI backend must implement
 * so the rest of the app is decoupled from whether the engine is
 * a local rule-based system or an external LLM API.
 */
interface AIServiceInterface
{
    /** Analyze free text describing a problem and suggest category/service */
    public function analyzeProblem(string $text): array;
}
