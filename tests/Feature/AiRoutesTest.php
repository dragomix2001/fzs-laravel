<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AiRoutesTest extends TestCase
{
    public function test_chatbot_index_page_loads_for_authenticated_user(): void
    {
        $user = User::where('email', 'fzs@fzs.rs')->first();
        if (! $user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'fzs@fzs.rs',
                'password' => Hash::make('fzs123'),
            ]);
        }

        $response = $this->actingAs($user)->get('/chatbot');

        $response->assertStatus(200);
        $response->assertViewIs('chatbot.index');
    }

    public function test_prediction_index_page_loads_for_authenticated_user(): void
    {
        $user = User::where('email', 'fzs@fzs.rs')->first();
        if (! $user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'fzs@fzs.rs',
                'password' => Hash::make('fzs123'),
            ]);
        }

        $response = $this->actingAs($user)->get('/prediction');

        $response->assertStatus(200);
        $response->assertViewIs('prediction.index');
    }

    public function test_prediction_student_page_loads(): void
    {
        $user = User::where('email', 'fzs@fzs.rs')->first();
        if (! $user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'fzs@fzs.rs',
                'password' => Hash::make('fzs123'),
            ]);
        }

        $studentId = \App\Models\Kandidat::value('id');
        if (! $studentId) {
            $this->markTestSkipped('No students in database');
        }

        $response = $this->actingAs($user)->get("/prediction/student/{$studentId}");

        $response->assertStatus(200);
        $response->assertViewIs('prediction.student');
    }
}
