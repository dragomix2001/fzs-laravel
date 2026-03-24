<?php

namespace Tests\Feature;

use App\Services\ChatbotService;
use App\Services\RagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_returns_mock_response_when_no_openai_key(): void
    {
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

    public function test_chatbot_handles_openai_exceptions_gracefully(): void
    {
        $this->markTestSkipped('Mocking issue with openai facade');
    }

    public function test_chatbot_returns_quick_questions(): void
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

    public function test_chatbot_session_handling_works(): void
    {
        $this->markTestSkipped('Mocking issue with openai facade');
    }
}
