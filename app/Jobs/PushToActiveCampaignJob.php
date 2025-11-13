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

    /**
     * Maximum number of attempts if job fails.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Backoff seconds for retries.
     *
     * @var array|int
     */
    public $backoff = [30, 120, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(Inquiry $inquiry)
    {
        // it's good to pass a fresh model to the job in case things change
        $this->inquiry = $inquiry;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $baseUrl = config('services.activecampaign.url');
        $apiKey  = config('services.activecampaign.key');

        if (empty($baseUrl) || empty($apiKey)) {
            Log::warning('ActiveCampaign config missing; skipping push', ['inquiry_id' => $this->inquiry->id]);
            return;
        }

        // Normalize URL (no trailing slash)
        $baseUrl = rtrim($baseUrl, '/');

        // 1) Sync contact
        $contactPayload = [
            'contact' => [
                'email' => $this->inquiry->email,
                'firstName' => $this->inquiry->name,
                // add custom fields if you have field ids; else use "phone" props
                'phone' => $this->inquiry->phone,
            ],
        ];

        // If you want to include additional data as custom fields, add them here.
        // Example: $contactPayload['contact']['fieldValues'] = [ ... ];

        try {
            $response = Http::withHeaders([
                'Api-Token' => $apiKey,
            ])->post($baseUrl . '/api/3/contact/sync', $contactPayload);

            if (!$response->successful()) {
                // Log and bail — will be retried according to $tries/backoff
                Log::error('ActiveCampaign contact sync failed', [
                    'inquiry_id' => $this->inquiry->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                // Throw so the job is retried
                throw new \RuntimeException('ActiveCampaign contact sync failed: ' . $response->status());
            }

            $body = $response->json();

            // ActiveCampaign v3 returns the contact object under 'contact'
            $contactId = data_get($body, 'contact.id') ?? data_get($body, 'contact.contact.id');

            // If contactId wasn't found try alternative path
            if (empty($contactId)) {
                // Log and continue — not fatal if tag assignment fails later
                Log::warning('ActiveCampaign sync returned no contact id', [
                    'inquiry_id' => $this->inquiry->id,
                    'response' => $body,
                ]);
            } else {
                // Save contact id to inquiry (optional)
                $this->inquiry->forceFill(['ac_contact_id' => $contactId])->save();
            }
        } catch (Throwable $e) {
            Log::error('ActiveCampaign contact sync exception', [
                'inquiry_id' => $this->inquiry->id,
                'exception' => $e->getMessage(),
            ]);
            // rethrow so it can be retried
            throw $e;
        }

        // 2) Add Tag based on status (you must set these tag IDs in config/services.php or .env)
        $status = $this->inquiry->status;
        $tagMap = config('services.activecampaign.tags', []);

        // pick tag id by status, fallback to generic tag
        $tagId = $tagMap[$status] ?? $tagMap['default'] ?? null;

        if ($tagId && !empty($contactId)) {
            try {
                // ActiveCampaign contactTag create endpoint
                // payload example: { "contactTag": { "contact": <contactId>, "tag": <tagId> } }
                $tagResponse = Http::withHeaders([
                    'Api-Token' => $apiKey,
                ])->post($baseUrl . '/api/3/contactTags', [
                    'contactTag' => [
                        'contact' => $contactId,
                        'tag' => $tagId,
                    ],
                ]);

                if (!$tagResponse->successful()) {
                    Log::error('ActiveCampaign add tag failed', [
                        'inquiry_id' => $this->inquiry->id,
                        'contact_id' => $contactId,
                        'tag_id' => $tagId,
                        'status' => $tagResponse->status(),
                        'body' => $tagResponse->body(),
                    ]);
                    // not throwing here because contact sync succeeded; but you may want to retry/tag differently
                }
            } catch (Throwable $e) {
                Log::error('ActiveCampaign tag add exception', [
                    'inquiry_id' => $this->inquiry->id,
                    'exception' => $e->getMessage(),
                ]);
            }
        } else {
            Log::info('No ActiveCampaign tag configured or contactId missing; skipping tag assign', [
                'inquiry_id' => $this->inquiry->id,
                'status' => $status,
                'configured_tag' => $tagId ?? null,
            ]);
        }

        // 3) Optionally: add to automation, add to list, or fire other endpoints using contactId
        // Example (if you use automations): POST to /api/3/contactAutomations with { "contactAutomation": {"contact": contactId, "automation": automationId } }
        $automationId = $tagMap['automations'][$status] ?? null;
        if (!empty($automationId) && !empty($contactId)) {
            try {
                $autoResp = Http::withHeaders([
                    'Api-Token' => $apiKey,
                ])->post($baseUrl . '/api/3/contactAutomations', [
                    'contactAutomation' => [
                        'contact' => $contactId,
                        'automation' => $automationId,
                    ],
                ]);

                if (!$autoResp->successful()) {
                    Log::error('ActiveCampaign add to automation failed', [
                        'inquiry_id' => $this->inquiry->id,
                        'automation_id' => $automationId,
                        'status' => $autoResp->status(),
                        'body' => $autoResp->body(),
                    ]);
                }
            } catch (Throwable $e) {
                Log::error('ActiveCampaign add to automation exception', [
                    'inquiry_id' => $this->inquiry->id,
                    'exception' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * If job fails permanently.
     */
    public function failed(Throwable $exception): void
    {
        // Notify, log, or take other remedial actions
        Log::error('PushToActiveCampaignJob failed permanently', [
            'inquiry_id' => $this->inquiry->id,
            'exception' => $exception->getMessage(),
        ]);

        // You could also notify an admin via email/Slack here
    }
}
