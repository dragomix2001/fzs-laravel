<?php

namespace Tests\Feature;

use App\Models\Kandidat;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AiRoutesTest extends TestCase
{
    protected ?User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::where('email', 'fzs@fzs.rs')->first();
        if (! $this->user) {
            $this->user = User::create([
                'name' => 'Test User',
                'email' => 'fzs@fzs.rs',
                'password' => Hash::make('fzs123'),
            ]);
        }
    }

    public function test_chatbot_index_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/chatbot');

        $response->assertStatus(200);
        $response->assertViewIs('chatbot.index');
    }

    public function test_prediction_index_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/prediction');

        $response->assertStatus(200);
        $response->assertViewIs('prediction.index');
    }

    public function test_prediction_student_page_loads(): void
    {
        $studentId = Kandidat::value('id');
        if (! $studentId) {
            $this->markTestSkipped('No students in database');
        }

        $response = $this->actingAs($this->user)->get("/prediction/student/{$studentId}");

        $response->assertStatus(200);
        $response->assertViewIs('prediction.student');
    }

    public function test_prediction_statistics_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/prediction/statistics');

        $response->assertStatus(200);
        $response->assertViewIs('prediction.statistics');
    }

    public function test_chatbot_requires_authentication(): void
    {
        $response = $this->get('/chatbot');

        $response->assertRedirect('/login');
    }

    public function test_prediction_requires_authentication(): void
    {
        $response = $this->get('/prediction');

        $response->assertRedirect('/login');
    }

    public function test_chatbot_chat_api_returns_json(): void
    {
        $response = $this->actingAs($this->user)->postJson('/chatbot/chat', [
            'message' => 'Тест порука',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
    }

    public function test_chatbot_clear_history_api(): void
    {
        $response = $this->actingAs($this->user)->postJson('/chatbot/clear');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_prediction_api_statistics(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/prediction/statistics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_students',
            'overall_pass_rate',
            'exam_statistics',
            'risk_distribution',
        ]);
    }
}
