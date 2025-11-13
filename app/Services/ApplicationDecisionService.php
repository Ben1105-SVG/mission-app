<?php

namespace App\Services;

class ApplicationDecisionService
{
    /**
     * Evaluate an inquiry and answers and return structured decision.
     *
     * @param  \App\Models\Inquiry  $inquiry
     * @param  array                $answers  // associative: semantic keys and/or question ids
     * @return array ['status' => 'green|yellow|red', 'flags' => [], 'score' => int, 'explanations' => []]
     */
    public static function evaluate($inquiry, array $answers): array
    {
        $flags = [];
        $explanations = [];
        $score = 0;

        // define weights (negative reduces score)
        $weights = [
            'lgbtq' => -2,               // sensitive — heavier weight
            'first_trip' => -1,
            'uncomfortable_praying' => -1,
            'never_shared_faith' => -1,
            'new_leader' => -1,
        ];

        // Helper: mark a flag
        $mark = function ($key, $message) use (&$flags, &$explanations, $weights, &$score) {
            $flags[$key] = $message;
            $explanations[$key] = $message;
            $score += $weights[$key] ?? -1;
        };

        // 1) leader identifies as LGBTQ+
        if (!empty($inquiry->lgbtq)) {
            $mark('lgbtq', 'Group leader identifies as LGBTQ+.');
        }

        // 2) first mission trip
        if (!empty($inquiry->first_trip)) {
            $mark('first_trip', 'This is the group leader’s first mission trip.');
        }

        // 3) uncomfortable praying — check answers by semantic key or question id
        $praying = $answers['praying'] ?? $answers['prayer'] ?? null;
        if ($praying !== null) {
            $praying_normalized = strtolower(trim($praying));
            if (in_array($praying_normalized, ['uncomfortable','somewhat_uncomfortable','not comfortable','no'])) {
                $mark('uncomfortable_praying', 'Leader is uncomfortable praying in public.');
            }
        }

        // 4) never shared faith
        $faith = $answers['faith_sharing'] ?? $answers['share_your_faith'] ?? null;
        if ($faith !== null) {
            $faith_normalized = strtolower(trim($faith));
            if (in_array($faith_normalized, ['never','no','never shared'])) {
                $mark('never_shared_faith', 'Leader has never shared their faith.');
            }
        }

        // 5) role less than 1 year
        if (!is_null($inquiry->role_duration) && is_numeric($inquiry->role_duration)) {
            if ($inquiry->role_duration < 1) {
                $mark('new_leader', 'Leader has been in role for less than one year.');
            }
        }

        // fallback: if there are other question ids that map to flags, check them too
        // e.g. answers['previous_experience'] === 'no' -> first_trip
        $prev = $answers['previous_experience'] ?? $answers['participated_before'] ?? null;
        if ($prev !== null && in_array(strtolower(trim($prev)), ['no','never'])) {
            if (!array_key_exists('first_trip', $flags)) {
                $mark('first_trip', 'No previous mission trip experience reported.');
            }
        }

        // default baseline score: start at +2 bias toward green, then apply negatives
        $score += 2;

        // determine status using score thresholds (tweakable)
        // >= 1  => green,  0..-1 => yellow, <= -2 => red
        if ($score >= 1) {
            $status = 'green';
        } elseif ($score >= 0 && $score < 1) {
            $status = 'yellow';
        } else {
            $status = 'red';
        }

        return [
            'status' => $status,
            'flags' => array_values($flags),
            'score' => $score,
            'explanations' => $explanations,
        ];
    }
}
