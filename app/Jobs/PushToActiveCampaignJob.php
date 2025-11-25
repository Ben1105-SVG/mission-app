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

    public Inquiry $inquiry;

    public $tries = 3;

    public $backoff = [30, 120, 300];

    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry->fresh(); // ensure latest model data
    }

    public function handle(): void
    {
        $baseUrl = rtrim(config('services.activecampaign.url', ''), '/');
        $apiKey  = config('services.activecampaign.key');

        if (empty($baseUrl) || empty($apiKey)) {
            Log::warning('ActiveCampaign config missing; skipping push', ['inquiry_id' => $this->inquiry->id]);
            return;
        }

        $contactId = $this->syncContact($baseUrl, $apiKey);

        if ($contactId) {
            $this->assignTag($baseUrl, $apiKey, $contactId);
            $this->assignAutomation($baseUrl, $apiKey, $contactId);
        } else {
            Log::warning('Contact ID missing, skipping tag & automation', ['inquiry_id' => $this->inquiry->id]);
        }
    }

    private function syncContact(string $baseUrl, string $apiKey): ?int
    {
        $payload = [
            'contact' => [
                'email' => $this->inquiry->email,
                'firstName' => $this->inquiry->name,
                'phone' => $this->inquiry->phone,
            ],
        ];

        try {
            $response = Http::withHeaders(['Api-Token' => $apiKey])
                ->post($baseUrl . '/api/3/contact/sync', $payload);

            if (!$response->successful()) {
                Log::error('ActiveCampaign contact sync failed', [
                    'inquiry_id' => $this->inquiry->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException('Contact sync failed: ' . $response->status());
            }

            $contactId = data_get($response->json(), 'contact.id');

            if ($contactId) {
                $this->inquiry->forceFill(['ac_contact_id' => $contactId])->save();
                
                // Add all form details as custom field values
                $this->addCustomFields($baseUrl, $apiKey, $contactId);
            } else {
                Log::warning('ActiveCampaign sync returned no contact id', [
                    'inquiry_id' => $this->inquiry->id,
                    'response' => $response->json(),
                ]);
            }

            return $contactId;
        } catch (Throwable $e) {
            Log::error('ActiveCampaign sync exception', [
                'inquiry_id' => $this->inquiry->id,
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function addCustomFields(string $baseUrl, string $apiKey, int $contactId): void
    {
        try {
            // Get all answers for this inquiry
            $answers = $this->inquiry->answers()->with('question')->get();
            
            // Build a comprehensive note with all form details for follow-up
            $statusLabel = strtoupper($this->inquiry->status ?? 'UNKNOWN');
            $noteContent = "=== MISSION TRIP APPLICATION ===\n";
            $noteContent .= "Status: {$statusLabel}\n";
            $noteContent .= "Submitted: " . $this->inquiry->created_at->format('F j, Y \a\t g:i A') . "\n\n";
            
            $noteContent .= "--- Contact Information ---\n";
            $noteContent .= "Name: {$this->inquiry->name}\n";
            $noteContent .= "Email: {$this->inquiry->email}\n";
            if ($this->inquiry->phone) {
                $noteContent .= "Phone: {$this->inquiry->phone}\n";
            }
            
            if ($this->inquiry->group_leader_role) {
                $noteContent .= "\n--- Leadership Information ---\n";
                $noteContent .= "Group Leader Role: {$this->inquiry->group_leader_role}\n";
                if ($this->inquiry->role_duration) {
                    $noteContent .= "Role Duration: {$this->inquiry->role_duration} years\n";
                }
            }
            
            // Add AI evaluation summary if available
            $flags = $this->inquiry->flags ?? [];
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
            if ($this->inquiry->status === 'yellow') {
                $noteContent .= "ACTION REQUIRED: Call applicant to discuss application and answer questions.\n";
            } elseif ($this->inquiry->status === 'red') {
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
                    'inquiry_id' => $this->inquiry->id,
                    'status' => $response->status(),
                ]);
            }
        } catch (Throwable $e) {
            // Don't fail the whole job if custom fields fail
            Log::warning('ActiveCampaign custom fields exception', [
                'inquiry_id' => $this->inquiry->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    private function assignTag(string $baseUrl, string $apiKey, int $contactId): void
    {
        $status = $this->inquiry->status;
        $tagMap = config('services.activecampaign.tags', []);
        $tagId = $tagMap[$status] ?? $tagMap['default'] ?? null;

        if (!$tagId) {
            Log::info('No tag configured for this status', ['inquiry_id' => $this->inquiry->id, 'status' => $status]);
            return;
        }

        try {
            $response = Http::withHeaders(['Api-Token' => $apiKey])
                ->post($baseUrl . '/api/3/contactTags', [
                    'contactTag' => ['contact' => $contactId, 'tag' => $tagId],
                ]);

            if (!$response->successful()) {
                Log::error('ActiveCampaign add tag failed', [
                    'inquiry_id' => $this->inquiry->id,
                    'contact_id' => $contactId,
                    'tag_id' => $tagId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (Throwable $e) {
            Log::error('ActiveCampaign tag add exception', [
                'inquiry_id' => $this->inquiry->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    private function assignAutomation(string $baseUrl, string $apiKey, int $contactId): void
    {
        $status = $this->inquiry->status;
        $automationId = config("services.activecampaign.automations.{$status}");

        if (empty($automationId)) {
            return;
        }

        try {
            $response = Http::withHeaders(['Api-Token' => $apiKey])
                ->post($baseUrl . '/api/3/contactAutomations', [
                    'contactAutomation' => ['contact' => $contactId, 'automation' => $automationId],
                ]);

            if (!$response->successful()) {
                Log::error('ActiveCampaign add to automation failed', [
                    'inquiry_id' => $this->inquiry->id,
                    'automation_id' => $automationId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (Throwable $e) {
            Log::error('ActiveCampaign automation exception', [
                'inquiry_id' => $this->inquiry->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('PushToActiveCampaignJob failed permanently', [
            'inquiry_id' => $this->inquiry->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
