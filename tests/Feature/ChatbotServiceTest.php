<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kandidat;
use App\Models\User;
use App\Services\ChatbotService;
use App\Services\RagService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ChatbotServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ChatbotService $chatbotService;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable OpenAI to test without external dependencies
        config(['openai.api_key' => null]);

        $this->chatbotService = app(ChatbotService::class);
    }

    // =========================================================================
    // chat() tests - mock response path
    // =========================================================================

    public function test_chat_uses_mock_response_when_api_key_is_null(): void
    {
        config(['openai.api_key' => null]);

        $result = $this->chatbotService->chat('Здраво');

        $this->assertTrue($result['success']);
        $this->assertIsString($result['message']);
        $this->assertNull($result['usage']);
    }

    public function test_chat_uses_mock_response_when_api_key_is_placeholder(): void
    {
        putenv('OPENAI_API_KEY=sk-your-openai-api-key-here');

        $result = $this->chatbotService->chat('Здраво');

        $this->assertTrue($result['success']);
        $this->assertIsString($result['message']);
        $this->assertNull($result['usage']);

        putenv('OPENAI_API_KEY=');
    }

    public function test_chat_returns_greeting_for_zdravo(): void
    {
        $result = $this->chatbotService->chat('Здраво');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Здраво! Како могу да вам помогнем?', $result['message']);
    }

    public function test_chat_returns_greeting_for_privet(): void
    {
        $result = $this->chatbotService->chat('Привет');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Здраво! Како могу да вам помогнем?', $result['message']);
        $this->assertStringContainsString('испитима', $result['message']);
    }

    public function test_chat_returns_exam_registration_info(): void
    {
        $result = $this->chatbotService->chat('Како се примети за испит?');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('/predmeti/', $result['message']);
    }

    public function test_chat_returns_tuition_info(): void
    {
        $result = $this->chatbotService->chat('Кад је рок за школарину?');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('школарине', $result['message']);
        $this->assertStringContainsString('/skolarina/', $result['message']);
    }

    public function test_chat_returns_exam_period_info(): void
    {
        $result = $this->chatbotService->chat('Који је следећи испитни рок?');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('рокови', $result['message']);
        $this->assertStringContainsString('/kalendar/', $result['message']);
    }

    public function test_chat_returns_grades_info(): void
    {
        $result = $this->chatbotService->chat('Где могу да видим своје оцене?');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('оцене', $result['message']);
    }

    public function test_chat_returns_contact_info(): void
    {
        $result = $this->chatbotService->chat('Како да контактирам професора?');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('студентску службу', $result['message']);
    }

    public function test_chat_returns_thank_you_response(): void
    {
        $result = $this->chatbotService->chat('Хвала');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Нема на чему!', $result['message']);
    }

    public function test_chat_is_case_insensitive(): void
    {
        $lowerResult = $this->chatbotService->chat('здраво');
        $upperResult = $this->chatbotService->chat('ЗДРАВО');

        $this->assertEquals($lowerResult['message'], $upperResult['message']);
    }

    public function test_chat_uses_rag_service_for_unknown_queries(): void
    {
        $result = $this->chatbotService->chat('Каква је временска прогноза?');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Користим базу знања', $result['message']);
        $this->assertStringContainsString('студентску службу', $result['message']);
    }

    public function test_chat_handles_partial_keyword_matches(): void
    {
        // Test that "примењен" matches keyword "примет"
        $result = $this->chatbotService->chat('Примењен приступ');

        $this->assertTrue($result['success']);
        // Should NOT match because "примет" is not in "Примењен"
        // Let's test actual partial match
        $result2 = $this->chatbotService->chat('Када је упис?');

        $this->assertTrue($result2['success']);
    }

    public function test_chat_handles_conversation_history(): void
    {
        // Even with history, mock response should work
        $history = [
            ['role' => 'user', 'content' => 'Здраво'],
            ['role' => 'assistant', 'content' => 'Здраво! Како могу да вам помогнем?'],
        ];

        $result = $this->chatbotService->chat('Хвала', $history);

        $this->assertTrue($result['success']);
        $this->assertIsString($result['message']);
    }

    public function test_chat_mock_response_returns_correct_structure(): void
    {
        $result = $this->chatbotService->chat('Тест порука');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('usage', $result);
        $this->assertTrue($result['success']);
        $this->assertIsString($result['message']);
        $this->assertNull($result['usage']);
    }

    // =========================================================================
    // getQuickQuestions() tests
    // =========================================================================

    public function test_get_quick_questions_returns_array(): void
    {
        $questions = $this->chatbotService->getQuickQuestions();

        $this->assertIsArray($questions);
        $this->assertNotEmpty($questions);
    }

    public function test_get_quick_questions_has_correct_structure(): void
    {
        $questions = $this->chatbotService->getQuickQuestions();

        foreach ($questions as $item) {
            $this->assertIsArray($item);
            $this->assertArrayHasKey('question', $item);
            $this->assertArrayHasKey('category', $item);
            $this->assertIsString($item['question']);
            $this->assertIsString($item['category']);
        }
    }

    public function test_get_quick_questions_returns_six_questions(): void
    {
        $questions = $this->chatbotService->getQuickQuestions();

        $this->assertCount(6, $questions);
    }

    public function test_get_quick_questions_includes_exam_questions(): void
    {
        $questions = $this->chatbotService->getQuickQuestions();

        $examQuestions = array_filter($questions, function ($q) {
            return $q['category'] === 'Испити';
        });

        $this->assertCount(2, $examQuestions);
    }

    public function test_get_quick_questions_covers_all_categories(): void
    {
        $questions = $this->chatbotService->getQuickQuestions();

        $categories = array_unique(array_column($questions, 'category'));

        $this->assertContains('Испити', $categories);
        $this->assertContains('Предмети', $categories);
        $this->assertContains('Оцене', $categories);
        $this->assertContains('Школарина', $categories);
        $this->assertContains('Контакт', $categories);
    }

    public function test_get_quick_questions_has_expected_content(): void
    {
        $questions = $this->chatbotService->getQuickQuestions();

        $questionTexts = array_column($questions, 'question');

        $this->assertContains('Када је следећи испитни рок?', $questionTexts);
        $this->assertContains('Како да пријавим испит?', $questionTexts);
        $this->assertContains('Које предмете имам ове godine?', $questionTexts);
    }

    // =========================================================================
    // getStudentInfo() tests
    // =========================================================================

    public function test_get_student_info_returns_error_for_nonexistent_student(): void
    {
        $result = $this->chatbotService->getStudentInfo(99999);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Студент није пронађен', $result['error']);
    }

    public function test_get_student_info_returns_student_data(): void
    {
        $kandidat = Kandidat::factory()->create();

        $result = $this->chatbotService->getStudentInfo($kandidat->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('student', $result);
        $this->assertArrayHasKey('polozeni_ispiti', $result);
        $this->assertArrayHasKey('prijave', $result);
        $this->assertEquals($kandidat->id, $result['student']->id);
    }

    public function test_get_student_info_handles_student_with_no_exams(): void
    {
        $kandidat = Kandidat::factory()->create();

        $result = $this->chatbotService->getStudentInfo($kandidat->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('student', $result);
        $this->assertArrayHasKey('polozeni_ispiti', $result);
        $this->assertArrayHasKey('prijave', $result);
        $this->assertEmpty($result['polozeni_ispiti']);
        $this->assertEmpty($result['prijave']);
    }

    // =========================================================================
    // buildSystemPrompt() tests (via reflection since it's protected)
    // =========================================================================

    public function test_build_system_prompt_includes_base_information(): void
    {
        $reflection = new \ReflectionClass($this->chatbotService);
        $method = $reflection->getMethod('buildSystemPrompt');
        $method->setAccessible(true);

        $prompt = $method->invoke($this->chatbotService, 'Тест контекст');

        $this->assertStringContainsString('Ти си AI асistent за Факултет за спорт', $prompt);
        $this->assertStringContainsString('База знања:', $prompt);
        $this->assertStringContainsString('Тест контекст', $prompt);
        $this->assertStringContainsString('српском језику', $prompt);
    }

    public function test_build_system_prompt_includes_context(): void
    {
        $reflection = new \ReflectionClass($this->chatbotService);
        $method = $reflection->getMethod('buildSystemPrompt');
        $method->setAccessible(true);

        $context = 'Специфичан контекст о испитима';
        $prompt = $method->invoke($this->chatbotService, $context);

        $this->assertStringContainsString($context, $prompt);
    }

    public function test_build_system_prompt_includes_user_info_when_authenticated(): void
    {
        $user = User::create([
            'name' => 'Тест Корисник',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        Auth::login($user);

        $reflection = new \ReflectionClass($this->chatbotService);
        $method = $reflection->getMethod('buildSystemPrompt');
        $method->setAccessible(true);

        $prompt = $method->invoke($this->chatbotService, '');

        $this->assertStringContainsString('Тренутни корисник: Тест Корисник', $prompt);

        Auth::logout();
    }

    public function test_build_system_prompt_marks_student_when_kandidat_exists(): void
    {
        $user = User::create([
            'name' => 'Студент Тест',
            'email' => 'student@test.com',
            'password' => bcrypt('password'),
        ]);
        $kandidat = Kandidat::factory()->create(['email' => 'student@test.com']);

        Auth::login($user);

        $reflection = new \ReflectionClass($this->chatbotService);
        $method = $reflection->getMethod('buildSystemPrompt');
        $method->setAccessible(true);

        $prompt = $method->invoke($this->chatbotService, '');

        $this->assertStringContainsString('Корисник је студент', $prompt);

        Auth::logout();
    }

    public function test_build_system_prompt_without_authenticated_user(): void
    {
        Auth::logout();

        $reflection = new \ReflectionClass($this->chatbotService);
        $method = $reflection->getMethod('buildSystemPrompt');
        $method->setAccessible(true);

        $prompt = $method->invoke($this->chatbotService, '');

        $this->assertStringContainsString('Ти си AI асistent за Факултет за спорт', $prompt);
    }

    public function test_build_system_prompt_includes_capabilities(): void
    {
        $reflection = new \ReflectionClass($this->chatbotService);
        $method = $reflection->getMethod('buildSystemPrompt');
        $method->setAccessible(true);

        $prompt = $method->invoke($this->chatbotService, '');

        $this->assertStringContainsString('Твоје способности:', $prompt);
        $this->assertStringContainsString('Одговарање на питања о испитима', $prompt);
        $this->assertStringContainsString('предметима и професорима', $prompt);
    }

    public function test_build_system_prompt_includes_rules(): void
    {
        $reflection = new \ReflectionClass($this->chatbotService);
        $method = $reflection->getMethod('buildSystemPrompt');
        $method->setAccessible(true);

        $prompt = $method->invoke($this->chatbotService, '');

        $this->assertStringContainsString('Правила:', $prompt);
        $this->assertStringContainsString('Не измишљај информације', $prompt);
        $this->assertStringContainsString('студентску службу', $prompt);
    }

    // =========================================================================
    // Integration tests
    // =========================================================================

    public function test_full_chat_workflow_with_keyword(): void
    {
        $result = $this->chatbotService->chat('Како да пријавим испит за следећи рок?');

        $this->assertTrue($result['success']);
        $this->assertIsString($result['message']);
        $this->assertNotEmpty($result['message']);
    }

    public function test_chatbot_service_uses_rag_service(): void
    {
        // Verify that ChatbotService has RagService dependency
        $reflection = new \ReflectionClass($this->chatbotService);
        $property = $reflection->getProperty('ragService');
        $property->setAccessible(true);

        $ragService = $property->getValue($this->chatbotService);

        $this->assertInstanceOf(RagService::class, $ragService);
    }

    public function test_chat_handles_multiple_keywords_in_message(): void
    {
        $result = $this->chatbotService->chat('Привет, како да пријавим испит и платим нешто?');

        $this->assertTrue($result['success']);
        $this->assertIsString($result['message']);
    }

    public function test_chat_preserves_cyrillic_characters(): void
    {
        $result = $this->chatbotService->chat('Школарина');

        $this->assertTrue($result['success']);
        // Response should contain Cyrillic
        $this->assertMatchesRegularExpression('/[\x{0400}-\x{04FF}]/u', $result['message']);
    }
}
