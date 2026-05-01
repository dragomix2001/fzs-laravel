<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\ChatbotService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class ChatbotControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
    }

    protected function tearDown(): void
    {
        Model::reguard();
        parent::tearDown();
    }

    private function authenticatedUser(): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => 'chatbot_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    public function test_index_returns_chatbot_view(): void
    {
        $this->mock(ChatbotService::class, function ($mock) {
            $mock->shouldReceive('getQuickQuestions')->once()->andReturn([
                ['question' => 'Koji su rokovi za prijavu?', 'category' => 'Upis'],
                ['question' => 'Koliko predmeta mogu da prijavim?', 'category' => 'Ispiti'],
            ]);
        });

        $user = $this->authenticatedUser();

        $response = $this->actingAs($user)->get(route('chatbot.index'));

        $response->assertStatus(200);
        $response->assertViewIs('chatbot.index');
        $response->assertViewHas('quickQuestions');
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('chatbot.index'));

        $response->assertRedirect('/login');
    }

    public function test_chat_returns_success_response(): void
    {
        $this->mock(ChatbotService::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'Ovo je одговор бота.',
                    'usage' => ['total_tokens' => 50],
                ]);
        });

        $user = $this->authenticatedUser();

        $response = $this->actingAs($user)->postJson(route('chatbot.chat'), [
            'message' => 'Који су рокови?',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Ovo je одговор бота.',
        ]);
    }

    public function test_chat_returns_null_usage_when_service_does_not_provide_it(): void
    {
        $this->mock(ChatbotService::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'Response without usage',
                ]);
        });

        $user = $this->authenticatedUser();

        $response = $this->actingAs($user)->postJson(route('chatbot.chat'), [
            'message' => 'Test',
        ]);

        $response->assertOk();
        $response->assertJsonPath('usage', null);
    }

    public function test_chat_stores_conversation_history_in_session(): void
    {
        $this->mock(ChatbotService::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'Response text',
                ]);
        });

        $user = $this->authenticatedUser();

        $this->actingAs($user)->postJson(route('chatbot.chat'), [
            'message' => 'Hello',
        ]);

        $history = Session::get('chatbot_history');
        $this->assertIsArray($history);
        $this->assertCount(2, $history);
        $this->assertEquals('user', $history[0]['role']);
        $this->assertEquals('Hello', $history[0]['content']);
        $this->assertEquals('assistant', $history[1]['role']);
    }

    public function test_chat_returns_error_on_failure(): void
    {
        $this->mock(ChatbotService::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn([
                    'success' => false,
                    'message' => 'API error occurred',
                ]);
        });

        $user = $this->authenticatedUser();

        $response = $this->actingAs($user)->postJson(route('chatbot.chat'), [
            'message' => 'Test message',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'API error occurred',
        ]);
    }

    public function test_chat_validates_message_is_required(): void
    {
        $user = $this->authenticatedUser();

        $response = $this->actingAs($user)->postJson(route('chatbot.chat'), []);

        $response->assertStatus(422);
        $response->assertJsonPath('error.validation.message.0', 'The message field is required.');
    }

    public function test_clear_history_removes_session_data(): void
    {
        $user = $this->authenticatedUser();

        Session::put('chatbot_history', [
            ['role' => 'user', 'content' => 'test'],
        ]);

        $response = $this->actingAs($user)->postJson(route('chatbot.clear'));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertNull(Session::get('chatbot_history'));
    }

    public function test_quick_question_returns_success_response(): void
    {
        $this->mock(ChatbotService::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'Quick answer here',
                ]);
        });

        $user = $this->authenticatedUser();

        $response = $this->actingAs($user)->postJson(route('chatbot.quick'), [
            'question' => 'Колико предмета могу да приjavим?',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Quick answer here',
        ]);
    }

    public function test_quick_question_stores_both_question_and_answer_in_history(): void
    {
        $this->mock(ChatbotService::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'The answer',
                ]);
        });

        $user = $this->authenticatedUser();

        $this->actingAs($user)->postJson(route('chatbot.quick'), [
            'question' => 'The question',
        ]);

        $history = Session::get('chatbot_history');
        $this->assertIsArray($history);
        $this->assertCount(2, $history);
        $this->assertEquals('user', $history[0]['role']);
        $this->assertEquals('The question', $history[0]['content']);
        $this->assertEquals('assistant', $history[1]['role']);
        $this->assertEquals('The answer', $history[1]['content']);
    }

    public function test_quick_question_returns_error_on_failure(): void
    {
        $this->mock(ChatbotService::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn([
                    'success' => false,
                    'message' => 'Service unavailable',
                ]);
        });

        $user = $this->authenticatedUser();

        $response = $this->actingAs($user)->postJson(route('chatbot.quick'), [
            'question' => 'Test question',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Service unavailable',
        ]);
    }

    public function test_quick_question_validates_question_is_required(): void
    {
        $user = $this->authenticatedUser();

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.quick'), []);

        $response->assertStatus(422);
        $response->assertJsonPath('error.validation.question.0', 'The question field is required.');
    }

    public function test_chat_trims_history_to_last_10_messages(): void
    {
        Session::put('chatbot_history', array_fill(0, 9, ['role' => 'user', 'content' => 'old']));

        $this->mock(ChatbotService::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'New response',
                ]);
        });

        $user = $this->authenticatedUser();

        $this->actingAs($user)->postJson(route('chatbot.chat'), [
            'message' => 'New message',
        ]);

        $history = Session::get('chatbot_history');
        $this->assertLessThanOrEqual(10, count($history));
    }

    public function test_quick_question_trims_history_to_last_10_messages(): void
    {
        Session::put('chatbot_history', array_fill(0, 10, ['role' => 'user', 'content' => 'old']));

        $this->mock(ChatbotService::class, function ($mock) {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'Trimmed response',
                ]);
        });

        $user = $this->authenticatedUser();

        $this->actingAs($user)->postJson(route('chatbot.quick'), [
            'question' => 'Question',
        ]);

        $history = Session::get('chatbot_history');
        $this->assertLessThanOrEqual(10, count($history));
    }
}
