<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;

class TestApplicationSubmission extends Command
{
    protected $signature = 'test:application 
                            {--status= : Force a specific status (green/yellow/red) for testing. If not provided, AI will determine the status}
                            {--name= : Applicant name (default: "Test Applicanttttttttttttttt")}
                            {--email= : Applicant email address (default: auto-generated)}
                            {--phone= : Applicant phone number (default: "+1234567890")}
                            {--group-leader-role= : Group leader role (default: "Youth Pastor")}
                            {--role-duration= : Role duration in years (default: 2.5)}
                            {--show-status-only : Only output the AI-determined status (useful for scripting)}
                            {--process-queue : Automatically process the queued ActiveCampaign job after submission}';

    protected $description = 'Test application submission with sample data. AI will determine status if --status is not provided. Use --process-queue to automatically send to ActiveCampaign.';

    public function handle(): int
    {

        $showStatusOnly = $this->option('show-status-only');
        
        if (!$showStatusOnly) {
            $this->info('🧪 Testing Application Submission...');
            $this->newLine();
        }

        // Get questions to build realistic answers
        $questions = Question::orderBy('group')->orderBy('id')->get();
        
        if ($questions->isEmpty()) {
            if (!$showStatusOnly) {
                $this->error('❌ No questions found in database. Please add questions first.');
            }
            return Command::FAILURE;
        }

        if (!$showStatusOnly) {
            $this->info("Found {$questions->count()} question(s)");
            $this->newLine();
        }

        // Build sample answers based on desired status
        // If status is not provided, use random/mixed answers to let AI determine
        $desiredStatus = $this->option('status');
        $letAiDetermine = empty($desiredStatus);
        
        if (!$showStatusOnly && $letAiDetermine) {
            $this->info('🤖 AI will determine the status based on answers...');
        }
        
        $answers = $this->buildSampleAnswers($questions, $desiredStatus);

        // Build payload with user-provided or default values
        $payload = [
            'name' => $this->option('name') ?? 'Test Applicantttttttttt',
            'email' => $this->option('email') ?? 'test-' . time() . '@example.com',
            'phone' => $this->option('phone') ?? '+1234567890',
            'group_leader_role' => $this->option('group-leader-role') ?? 'Youth Pastor',
            'role_duration' => $this->option('role-duration') ? (float) $this->option('role-duration') : 2.5,
            'answers' => $answers,
        ];
        if (!$showStatusOnly) {
            $this->info('📤 Submitting application...');
            $this->line('   Name: ' . $payload['name']);
            $this->line('   Email: ' . $payload['email']);
            if ($letAiDetermine) {
                $this->line('   Status: 🤖 Will be determined by AI');
            } else {
                $this->line('   Expected Status: ' . strtoupper($desiredStatus));
            }
            $this->newLine();
        }

        try {
            // Use the application's actual URL or construct from request
            $baseUrl = config('app.url');
            if (empty($baseUrl) || $baseUrl === 'http://localhost') {
                // Default to Laravel's default development server port
                $baseUrl = 'http://localhost:8000';
            }
            $baseUrl = rtrim($baseUrl, '/');
            
            if (!$showStatusOnly) {
                $this->line('   Base URL: ' . $baseUrl);
                $this->line('   Endpoint: ' . $baseUrl . '/api/inquiries');
                $this->newLine();
                
                // Check if server is running
                try {
                    $healthCheck = Http::timeout(2)->get($baseUrl . '/up');
                    if (!$healthCheck->successful()) {
                        $this->warn('⚠️  Server might not be running. Make sure to start it with:');
                        $this->line('   php artisan serve');
                        $this->newLine();
                    }
                } catch (\Exception $e) {
                    $this->warn('⚠️  Cannot reach server at ' . $baseUrl);
                    $this->warn('   Make sure Laravel is running: php artisan serve');
                    $this->newLine();
                }
            }
            
            $response = Http::timeout(30)
                ->post($baseUrl . '/api/inquiries', $payload);

            if ($response->successful()) {
                $data = $response->json();
                $aiDeterminedStatus = strtolower($data['status'] ?? 'unknown');
                
                // If --show-status-only flag, only output the status
                if ($this->option('show-status-only')) {
                    $this->line($aiDeterminedStatus);
                    return Command::SUCCESS;
                }
                
                $this->info('✅ Application submitted successfully!');
                $this->newLine();
                
                $this->info('📋 Response:');
                
                // Highlight AI-determined status if it wasn't forced
                if ($letAiDetermine) {
                    $this->info('   🤖 AI-Determined Status: ' . strtoupper($aiDeterminedStatus));
                } else {
                    $this->line('   Status: ' . strtoupper($aiDeterminedStatus));
                    if ($aiDeterminedStatus !== strtolower($desiredStatus)) {
                        $this->warn('   ⚠️  Note: AI returned "' . $aiDeterminedStatus . '" but you requested "' . $desiredStatus . '"');
                    }
                }
                
                $this->line('   Message: ' . ($data['message'] ?? 'No message'));
                
                if (isset($data['signup_link'])) {
                    $this->info('   Signup Link: ' . $data['signup_link']);
                } else {
                    $this->line('   Signup Link: None (not Green status)');
                }
                
                // Show AI insights if available
                if (isset($data['keyStrengths']) && !empty($data['keyStrengths'])) {
                    $this->newLine();
                    $this->info('💪 Key Strengths:');
                    foreach ($data['keyStrengths'] as $strength) {
                        $this->line('   • ' . $strength);
                    }
                }
                
                if (isset($data['keyConcerns']) && !empty($data['keyConcerns'])) {
                    $this->newLine();
                    $this->warn('⚠️  Key Concerns:');
                    foreach ($data['keyConcerns'] as $concern) {
                        $this->line('   • ' . $concern);
                    }
                }
                
                $this->newLine();
                
                // Check what should happen
                $status = $aiDeterminedStatus;
                $this->info('🔍 What happens next:');
                
                if ($status === 'green') {
                    $this->line('   ✓ User gets signup link');
                    $this->line('   ✓ Sent to ActiveCampaign (queued) - Tagged with "Green"');
                    $this->line('   ✓ Complete form details in notes');
                    $this->line('   ✓ Can proceed to payment');
                } elseif (in_array($status, ['yellow', 'red'])) {
                    $this->line('   ✓ Sent to ActiveCampaign (queued)');
                    $this->line('   ✓ Tagged with "' . ucfirst($status) . '" tag');
                    $this->line('   ✓ Complete form details in notes');
                }
                
                // All statuses are sent to ActiveCampaign
                if (in_array($status, ['green', 'yellow', 'red'])) {
                    $this->line('   ⏳ Check queue: php artisan queue:work');
                    $this->line('   ⏳ Check ActiveCampaign for contact');
                }
                
                $this->newLine();
                
                // Automatically process queue if requested
                if ($this->option('process-queue')) {
                    $this->info('🔄 Processing queued ActiveCampaign job...');
                    $this->newLine();
                    
                    // Wait a moment for the job to be queued
                    sleep(1);
                    
                    // Process the queue job
                    try {
                        // Use Artisan::call to process the queue
                        Artisan::call('queue:work', [
                            '--once' => true,
                            '--quiet' => true,
                        ]);
                        
                        $this->info('✅ ActiveCampaign job processed successfully!');
                        $this->line('   Contact should now be in ActiveCampaign');
                        $this->line('   Check ActiveCampaign to verify:');
                        $this->line('   - Contact synced');
                        $this->line('   - Tagged with "' . ucfirst($status) . '"');
                        if (in_array($status, ['green', 'yellow', 'red'])) {
                            $listMap = config('services.activecampaign.lists', []);
                            $listId = $listMap[$status] ?? null;
                            if ($listId) {
                                $this->line('   - Added to ' . ucfirst($status) . ' list');
                            } else {
                                $this->line('   - List assignment skipped (not configured)');
                            }
                        }
                        $this->line('   - Notes added with form details');
                    } catch (\Exception $e) {
                        $this->warn('⚠️  Could not process queue automatically: ' . $e->getMessage());
                        $this->line('   Process manually: php artisan queue:work --once');
                    }
                    
                    $this->newLine();
                } else {
                    // Show how to use the status
                    if ($letAiDetermine) {
                        $this->info('💡 To test with this specific status:');
                        $this->line('   php artisan test:application --status=' . $aiDeterminedStatus);
                    }
                    
                    $this->info('💡 To automatically process the queue job:');
                    $this->line('   php artisan test:application --process-queue');
                    $this->newLine();
                    $this->info('💡 Or process manually:');
                    $this->line('   php artisan queue:work --once');
                }
                
            } else {
                $this->error('❌ Application submission failed');
                $this->error('   Status: ' . $response->status());
                $this->error('   Response: ' . $response->body());
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function buildSampleAnswers($questions, ?string $desiredStatus): array
    {
        $answers = [];

        foreach ($questions as $question) {
            $key = $question->key ?? "question_{$question->id}";
            
            // Build answers based on desired status
            if ($desiredStatus === 'green') {
                // Green answers: all positive
                $answer = $this->getGreenAnswer($key, $question);
            } elseif ($desiredStatus === 'red') {
                // Red answers: some concerning responses
                $answer = $this->getRedAnswer($key, $question);
            } elseif ($desiredStatus === 'yellow') {
                // Yellow answers: mixed responses
                $answer = $this->getYellowAnswer($key, $question);
            } else {
                // No status specified - use realistic mixed answers to let AI determine
                // Mix of positive and neutral answers that could result in any status
                $answer = $this->getRealisticAnswer($key, $question);
            }

            $answers[$question->id] = $answer;
            if ($key) {
                $answers[$key] = $answer;
            }
        }

        return $answers;
    }

    private function getRealisticAnswer(string $key, $question): string
    {
        // Realistic answers that could result in any status depending on AI evaluation
        $realisticAnswers = [
            'previous_experience' => 'Yes, I have been on one mission trip before',
            'praying' => 'Comfortable',
            'faith_sharing' => 'I have shared my faith a few times',
            'view_of_marriage' => 'I agree with most of the statement',
            'prayer_group_comfort' => 'Comfortable',
            'team_readiness' => 'I feel mostly ready',
            'challenges' => 'I have a few questions',
            'statement_of_beliefs' => 'I agree',
        ];

        return $realisticAnswers[$key] ?? 'Yes';
    }

    private function getGreenAnswer(string $key, $question): string
    {
        // Return positive answers for green status
        $greenAnswers = [
            'previous_experience' => 'Yes, I have been on multiple mission trips',
            'praying' => 'Very comfortable',
            'faith_sharing' => 'Yes, I regularly share my faith and am comfortable doing so',
            'view_of_marriage' => 'I fully agree with the statement',
            'prayer_group_comfort' => 'Very comfortable',
            'team_readiness' => 'I feel ready and prepared',
            'challenges' => 'No concerns or hesitations',
            'statement_of_beliefs' => 'I agree with all statements',
        ];

        return $greenAnswers[$key] ?? 'Yes';
    }

    private function getYellowAnswer(string $key, $question): string
    {
        // Return mixed answers for yellow status
        $yellowAnswers = [
            'previous_experience' => 'No, this would be my first mission trip',
            'praying' => 'Somewhat comfortable',
            'faith_sharing' => 'I have shared before but feel a bit nervous',
            'view_of_marriage' => 'I mostly agree but have some questions',
            'prayer_group_comfort' => 'Somewhat comfortable',
            'team_readiness' => 'I think I\'m ready but want to learn more',
            'challenges' => 'I have a few questions about logistics',
            'statement_of_beliefs' => 'I agree with most statements',
        ];

        return $yellowAnswers[$key] ?? 'Maybe';
    }

    private function getRedAnswer(string $key, $question): string
    {
        // Return concerning answers for red status
        $redAnswers = [
            'previous_experience' => 'No experience',
            'praying' => 'Not comfortable',
            'faith_sharing' => 'I have not shared my faith before',
            'view_of_marriage' => 'I do not agree with the statement',
            'prayer_group_comfort' => 'Not comfortable',
            'team_readiness' => 'I\'m not sure if I\'m ready',
            'challenges' => 'I have significant concerns',
            'statement_of_beliefs' => 'I do not agree',
        ];

        return $redAnswers[$key] ?? 'No';
    }
}

