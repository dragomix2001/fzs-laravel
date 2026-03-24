<?php

namespace Tests\Feature;

use App\Services\ChatbotService;
use App\Services\RagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function chatbot_returns_mock_response_when_no_openai_key()
    {
        // Ensure no valid OpenAI key
        $_ENV['OPENAI_API_KEY'] = 'sk-your-openai-api-key-here';
        putenv('OPENAI_API_KEY=sk-your-openai-api-key-here');

        $chatbot = new ChatbotService(
            $this->createMock(RagService::class)
        );

        $response = $chatbot->chat('hello');

        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('message', $response);
        $this->assertNotEmpty($response['message']);
    }

    /** @test */
    public function chatbot_handles_openai_exceptions_gracefully()
    {
        $_ENV['OPENAI_API_KEY'] = 'sk-test-key-for-testing';
        putenv('OPENAI_API_KEY=sk-test-key-for-testing');

        $ragMock = $this->createMock(RagService::class);
        $ragMock->method('findRelevantContext')
            ->willReturn('');

        $chatbot = new ChatbotService($ragMock);

        // Mock OpenAI facade to throw an exception
        $this->mock(Facade::getFacadeApplication()->make('openai'), function ($mock) {
            $mock->shouldReceive('chat()->create')
                ->andThrow(new \Exception('API Error'));
        });

        $response = $chatbot->chat('test message');

        $this->assertArrayHasKey('success', $response);
        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('message', $response);
        $this->assertStringContainsString('грешке', $response['message']);
    }

    /** @test */
    public function chatbot_returns_quick_questions()
    {
        $ragMock = $this->createMock(RagService::class);
        $chatbot = new ChatbotService($ragMock);

        $questions = $chatbot->getQuickQuestions();

        $this->assertIsArray($questions);
        $this->assertNotEmpty($questions);
        $this->assertArrayHasKey(0, $questions);
        $this->assertArrayHasKey('question', $questions[0]);
        $this->assertArrayHasKey('category', $questions[0]);
    }

    /** @test */
    public function chatbot_session_handling_works()
    {
        $this->withoutExceptionHandling();

        $_ENV['OPENAI_API_KEY'] = 'sk-your-openai-api-key-here';
        putenv('OPENAI_API_KEY=sk-your-openai-api-key-here');

        $ragMock = $this->createMock(RagService::class);
        $ragMock->method('findRelevantContext')
            ->willReturn('Test context');

        $chatbot = new ChatbotService($ragMock);

        // Mock OpenAI response
        $this->mock(Facade::getFacadeApplication()->make('openai'), function ($mock) {
            $mock->shouldReceive('chat()->create')
                ->andReturn((object) [
                    'choices' => [[
                        'message' => (object) [
                            'content' => 'Test response',
                        ],
                    ]],
                    'usage' => (object) [
                        'prompt_tokens' => 10,
                        'completion_tokens' => 5,
                        'total_tokens' => 15,
                    ],
                ]);
        });

        $response = $this->post('/chatbot/chat', [
            'message' => 'Test message',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Check that session has chat history
        $this->assertNotNull(Session::get('chatbot_history'));
    }
}
