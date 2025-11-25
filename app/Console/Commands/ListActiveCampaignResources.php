<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ListActiveCampaignResources extends Command
{
    protected $signature = 'activecampaign:list {--tags : List all tags} {--automations : List all automations}';

    protected $description = 'List available tags and automations in ActiveCampaign';

    public function handle(): int
    {
        $baseUrl = rtrim(config('services.activecampaign.url', env('ACTIVE_CAMPAIGN_URL', '')), '/');
        $apiKey = config('services.activecampaign.key', env('ACTIVE_CAMPAIGN_KEY', ''));

        if (empty($baseUrl) || empty($apiKey)) {
            $this->error('❌ ActiveCampaign credentials not configured. Please set ACTIVE_CAMPAIGN_URL and ACTIVE_CAMPAIGN_KEY in .env');
            return Command::FAILURE;
        }

        $listTags = $this->option('tags') || !$this->option('automations');
        $listAutomations = $this->option('automations') || !$this->option('tags');

        if ($listTags) {
            $this->listTags($baseUrl, $apiKey);
        }

        if ($listAutomations) {
            $this->listAutomations($baseUrl, $apiKey);
        }

        return Command::SUCCESS;
    }

    private function listTags(string $baseUrl, string $apiKey): void
    {
        $this->info('🏷️  Fetching Tags from ActiveCampaign...');
        $this->newLine();

        try {
            $response = Http::withHeaders(['Api-Token' => $apiKey])
                ->timeout(10)
                ->get($baseUrl . '/api/3/tags', [
                    'limit' => 100,
                ]);

            if (!$response->successful()) {
                $this->error('❌ Failed to fetch tags');
                $this->error("Status: {$response->status()}");
                $this->error('Response: ' . Str::limit($response->body(), 200));
                return;
            }

            $tags = $response->json('tags', []);
            $meta = $response->json('meta', []);

            if (empty($tags)) {
                $this->warn('⚠ No tags found in ActiveCampaign');
                $this->info('💡 Create tags at: https://bsaqeyan89737.activehosted.com/app/contacts/tags');
                return;
            }

            $total = $meta['total'] ?? count($tags);
            $this->info("Found {$total} tag(s):");
            $this->newLine();

            $tableData = [];
            foreach ($tags as $tag) {
                $tableData[] = [
                    'ID' => $tag['id'] ?? 'N/A',
                    'Name' => $tag['name'] ?? 'Unnamed',
                    'Type' => $tag['tagType'] ?? 'contact',
                    'Created' => isset($tag['cdate']) ? date('Y-m-d', strtotime($tag['cdate'])) : 'N/A',
                ];
            }

            $this->table(['ID', 'Name', 'Type', 'Created'], $tableData);
            $this->newLine();

            // Show recommended tags
            $this->info('💡 Recommended tags for your .env file:');
            $yellowTag = collect($tags)->first(fn($t) => stripos($t['name'] ?? '', 'yellow') !== false);
            $redTag = collect($tags)->first(fn($t) => stripos($t['name'] ?? '', 'red') !== false);

            if ($yellowTag) {
                $this->line("   AC_TAG_YELLOW={$yellowTag['id']}  # {$yellowTag['name']}");
            } else {
                $this->warn('   ⚠ No tag found matching "yellow" - create one in ActiveCampaign');
            }

            if ($redTag) {
                $this->line("   AC_TAG_RED={$redTag['id']}  # {$redTag['name']}");
            } else {
                $this->warn('   ⚠ No tag found matching "red" - create one in ActiveCampaign');
            }

        } catch (\Exception $e) {
            $this->error('❌ Error fetching tags: ' . $e->getMessage());
        }

        $this->newLine();
    }

    private function listAutomations(string $baseUrl, string $apiKey): void
    {
        $this->info('🤖 Fetching Automations from ActiveCampaign...');
        $this->newLine();

        try {
            $response = Http::withHeaders(['Api-Token' => $apiKey])
                ->timeout(10)
                ->get($baseUrl . '/api/3/automations', [
                    'limit' => 100,
                ]);

            if (!$response->successful()) {
                $this->error('❌ Failed to fetch automations');
                $this->error("Status: {$response->status()}");
                $this->error('Response: ' . Str::limit($response->body(), 200));
                return;
            }

            $automations = $response->json('automations', []);
            $meta = $response->json('meta', []);

            if (empty($automations)) {
                $this->warn('⚠ No automations found in ActiveCampaign');
                $this->info('💡 Create automations at: https://bsaqeyan89737.activehosted.com/app/automations');
                return;
            }

            $total = $meta['total'] ?? count($automations);
            $this->info("Found {$total} automation(s):");
            $this->newLine();

            $tableData = [];
            foreach ($automations as $automation) {
                $status = $automation['status'] ?? 'unknown';
                $statusIcon = match($status) {
                    0 => '⏸️',
                    1 => '▶️',
                    default => '❓',
                };

                $tableData[] = [
                    'ID' => $automation['id'] ?? 'N/A',
                    'Name' => $automation['name'] ?? 'Unnamed',
                    'Status' => $statusIcon . ' ' . ($status == 1 ? 'Active' : 'Inactive'),
                ];
            }

            $this->table(['ID', 'Name', 'Status'], $tableData);
            $this->newLine();

            // Show recommended automations
            $this->info('💡 To use automations, add to your .env:');
            $yellowAuto = collect($automations)->first(fn($a) => stripos($a['name'] ?? '', 'yellow') !== false);
            $redAuto = collect($automations)->first(fn($a) => stripos($a['name'] ?? '', 'red') !== false);

            if ($yellowAuto) {
                $this->line("   AC_AUTOMATION_YELLOW={$yellowAuto['id']}  # {$yellowAuto['name']}");
            } else {
                $this->info('   ℹ No automation found matching "yellow" (optional)');
            }

            if ($redAuto) {
                $this->line("   AC_AUTOMATION_RED={$redAuto['id']}  # {$redAuto['name']}");
            } else {
                $this->info('   ℹ No automation found matching "red" (optional)');
            }

        } catch (\Exception $e) {
            $this->error('❌ Error fetching automations: ' . $e->getMessage());
        }

        $this->newLine();
    }
}
