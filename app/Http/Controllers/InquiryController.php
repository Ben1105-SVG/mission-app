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
     * Store a newly created inquiry and process the decision.
     */
    public function store(Request $request)
    {
        // validate
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'phone' => 'nullable|string|max:50',
            'group_leader_role' => 'nullable|string|max:191',
            'role_duration' => 'nullable|numeric',
            'answers' => 'required|array',
        ]);

        return DB::transaction(function () use ($data) {
            $inquiry = Inquiry::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'group_leader_role' => $data['group_leader_role'] ?? null,
                'role_duration' => isset($data['role_duration']) ? floatval($data['role_duration']) : null,
            ]);

            foreach ($data['answers'] as $key => $value) {
                if (is_numeric($key)) {
                    Answer::create([
                        'inquiry_id' => $inquiry->id,
                        'question_id' => (int) $key,
                        'answer' => is_array($value) ? json_encode($value) : (string) $value,
                    ]);
                }
            }

            $decision = ApplicationDecisionService::evaluate($inquiry, $data['answers'] ?? []);
            $flagsToSave = null;
            if (isset($decision['flags']) && (is_array($decision['flags']) || is_object($decision['flags']))) {
                // try to include explanations/score if present
                $flagsToSave = [
                    'flags' => $decision['flags'],
                ];
                if (isset($decision['explanations'])) $flagsToSave['explanations'] = $decision['explanations'];
                if (isset($decision['score'])) $flagsToSave['score'] = $decision['score'];
            } else {
                $flagsToSave = ['flags' => $decision['flags'] ?? []];
            }

            $inquiry->status = $decision['status'] ?? 'yellow';
            $inquiry->flags = json_encode($flagsToSave);
            $inquiry->save();

            if (isset($decision['status']) && in_array($decision['status'], ['yellow', 'red'], true)) {

                dispatch(new PushToActiveCampaignJob($inquiry));
            }

            return response()->json([
                'status' => $inquiry->status,
                'flags' => $decision['flags'] ?? [],
                'explanations' => $decision['explanations'] ?? null,
                'score' => $decision['score'] ?? null,
                'message' => $this->getMessageByStatus($inquiry->status),
                'signup_link' => ($inquiry->status === 'green') ? config('missions.signup_url', 'https://trip-signup-link.com') : null,
            ], 201);
        }, 5);
    }

    /**
     * Optional: return a friendly message based on status.
     */
    private function getMessageByStatus($status)
    {
        switch ($status) {
            case 'green':
                return 'Congratulations! You are approved. You can sign up for the trip.';
            case 'yellow':
                return 'Thanks for applying! Someone will follow up with you soon.';
            case 'red':
                return 'Thank you for your interest. We will be in touch for other opportunities.';
            default:
                return '';
        }
    }
}
