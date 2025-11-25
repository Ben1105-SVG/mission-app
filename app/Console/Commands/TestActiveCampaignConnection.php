<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TestActiveCampaignConnection extends Command
{
    protected $signature = 'activecampaign:test';
    protected $description = 'Test ActiveCampaign API connection and configuration';

    public function handle(): int
    {
        $this->info('🔍 Testing ActiveCampaign Integration...');
        $this->newLine();

        // Read from config (which reads from .env)
        $baseUrl = rtrim(config('services.activecampaign.url', ''), '/');
        $apiKey = config('services.activecampaign.key', '');

        // Configuration checks
        $this->info('📋 Configuration Check:');
        if (empty($baseUrl)) {
            $this->error('❌ ACTIVE_CAMPAIGN_URL is not set in .env or config/services.php');
            return Command::FAILURE;
        }
        $this->info("   ✓ API URL: {$baseUrl}");

        if (empty($apiKey)) {
            $this->error('❌ ACTIVE_CAMPAIGN_KEY is not set in .env or config/services.php');
            return Command::FAILURE;
        }
        // mask the key when logging
        $masked = Str::mask($apiKey, '*', 6, max(0, strlen($apiKey) - 10));
        $this->info('   ✓ API Key: ' . $masked);
        $this->newLine();

        $this->info('🔌 Testing API Connection:');
        try {
            $response = Http::withHeaders(['Api-Token' => $apiKey])
                ->timeout(10)
                ->get($baseUrl . '/api/3/accounts');

            if ($response->successful()) {
                $this->info('   ✓ API connection successful');

                // safely read account name if present
                $account = $response->json('account') ?? $response->json('accounts.0');
                if ($account && is_array($account)) {
                    $name = $account['name'] ?? $account['account_name'] ?? 'unknown';
                    $this->info("   ✓ Account: {$name}");
                }
            } else {
                $this->error('   ❌ API connection failed');
                $this->error("   Status: {$response->status()}");
                $this->error('   Response (truncated): ' . Str::limit($response->body(), 1000));
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('   ❌ API connection error: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->newLine();

        // Check tags (config/services.php -> activecampaign.tags)
        $this->info('🏷️  Checking Tags:');
        $tags = config('services.activecampaign.tags', []);
        $tagStatuses = ['green', 'yellow', 'red', 'default'];

        foreach ($tagStatuses as $status) {
            $tagId = $tags[$status] ?? null;
            if ($tagId) {
                try {
                    $resp = Http::withHeaders(['Api-Token' => $apiKey])
                        ->timeout(10)
                        ->get($baseUrl . "/api/3/tags/{$tagId}");

                    if ($resp->successful()) {
                        $tag = $resp->json('tag');
                        $tagName = $tag['name'] ?? 'Unnamed';
                        $statusIcon = ($tagName === 'Unnamed' || empty(trim($tagName))) ? '⚠' : '✓';
                        $this->info("   {$statusIcon} {$status} tag (ID: {$tagId}): {$tagName}");
                        if ($tagName === 'Unnamed' || empty(trim($tagName))) {
                            $this->line("      💡 Consider renaming this tag to '{$status}' in ActiveCampaign for clarity");
                        }
                    } else {
                        $this->warn("   ⚠ {$status} tag (ID: {$tagId}): Not found (HTTP {$resp->status()})");
                    }
                } catch (\Exception $e) {
                    $this->warn("   ⚠ {$status} tag (ID: {$tagId}): Error - {$e->getMessage()}");
                }
            } else {
                if ($status !== 'green') { // Green tag is optional
                    $this->warn("   ⚠ {$status} tag: Not configured");
                }
            }
        }

        $this->newLine();

        // Check automations
        $this->info('🤖 Checking Automations:');
        $automations = config('services.activecampaign.automations', []);
        $automationStatuses = ['yellow', 'red'];

        foreach ($automationStatuses as $status) {
            $automationId = $automations[$status] ?? null;
            if ($automationId) {
                try {
                    $resp = Http::withHeaders(['Api-Token' => $apiKey])
                        ->timeout(10)
                        ->get($baseUrl . "/api/3/automations/{$automationId}");

                    if ($resp->successful()) {
                        $automation = $resp->json('automation');
                        $this->info("   ✓ {$status} automation (ID: {$automationId}): " . ($automation['name'] ?? 'unnamed'));
                    } else {
                        $this->warn("   ⚠ {$status} automation (ID: {$automationId}): Not found (HTTP {$resp->status()})");
                    }
                } catch (\Exception $e) {
                    $this->warn("   ⚠ {$status} automation (ID: {$automationId}): Error - {$e->getMessage()}");
                }
            } else {
                $this->info("   ℹ {$status} automation: Not configured (optional)");
            }
        }

        $this->newLine();

        // Check remote MCP URL (optional)
        $this->info('🔗 Checking Remote MCP URL (optional):');
        $mcpUrl = config('services.activecampaign.mcp_url', '');
        if ($mcpUrl) {
            $this->info("   ✓ MCP URL: {$mcpUrl}");
            // try HEAD request (non-intrusive)
            try {
                $head = Http::timeout(6)->head($mcpUrl);
                $this->info("   ✓ MCP endpoint reachable (HTTP {$head->status()})");
            } catch (\Exception $e) {
                $this->warn("   ⚠ MCP endpoint check failed: {$e->getMessage()}");
            }
        } else {
            $this->warn('   ⚠ ACTIVE_CAMPAIGN_MCP_URL not configured (optional)');
        }

        $this->newLine();

        // Check signup URL
        $this->info('🔗 Checking Signup URL:');
        $signupUrl = config('missions.signup_url', '');
        if ($signupUrl && $signupUrl !== 'https://trip-signup-link.com') {
            $this->info("   ✓ Signup URL: {$signupUrl}");
        } else {
            $this->warn('   ⚠ MISSIONS_SIGNUP_URL not set (using default)');
        }

        $this->newLine();
        $this->info('✅ Integration test complete!');
        $this->newLine();
        $this->info('📝 Next Steps:');
        $this->line('   1. Make sure tags & automations exist in ActiveCampaign');
        $this->line('   2. Configure ACTIVE_CAMPAIGN_MCP_URL if you use remote MCP');
        $this->line('   3. Set MISSIONS_SIGNUP_URL for green applicants');
        $this->line('   4. Test with a real application submission');
        $this->newLine();

        return Command::SUCCESS;
    }
}
