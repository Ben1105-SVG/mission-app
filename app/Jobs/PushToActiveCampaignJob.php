<?php

namespace App\Jobs;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PushToActiveCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?int $inquiryId = null;

    public $tries = 3;

    public $backoff = [30, 120, 300];

    public function __construct(Inquiry $inquiry)
    {
        $this->inquiryId = $inquiry->id;
    }

    public function handle(): void
    {
        // Handle legacy jobs that might have been serialized with the old format
        if (!$this->inquiryId) {
            Log::error('Inquiry ID missing in ActiveCampaign job');
            return;
        }
        
        // Load the inquiry from the database
        $inquiry = Inquiry::find($this->inquiryId);
        
        if (!$inquiry) {
            Log::error('Inquiry not found for ActiveCampaign job', ['inquiry_id' => $this->inquiryId]);
            return;
        }
        
        $baseUrl = rtrim(config('services.activecampaign.url', ''), '/');
        $apiKey  = config('services.activecampaign.key');

        if (empty($baseUrl) || empty($apiKey)) {
            Log::warning('ActiveCampaign config missing; skipping push', ['inquiry_id' => $inquiry->id]);
            return;
        }

        $contactId = $this->syncContact($baseUrl, $apiKey, $inquiry);

        if ($contactId) {
            $this->assignTag($baseUrl, $apiKey, $contactId, $inquiry);
            $this->assignAutomation($baseUrl, $apiKey, $contactId, $inquiry);
        } else {
            Log::warning('Contact ID missing, skipping tag & automation', ['inquiry_id' => $inquiry->id]);
        }
    }

    private function syncContact(string $baseUrl, string $apiKey, Inquiry $inquiry): ?int
    {
        $payload = [
            'contact' => [
                'email' => $inquiry->email,
                'firstName' => $inquiry->name,
                'phone' => $inquiry->phone,
            ],
        ];

        try {
            $response = Http::withHeaders(['Api-Token' => $apiKey])
                ->post($baseUrl . '/api/3/contact/sync', $payload);

            if (!$response->successful()) {
                Log::error('ActiveCampaign contact sync failed', [
                    'inquiry_id' => $inquiry->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException('Contact sync failed: ' . $response->status());
            }

            $contactId = data_get($response->json(), 'contact.id');

            if ($contactId) {
                $inquiry->forceFill(['ac_contact_id' => $contactId])->save();
                
                // Add all form details as custom field values
                $this->addCustomFields($baseUrl, $apiKey, $contactId, $inquiry);
            } else {
                Log::warning('ActiveCampaign sync returned no contact id', [
                    'inquiry_id' => $inquiry->id,
                    'response' => $response->json(),
                ]);
            }

            return $contactId;
        } catch (Throwable $e) {
            Log::error('ActiveCampaign sync exception', [
                'inquiry_id' => $inquiry->id,
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function addCustomFields(string $baseUrl, string $apiKey, int $contactId, Inquiry $inquiry): void
    {
        try {
            // Get all answers for this inquiry
            $answers = $inquiry->answers()->with('question')->get();
            
            // Build a comprehensive note with all form details for follow-up
            $statusLabel = strtoupper($inquiry->status ?? 'UNKNOWN');
            $noteContent = "=== MISSION TRIP APPLICATION ===\n";
            $noteContent .= "Status: {$statusLabel}\n";
            $noteContent .= "Submitted: " . $inquiry->created_at->format('F j, Y \a\t g:i A') . "\n\n";
            
            $noteContent .= "--- Contact Information ---\n";
            $noteContent .= "Name: {$inquiry->name}\n";
            $noteContent .= "Email: {$inquiry->email}\n";
            if ($inquiry->phone) {
                $noteContent .= "Phone: {$inquiry->phone}\n";
            }
            
            if ($inquiry->group_leader_role) {
                $noteContent .= "\n--- Leadership Information ---\n";
                $noteContent .= "Group Leader Role: {$inquiry->group_leader_role}\n";
                if ($inquiry->role_duration) {
                    $noteContent .= "Role Duration: {$inquiry->role_duration} years\n";
                }
            }
            
            // Add AI evaluation summary if available
            $flags = $inquiry->flags ?? [];
            if (isset($flags['explanations']['internal'])) {
                $noteContent .= "\n--- AI Evaluation Summary ---\n";
                $noteContent .= "{$flags['explanations']['internal']}\n";
            }
            
            // Add key concerns if available
            if (isset($flags['flags']) && is_array($flags['flags']) && count($flags['flags']) > 0) {
                $noteContent .= "\n--- Key Concerns ---\n";
                foreach ($flags['flags'] as $concern) {
                    $noteContent .= "• {$concern}\n";
                }
            }
            
            // Add all question answers for complete context
            $noteContent .= "\n--- Complete Form Responses ---\n";
            foreach ($answers as $answer) {
                $questionText = $answer->question->text ?? "Question {$answer->question_id}";
                $answerText = is_string($answer->answer) ? $answer->answer : json_encode($answer->answer);
                $noteContent .= "\n{$questionText}\n{$answerText}\n";
            }
            
            $noteContent .= "\n--- Next Steps ---\n";
            if ($inquiry->status === 'green') {
                $noteContent .= "STATUS: Auto-approved - Applicant has been provided with signup link.\n";
                $noteContent .= "ACTION: Monitor for signup completion and payment.\n";
            } elseif ($inquiry->status === 'yellow') {
                $noteContent .= "ACTION REQUIRED: Call applicant to discuss application and answer questions.\n";
            } elseif ($inquiry->status === 'red') {
                $noteContent .= "ACTION REQUIRED: Follow up with applicant about alternative opportunities.\n";
            }
            
            // Add note to contact
            $response = Http::withHeaders(['Api-Token' => $apiKey])
                ->post($baseUrl . '/api/3/notes', [
                    'note' => [
                        'contact' => $contactId,
                        'note' => $noteContent,
                    ],
                ]);
            
            if (!$response->successful()) {
                Log::warning('ActiveCampaign note creation failed', [
                    'inquiry_id' => $inquiry->id,
                    'status' => $response->status(),
                ]);
            }
        } catch (Throwable $e) {
            // Don't fail the whole job if custom fields fail
            Log::warning('ActiveCampaign custom fields exception', [
                'inquiry_id' => $inquiry->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    private function assignTag(string $baseUrl, string $apiKey, int $contactId, Inquiry $inquiry): void
    {
        $status = $inquiry->status;
        $tagMap = config('services.activecampaign.tags', []);
        $tagId = $tagMap[$status] ?? $tagMap['default'] ?? null;

        if (!$tagId) {
            Log::info('No tag configured for this status', ['inquiry_id' => $inquiry->id, 'status' => $status]);
            return;
        }

        try {
            $response = Http::withHeaders(['Api-Token' => $apiKey])
                ->post($baseUrl . '/api/3/contactTags', [
                    'contactTag' => ['contact' => $contactId, 'tag' => $tagId],
                ]);

            if (!$response->successful()) {
                Log::error('ActiveCampaign add tag failed', [
                    'inquiry_id' => $inquiry->id,
                    'contact_id' => $contactId,
                    'tag_id' => $tagId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (Throwable $e) {
            Log::error('ActiveCampaign tag add exception', [
                'inquiry_id' => $inquiry->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    private function assignAutomation(string $baseUrl, string $apiKey, int $contactId, Inquiry $inquiry): void
    {
        $status = $inquiry->status;
        $automationId = config("services.activecampaign.automations.{$status}");

        // Automations are optional - only trigger if configured
        if (empty($automationId)) {
            Log::info('No automation configured for this status', [
                'inquiry_id' => $inquiry->id,
                'status' => $status,
            ]);
            return;
        }

        try {
            $response = Http::withHeaders(['Api-Token' => $apiKey])
                ->post($baseUrl . '/api/3/contactAutomations', [
                    'contactAutomation' => ['contact' => $contactId, 'automation' => $automationId],
                ]);

            if (!$response->successful()) {
                Log::error('ActiveCampaign add to automation failed', [
                    'inquiry_id' => $inquiry->id,
                    'automation_id' => $automationId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (Throwable $e) {
            Log::error('ActiveCampaign automation exception', [
                'inquiry_id' => $inquiry->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('PushToActiveCampaignJob failed permanently', [
            'inquiry_id' => $this->inquiryId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
