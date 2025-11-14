<?php

namespace App\Services;

use App\Services\GroqClient;
use Illuminate\Support\Facades\Log;

class ApplicationDecisionService
{
    /**
     * Evaluate answers and return decision via GroqClient.
     * Includes AI-generated colors based on answers.
     */
    public static function evaluate($inquiry, array $answers): array
    {
        $prompt = self::buildPrompt($answers);
        $client = GroqClient::client();

        try {
            $response = $client->chat()->create([
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an assistant that evaluates applications as green, yellow, or red. Return a JSON object with keys: status, score, flags, explanations, colors. The colors key should contain: primary (hex color for main status), secondary (hex color for accent), and answerColors (object mapping question IDs to hex colors based on each answer). Generate meaningful colors that reflect the sentiment and safety level of each answer.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

            $text = $response->choices[0]->message->content ?? '';

            // Clean JSON block if wrapped in markdown
            $trimmed = trim($text);
                $trimmed = preg_replace('/^```json\s*/i', '', $trimmed);
                $trimmed = preg_replace('/^```\s*/', '', $trimmed);
                $trimmed = preg_replace('/\s*```$/', '', $trimmed);

            // Extract JSON from text
                if (preg_match('/\{.*\}/s', $trimmed, $m)) {
                    $jsonText = $m[0];
                } else {
                    $jsonText = $trimmed;
                }

            // Parse AI response as JSON
            $decision = json_decode($jsonText, true);

            // fallback if parsing fails
            if (!$decision || !isset($decision['status'])) {
                $decision = [
                    'status' => 'yellow',
                    'score' => null,
                    'flags' => [],
                    'explanations' => ['general' => 'Could not determine automatically.'],
                    'colors' => self::getDefaultColors('yellow'),
                ];
            }

            // sanitize fields
            $decision['flags'] = is_array($decision['flags'] ?? null) ? array_values($decision['flags']) : [];
            $decision['explanations'] = is_array($decision['explanations'] ?? null) ? $decision['explanations'] : [];
            $decision['score'] = isset($decision['score']) ? max(0, min(100, (int)$decision['score'])) : null;
            
            // Ensure colors are present and properly formatted
            if (!isset($decision['colors']) || !is_array($decision['colors'])) {
                $decision['colors'] = self::getDefaultColors($decision['status'] ?? 'yellow');
            } else {
                // Validate and set default colors if missing
                $decision['colors'] = array_merge(
                    self::getDefaultColors($decision['status'] ?? 'yellow'),
                    $decision['colors']
                );
            }

        } catch (\Throwable $e) {
            Log::error('GroqClient evaluation failed', [
                'exception' => $e->getMessage(),
                'answers' => $answers,
            ]);

            $decision = [
                'status' => 'yellow',
                'score' => null,
                'flags' => [],
                'explanations' => ['general' => 'Could not determine automatically.'],
                'colors' => self::getDefaultColors('yellow'),
            ];
        }

        return $decision;
    }

    private static function buildPrompt(array $answers): string
    {
        $formattedAnswers = [];
        foreach ($answers as $questionId => $answer) {
            $formattedAnswers[] = "Q{$questionId}: " . (is_array($answer) ? implode(', ', $answer) : $answer);
        }

        return "Evaluate the applicant based on these answers. Return a JSON object with the following structure:
{
  \"status\": \"green\" | \"yellow\" | \"red\",
  \"score\": 0-100,
  \"flags\": [\"array\", \"of\", \"flag\", \"strings\"],
  \"explanations\": {\"flag_key\": \"explanation text\"},
  \"colors\": {
    \"primary\": \"#hexcolor\" (main status color - green for approved, yellow for review, red for concerns),
    \"secondary\": \"#hexcolor\" (accent color that complements primary),
    \"answerColors\": {
      \"questionId\": \"#hexcolor\" (color for each answer based on sentiment: positive=greens, neutral=yellows, negative=reds)
    }
  }
}

Generate meaningful colors:
- Primary: Use #22c55e (green) for 'green' status, #eab308 (yellow) for 'yellow', #ef4444 (red) for 'red', or create variations
- Secondary: Choose complementary colors (e.g., lighter/darker shades, or contrasting accents)
- AnswerColors: Assign colors to each question ID based on answer sentiment:
  * Positive/safe answers: green shades (#22c55e, #16a34a, #15803d)
  * Neutral/moderate answers: yellow/amber shades (#eab308, #f59e0b, #d97706)
  * Negative/concerning answers: red/orange shades (#ef4444, #f97316, #dc2626)
  * Missing/unknown: gray shades (#6b7280, #9ca3af)

Answers:\n"
            . implode("\n", $formattedAnswers);
    }

    /**
     * Get default colors based on status.
     */
    private static function getDefaultColors(string $status): array
    {
        $defaults = [
            'green' => [
                'primary' => '#22c55e',
                'secondary' => '#16a34a',
                'answerColors' => [],
            ],
            'yellow' => [
                'primary' => '#eab308',
                'secondary' => '#f59e0b',
                'answerColors' => [],
            ],
            'red' => [
                'primary' => '#ef4444',
                'secondary' => '#f97316',
                'answerColors' => [],
            ],
        ];

        return $defaults[$status] ?? $defaults['yellow'];
    }
}
