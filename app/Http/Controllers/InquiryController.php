<?php

namespace App\Http\Controllers;

use App\Services\ApplicationDecisionService;
use App\Models\Inquiry;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\PushToActiveCampaignJob;

class InquiryController extends Controller
{
    /**
     * Store a newly created inquiry and process the decision via GroqClient AI.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'phone' => 'nullable|string|max:50',
            'group_leader_role' => 'nullable|string|max:191',
            'role_duration' => 'nullable|numeric|min:0',
            'answers' => 'required|array',
            'answers.*' => 'nullable',
        ]);

        $decision = ApplicationDecisionService::evaluate($data['answers']);

        // 2️⃣ Save inquiry and answers in transaction
        return DB::transaction(function () use ($data, $decision) {
            $inquiry = Inquiry::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'group_leader_role' => $data['group_leader_role'] ?? null,
                'role_duration' => isset($data['role_duration']) ? (float) $data['role_duration'] : null,
            ]);

            foreach ($data['answers'] as $questionId => $answer) {
                if (is_numeric($questionId)) {
                    Answer::create([
                        'inquiry_id' => $inquiry->id,
                        'question_id' => (int) $questionId,
                        'answer' => is_array($answer) ? json_encode($answer) : (string) $answer,
                    ]);
                }
            }

            // Prepare flags data to save (including explanations)
            $flagsToSave = [
                'flags' => $decision['flags'] ?? [],
                'explanations' => $decision['explanations'] ?? [],
                'score' => $decision['score'] ?? null,
                'colors' => $decision['colors'] ?? null,
            ];

            // Ensure explanations is always an array (not null) for consistency
            if (!is_array($flagsToSave['explanations'])) {
                $flagsToSave['explanations'] = [];
            }

            $inquiry->status = $decision['status'] ?? 'yellow';
            // Use array directly - Laravel will auto-encode due to 'flags' => 'array' cast
            $inquiry->flags = $flagsToSave;
            $inquiry->save();

            // 4️⃣ Dispatch job to ActiveCampaign for Yellow and Red statuses
            // Green statuses are auto-approved and don't need follow-up
            if (in_array($inquiry->status, ['yellow', 'red'], true)) {
                dispatch(new PushToActiveCampaignJob($inquiry));
            }

            // 5️⃣ Return JSON response with appropriate message and actions
            return response()->json([
                'status' => $inquiry->status,
                'flags' => $decision['flags'] ?? [],
                'explanations' => $decision['explanations'] ?? null,
                'score' => $decision['score'] ?? null,
                'colors' => $decision['colors'] ?? null,
                'message' => $this->getMessageByStatus($inquiry->status),
                'signup_link' => $inquiry->status === 'green'
                    ? config('missions.signup_url', 'https://trip-signup-link.com')
                    : null,
                'keyStrengths' => $decision['keyStrengths'] ?? [],
                'keyConcerns' => $decision['keyConcerns'] ?? [],
            ], 201);
        }, 5);
    }

    private function getMessageByStatus(string $status): string
    {
        return match($status) {
            'green' => 'Congratulations! Your application has been approved. You can now sign up for the trip and pay your deposit using the link below.',
            'yellow' => 'Thank you for your application! A member of our team will reach out to you soon to discuss next steps and answer any questions you may have.',
            'red' => 'Thank you for your interest in our mission trip. While we\'re not able to move forward with this particular opportunity at this time, we\'d love to stay connected and explore other ways you can be involved in our mission work. Our team will be in touch soon.',
            default => 'Thank you for your application. We will review it and get back to you soon.',
        };
    }
}
