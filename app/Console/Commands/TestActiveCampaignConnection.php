<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestActiveCampaignConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activecampaign:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test ActiveCampaign API connection and configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Testing ActiveCampaign Integration...');
        $this->newLine();

        $baseUrl = rtrim(env('ACTIVE_CAMPAIGN_URL'), '/');
        $apiKey = env('ACTIVE_CAMPAIGN_KEY');

        // Check configuration
        $this->info('📋 Configuration Check:');
        if (empty($baseUrl)) {
            $this->error('❌ ACTIVE_CAMPAIGN_URL is not set in .env');
            return Command::FAILURE;
        }
        $this->info("   ✓ API URL: {$baseUrl}");

        if (empty($apiKey)) {
            $this->error('❌ ACTIVE_CAMPAIGN_KEY is not set in .env');
            return Command::FAILURE;
        }
        $this->info('   ✓ API Key: ' . substr($apiKey, 0, 10) . '...');
        $this->newLine();

        // Test API connection
        $this->info('🔌 Testing API Connection:');
        try {
            $response = Http::withHeaders(['Api-Token' => $apiKey])
                ->timeout(10)
                ->get($baseUrl . '/api/3/accounts');

            if ($response->successful()) {
                $this->info('   ✓ API connection successful');
                $account = $response->json('account');
                if ($account) {
                    $this->info("   ✓ Account: {$account['name']}");
                }
            } else {
                $this->error('   ❌ API connection failed');
                $this->error("   Status: {$response->status()}");
                $this->error("   Response: {$response->body()}");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('   ❌ API connection error: ' . $e->getMessage());
            return Command::FAILURE;
        }
        $this->newLine();

        // Fetch all tags once
        $this->info('🏷️ Fetching All Tags:');
        $allTags = collect();
        try {
            $allTagsResponse = Http::withHeaders(['Api-Token' => $apiKey])
                ->timeout(10)
                ->get($baseUrl . '/api/3/tags');

            if ($allTagsResponse->successful()) {
                $allTags = collect($allTagsResponse->json('tags') ?? []);
                if ($allTags->isEmpty()) {
                    $this->warn('   ⚠ No tags found');
                } else {
                    foreach ($allTags as $tag) {
                        $desc = $tag['description'] ?: 'No description';
                        $this->line("   • ID: {$tag['id']} | Name: {$tag['tag']} | Description: {$desc}");
                    }
                }
            } else {
                $this->warn("   ⚠ Failed to fetch tags, status: {$allTagsResponse->status()}");
            }
        } catch (\Exception $e) {
            $this->warn("   ⚠ Error fetching tags: {$e->getMessage()}");
        }
        $this->newLine();

        // Prepare tags from .env
        $tags = [
            'green'   => env('AC_TAG_GREEN'),
            'yellow'  => env('AC_TAG_YELLOW'),
            'red'     => env('AC_TAG_RED'),
            'default' => env('AC_TAG_DEFAULT'),
        ];

        // Check each tag by ID
        $this->info('🏷️  Checking Tags by ID:');
        foreach ($tags as $status => $tagId) {
            if ($tagId) {
                $tag = $allTags->firstWhere('id', $tagId);
                if ($tag) {
                    $this->info("   ✓ {$status} tag (ID: {$tagId}): {$tag['tag']}");
                } else {
                    $this->warn("   ⚠ {$status} tag (ID: {$tagId}): Not found");
                }
            } else {
                $this->warn("   ⚠ {$status} tag: Not configured");
            }
        }
        $this->newLine();

        // Prepare automations
        $automations = [
            'yellow' => env('AC_AUTOMATION_YELLOW'),
            'red'    => env('AC_AUTOMATION_RED'),
        ];

        // Check automations
        $this->info('🤖 Checking Automations:');
        foreach ($automations as $status => $automationId) {
            if ($automationId) {
                try {
                    $response = Http::withHeaders(['Api-Token' => $apiKey])
                        ->timeout(10)
                        ->get($baseUrl . "/api/3/automations/{$automationId}");

                    if ($response->successful() && isset($response->json()['automation']['name'])) {
                        $automation = $response->json('automation');
                        $this->info("   ✓ {$status} automation (ID: {$automationId}): {$automation['name']}");
                    } else {
                        $this->warn("   ⚠ {$status} automation (ID: {$automationId}): Not found");
                    }
                } catch (\Exception $e) {
                    $this->warn("   ⚠ {$status} automation (ID: {$automationId}): Error checking - {$e->getMessage()}");
                }
            } else {
                $this->info("   ℹ {$status} automation: Not configured (optional)");
            }
        }
        $this->newLine();

        // Check signup URL
        $this->info('🔗 Checking Signup URL:');
        $signupUrl = env('MISSIONS_SIGNUP_URL');
        if ($signupUrl && $signupUrl !== 'https://trip-signup-link.com') {
            $this->info("   ✓ Signup URL: {$signupUrl}");
        } else {
            $this->warn('   ⚠ MISSIONS_SIGNUP_URL not set (using default)');
        }
        $this->newLine();

        // Summary
        $this->info('✅ Integration test complete!');
        $this->newLine();
        $this->info('📝 Next Steps:');
        $this->line('   1. Make sure all tags are created in ActiveCampaign');
        $this->line('   2. Configure automations (optional but recommended)');
        $this->line('   3. Set MISSIONS_SIGNUP_URL for green status applicants');
        $this->line('   4. Test with a real application submission');
        $this->newLine();

        return Command::SUCCESS;
    }
}
