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
use Illuminate\Support\Facades\Mail;
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
            $this->sendEmail($baseUrl, $apiKey, $contactId, $inquiry);
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
            // Truncate note if too long (ActiveCampaign has limits)
            $maxNoteLength = 65000; // ActiveCampaign note limit
            if (strlen($noteContent) > $maxNoteLength) {
                $noteContent = substr($noteContent, 0, $maxNoteLength - 100) . "\n\n[Note truncated due to length...]";
            }
            
            // ActiveCampaign API requires relid and reltype for notes
            // For contact notes: reltype = "Subscriber", relid = contact_id
            $response = Http::withHeaders(['Api-Token' => $apiKey])
                ->timeout(10)
                ->post($baseUrl . '/api/3/notes', [
                    'note' => [
                        'relid' => $contactId,
                        'reltype' => 'Subscriber',
                        'note' => $noteContent,
                    ],
                ]);
            
            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $response->body();
                
                Log::warning('ActiveCampaign note creation failed', [
                    'inquiry_id' => $inquiry->id,
                    'contact_id' => $contactId,
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'note_length' => strlen($noteContent),
                ]);
            } else {
                Log::info('ActiveCampaign note created successfully', [
                    'inquiry_id' => $inquiry->id,
                    'contact_id' => $contactId,
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

    private function sendEmail(string $baseUrl, string $apiKey, int $contactId, Inquiry $inquiry): void
    {
        $status = $inquiry->status;
        $listId = config("services.activecampaign.campaigns.{$status}");

        // If list ID is configured, try adding contact to list first
        // Otherwise, send email directly
        if (!empty($listId)) {
            try {
                // Add contact to a list - this will trigger any campaigns/automations on that list
                // ActiveCampaign will automatically send emails if the list has a campaign or automation
                $response = Http::withHeaders(['Api-Token' => $apiKey])
                    ->post($baseUrl . '/api/3/contactLists', [
                        'contactList' => [
                            'list' => (int) $listId,
                            'contact' => $contactId,
                            'status' => 1, // 1 = Active/Subscribed
                        ],
                    ]);

                if ($response->successful()) {
                    Log::info('ActiveCampaign contact added to list - email will be sent', [
                        'inquiry_id' => $inquiry->id,
                        'contact_id' => $contactId,
                        'list_id' => $listId,
                        'status' => $status,
                    ]);
                    return; // Success - email will be sent via list campaign/automation
                } else {
                    Log::warning('Failed to add contact to list, trying direct email', [
                        'inquiry_id' => $inquiry->id,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    // Fall through to direct email sending
                }
            } catch (Throwable $e) {
                Log::warning('Exception adding contact to list, trying direct email', [
                    'inquiry_id' => $inquiry->id,
                    'exception' => $e->getMessage(),
                ]);
                // Fall through to direct email sending
            }
        }

        // Send email directly (either because no list configured or list method failed)
        Log::info('Sending email directly via ActiveCampaign API', [
            'inquiry_id' => $inquiry->id,
            'contact_id' => $contactId,
            'status' => $status,
        ]);
        $this->sendMessageDirectly($baseUrl, $apiKey, $contactId, $inquiry);
    }

    private function sendMessageDirectly(string $baseUrl, string $apiKey, int $contactId, Inquiry $inquiry): void
    {
        try {
            Log::info('Starting direct email send process', [
                'inquiry_id' => $inquiry->id,
                'contact_id' => $contactId,
            ]);
            
            // Use Laravel Mail to send email directly
            // This is the most reliable method and works with your existing mail configuration
            $email = $inquiry->email;
            $name = $inquiry->name;
            $htmlContent = $this->getEmailContent($inquiry, $name);
            $textContent = $this->getEmailTextContent($inquiry, $name);
            
            Mail::send([], [], function ($message) use ($email, $name, $inquiry, $htmlContent, $textContent) {
                $message->to($email, $name)
                    ->subject($this->getEmailSubject($inquiry->status))
                    ->html($htmlContent)
                    ->text($textContent);
            });

            Log::info('✅ Email sent successfully via Laravel Mail', [
                'inquiry_id' => $inquiry->id,
                'contact_id' => $contactId,
                'email' => $email,
                'status' => $inquiry->status,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to send email via Laravel Mail', [
                'inquiry_id' => $inquiry->id,
                'contact_id' => $contactId,
                'email' => $inquiry->email,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function getEmailSubject(string $status): string
    {
        return match($status) {
            'green' => 'Congratulations! Your Mission Trip Application Has Been Approved',
            'yellow' => 'Thank You for Your Mission Trip Application',
            'red' => 'Thank You for Your Interest in Our Mission Trip',
            default => 'Mission Trip Application Received',
        };
    }

    private function getEmailContent(Inquiry $inquiry, string $name): string
    {
        $status = $inquiry->status;
        
        $message = match($status) {
            'green' => 'Congratulations! Your application has been approved. You can now sign up for the trip and pay your deposit.',
            'yellow' => 'Thank you for your application! A member of our team will reach out to you soon to discuss next steps and answer any questions you may have.',
            'red' => 'Thank you for your interest in our mission trip. While we\'re not able to move forward with this particular opportunity at this time, we\'d love to stay connected and explore other ways you can be involved in our mission work. Our team will be in touch soon.',
            default => 'Thank you for your application. We will review it and get back to you soon.',
        };

        $signupLink = ($status === 'green' && config('missions.signup_url')) 
            ? '<p style="margin: 20px 0;"><a href="' . config('missions.signup_url') . '" style="background-color: #22c55e; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">Reserve Your Trip</a></p>'
            : '';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #22c55e;'>Hello {$name},</h2>
            <p>{$message}</p>
            {$signupLink}
            <p style='margin-top: 30px; font-size: 12px; color: #666;'>
                If you have any questions, please don't hesitate to contact us at stm@adventures.org
            </p>
        </body>
        </html>
        ";
    }

    private function getEmailTextContent(Inquiry $inquiry, string $name): string
    {
        $status = $inquiry->status;
        
        $message = match($status) {
            'green' => 'Congratulations! Your application has been approved. You can now sign up for the trip and pay your deposit.',
            'yellow' => 'Thank you for your application! A member of our team will reach out to you soon to discuss next steps and answer any questions you may have.',
            'red' => 'Thank you for your interest in our mission trip. While we\'re not able to move forward with this particular opportunity at this time, we\'d love to stay connected and explore other ways you can be involved in our mission work. Our team will be in touch soon.',
            default => 'Thank you for your application. We will review it and get back to you soon.',
        };

        $signupLink = ($status === 'green' && config('missions.signup_url')) 
            ? "\n\nReserve your trip: " . config('missions.signup_url')
            : '';

        return "Hello {$name},\n\n{$message}{$signupLink}\n\nIf you have any questions, please contact us at stm@adventures.org";
    }


    public function failed(Throwable $exception): void
    {
        Log::error('PushToActiveCampaignJob failed permanently', [
            'inquiry_id' => $this->inquiryId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
