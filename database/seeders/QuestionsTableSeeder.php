<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $questions = [
            // Group 1 (Q1-3)
            [
                'text' => 'Group or Individual: Are you inquiring on behalf of a youth group, family, or as an individual?',
                'group' => 1,
                'key' => 'group_or_individual',
                'type' => 'select',
                'options' => ['Youth group','Family','Individual'],
                'hint' => null,
            ],
            [
                'text' => 'Church Affiliation: What church or organization are you affiliated with?',
                'group' => 1,
                'key' => 'church_affiliation',
                'type' => 'text',
                'options' => null,
                'hint' => 'Name of church / organization (if any).',
            ],
            [
                'text' => 'Group Leader Role: What is your role or position within the church or group? How long have you served in this role?',
                'group' => 1,
                'key' => 'group_leader_role_and_duration',
                'type' => 'text',
                'options' => null,
                'hint' => 'Include title and years (e.g. Youth Pastor — 2 years).',
            ],

            // Group 2 (Q4-6)
            [
                'text' => 'Previous Experience: Have you or your group participated in a mission trip before?',
                'group' => 2,
                'key' => 'previous_experience',
                'type' => 'select',
                'options' => ['Yes','No'],
                'hint' => 'If yes, briefly list countries/years.',
            ],
            [
                'text' => 'Spiritual Leadership: How comfortable are you leading others in prayer and spiritual discussions during a trip?',
                'group' => 2,
                'key' => 'praying',
                'type' => 'comfort',
                'options' => ['Very comfortable','Comfortable','Somewhat uncomfortable','Uncomfortable'],
                'hint' => null,
            ],
            [
                'text' => 'Faith Sharing: Have you ever shared your faith or testimony with someone personally? How comfortable are you with sharing your faith with others?',
                'group' => 2,
                'key' => 'faith_sharing',
                'type' => 'comfort',
                'options' => ['Very comfortable','Comfortable','Somewhat uncomfortable','Uncomfortable','Never shared'],
                'hint' => null,
            ],

            // Group 3 (Q7-11)
            [
                'text' => 'Group Composition: How many people are in your group, and what is their age range?',
                'group' => 3,
                'key' => 'group_composition',
                'type' => 'text',
                'options' => null,
                'hint' => 'Example: 12 people — ages 16–25.',
            ],
            [
                'text' => 'Purpose of Trip: What are your primary goals or hopes for this mission experience?',
                'group' => 3,
                'key' => 'purpose_of_trip',
                'type' => 'text',
                'options' => null,
                'hint' => 'Examples: construction, evangelism, discipleship, medical.',
            ],
            [
                'text' => 'View of marriage and sexuality: How would you respond to this statement about our view of marriage and sexuality? Is there anything about it that concerns you?',
                'group' => 3,
                'key' => 'view_of_marriage',
                'type' => 'textarea',
                'options' => null,
                'hint' => 'Provide candid response — this helps us determine fit.',
            ],
            [
                'text' => 'Prayer: How comfortable are you praying in a group setting?',
                'group' => 3,
                'key' => 'prayer_group_comfort',
                'type' => 'comfort',
                'options' => ['Very comfortable','Comfortable','Somewhat uncomfortable','Uncomfortable'],
                'hint' => null,
            ],
            [
                'text' => 'Team Readiness: How would you describe your group’s spiritual maturity and readiness for ministry?',
                'group' => 3,
                'key' => 'team_readiness',
                'type' => 'text',
                'options' => null,
                'hint' => 'Briefly describe training, discipleship, or prior ministry experience.',
            ],

            // Group 4 (Q12-13)
            [
                'text' => 'Challenges or Concerns: Are there any challenges or hesitations you or your group might have about participating in a mission trip?',
                'group' => 4,
                'key' => 'challenges',
                'type' => 'textarea',
                'options' => null,
                'hint' => 'e.g., health concerns, mobility, language barriers.',
            ],
            [
                'text' => 'Statement of Beliefs: Have you reviewed and do you agree with Adventures in Missions’ statement of faith and code of conduct for mission teams?',
                'group' => 4,
                'key' => 'statement_of_beliefs',
                'type' => 'select',
                'options' => ['Yes, I agree','No — I have concerns'],
                'hint' => 'Please read the statement of faith and code of conduct before answering.',
            ],
        ];

        foreach ($questions as $q) {
            DB::table('questions')->insert([
                'text' => $q['text'],
                'group' => $q['group'],
                'key' => $q['key'],
                'type' => $q['type'],
                'options' => $q['options'] ? json_encode($q['options']) : null,
                'hint' => $q['hint'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
