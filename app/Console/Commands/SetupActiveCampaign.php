<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SetupActiveCampaign extends Command
{
    protected $signature = 'activecampaign:setup 
                            {--create-tags : Attempt to create Yellow and Red tags automatically}
                            {--signup-url= : Set the signup URL for green status}';

    protected $description = 'Interactive setup wizard for ActiveCampaign integration';

    public function handle(): int
    {
        $this->info('🚀 ActiveCampaign Setup Wizard');
        $this->newLine();

        $baseUrl = rtrim(config('services.activecampaign.url', env('ACTIVE_CAMPAIGN_URL', '')), '/');
        $apiKey = config('services.activecampaign.key', env('ACTIVE_CAMPAIGN_KEY', ''));

        if (empty($baseUrl) || empty($apiKey)) {
            $this->error('❌ ActiveCampaign credentials not configured');
            $this->line('Please set ACTIVE_CAMPAIGN_URL and ACTIVE_CAMPAIGN_KEY in .env');
            return Command::FAILURE;
        }

        // Step 1: Check existing tags
        $this->info('📋 Step 1: Checking Tags...');
        $yellowTag = $this->findOrCreateTag($baseUrl, $apiKey, 'Yellow', 'yellow');
        $redTag = $this->findOrCreateTag($baseUrl, $apiKey, 'Red', 'red');

        // Step 2: Update .env with tag IDs
        if ($yellowTag && $redTag) {
            $this->updateEnvFile('AC_TAG_YELLOW', $yellowTag);
            $this->updateEnvFile('AC_TAG_RED', $redTag);
        }

        // Step 3: Handle signup URL
        $signupUrl = $this->option('signup-url');
        if ($signupUrl) {
            $this->updateEnvFile('MISSIONS_SIGNUP_URL', $signupUrl);
        } else {
            $current = env('MISSIONS_SIGNUP_URL', 'https://trip-signup-link.com');
            if ($current === 'https://trip-signup-link.com') {
                $this->warn('⚠️  Signup URL is still using default');
                $this->line('   Update MISSIONS_SIGNUP_URL in .env with your actual signup page');
            }
        }

        // Step 4: Final test
        $this->newLine();
        $this->info('✅ Setup complete! Running final test...');
        $this->newLine();
        
        $this->call('activecampaign:test');

        return Command::SUCCESS;
    }

    private function findOrCreateTag(string $baseUrl, string $apiKey, string $displayName, string $status): ?int
    {
        // First, try to find existing tag
        try {
            $response = Http::withHeaders(['Api-Token' => $apiKey])
                ->timeout(10)
                ->get($baseUrl . '/api/3/tags', ['limit' => 100]);

            if ($response->successful()) {
                $tags = $response->json('tags', []);
                
                // Look for tag by name (case insensitive)
                foreach ($tags as $tag) {
                    $tagName = strtolower($tag['name'] ?? '');
                    if ($tagName === strtolower($displayName) || 
                        $tagName === strtolower($status) ||
                        stripos($tagName, $status) !== false) {
                        $this->info("   ✓ Found existing tag: {$tag['name']} (ID: {$tag['id']})");
                        return (int) $tag['id'];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->warn("   ⚠ Error checking tags: {$e->getMessage()}");
        }

        // Try to create tag if option is set
        if ($this->option('create-tags')) {
            try {
                $response = Http::withHeaders(['Api-Token' => $apiKey])
                    ->timeout(10)
                    ->post($baseUrl . '/api/3/tags', [
                        'tag' => [
                            'tag' => $displayName,
                            'tagType' => 'contact',
                        ],
                    ]);

                if ($response->successful()) {
                    $tag = $response->json('tag');
                    $this->info("   ✓ Created tag: {$displayName} (ID: {$tag['id']})");
                    return (int) $tag['id'];
                } else {
                    $this->warn("   ⚠ Could not create tag: HTTP {$response->status()}");
                }
            } catch (\Exception $e) {
                $this->warn("   ⚠ Error creating tag: {$e->getMessage()}");
            }
        }

        // Manual action needed
        $this->warn("   ⚠ Tag '{$displayName}' not found");
        $this->line("   → Go to: https://bsaqeyan89737.activehosted.com/app/contacts/tags");
        $this->line("   → Create or rename a tag to '{$displayName}'");
        $this->line("   → Run: php artisan activecampaign:list --tags");
        $this->line("   → Update .env: AC_TAG_" . strtoupper($status) . "=<id>");

        return null;
    }

    private function updateEnvFile(string $key, $value): void
    {
        $envFile = base_path('.env');
        
        if (!file_exists($envFile)) {
            $this->warn("   ⚠ .env file not found");
            return;
        }

        $envContent = file_get_contents($envFile);
        $pattern = "/^{$key}=.*/m";
        
        if (preg_match($pattern, $envContent)) {
            // Update existing
            $newLine = "{$key}={$value}";
            $envContent = preg_replace($pattern, $newLine, $envContent);
            $this->info("   ✓ Updated {$key} in .env");
        } else {
            // Add new
            $envContent .= "\n{$key}={$value}\n";
            $this->info("   ✓ Added {$key} to .env");
        }

        file_put_contents($envFile, $envContent);
    }
}

