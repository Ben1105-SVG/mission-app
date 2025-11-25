<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestApplicationSubmission extends Command
{
    protected $signature = 'test:application 
                            {--status= : Force a specific status (green/yellow/red) for testing}
                            {--email= : Use a specific email address}';

    protected $description = 'Test application submission with sample data';

    public function handle(): int
    {
        $this->info('🧪 Testing Application Submission...');
        $this->newLine();

        // Get questions to build realistic answers
        $questions = Question::orderBy('group')->orderBy('id')->get();
        
        if ($questions->isEmpty()) {
            $this->error('❌ No questions found in database. Please add questions first.');
            return Command::FAILURE;
        }

        $this->info("Found {$questions->count()} question(s)");
        $this->newLine();

        // Build sample answers based on desired status
        $desiredStatus = $this->option('status');
        $answers = $this->buildSampleAnswers($questions, $desiredStatus);

        // Build payload
        $email = $this->option('email') ?? 'test-' . time() . '@example.com';
        $payload = [
            'name' => 'Test Applicant',
            'email' => $email,
            'phone' => '+1234567890',
            'group_leader_role' => 'Youth Pastor',
            'role_duration' => 2.5,
            'answers' => $answers,
        ];

        $this->info('📤 Submitting application...');
        $this->line('   Name: ' . $payload['name']);
        $this->line('   Email: ' . $payload['email']);
        $this->line('   Expected Status: ' . ($desiredStatus ?? 'Auto-determined by AI'));
        $this->newLine();

        try {
            // Use the application's actual URL or construct from request
            $baseUrl = config('app.url');
            if (empty($baseUrl) || $baseUrl === 'http://localhost') {
                // Try to detect from environment
                $baseUrl = env('APP_URL', 'http://localhost:8000');
            }
            $baseUrl = rtrim($baseUrl, '/');
            
            $this->line('   Base URL: ' . $baseUrl);
            
            $response = Http::timeout(30)
                ->post($baseUrl . '/api/inquiries', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                $this->info('✅ Application submitted successfully!');
                $this->newLine();
                
                $this->info('📋 Response:');
                $this->line('   Status: ' . strtoupper($data['status'] ?? 'unknown'));
                $this->line('   Message: ' . ($data['message'] ?? 'No message'));
                
                if (isset($data['signup_link'])) {
                    $this->info('   Signup Link: ' . $data['signup_link']);
                } else {
                    $this->line('   Signup Link: None (not Green status)');
                }
                
                $this->newLine();
                
                // Check what should happen
                $status = $data['status'] ?? 'unknown';
                $this->info('🔍 What happens next:');
                
                if ($status === 'green') {
                    $this->line('   ✓ User gets signup link');
                    $this->line('   ✓ NOT sent to ActiveCampaign');
                    $this->line('   ✓ Can proceed to payment');
                } elseif (in_array($status, ['yellow', 'red'])) {
                    $this->line('   ✓ Sent to ActiveCampaign (queued)');
                    $this->line('   ✓ Tagged with "' . ucfirst($status) . '" tag');
                    $this->line('   ✓ Complete form details in notes');
                    $this->line('   ⏳ Check queue: php artisan queue:work');
                    $this->line('   ⏳ Check ActiveCampaign for contact');
                }
                
                $this->newLine();
                $this->info('💡 To check if job was queued:');
                $this->line('   php artisan queue:work --once');
                
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
            } else {
                // Yellow answers: mixed responses
                $answer = $this->getYellowAnswer($key, $question);
            }

            $answers[$question->id] = $answer;
            if ($key) {
                $answers[$key] = $answer;
            }
        }

        return $answers;
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

