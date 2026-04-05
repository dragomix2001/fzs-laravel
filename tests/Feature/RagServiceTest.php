<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\KnowledgeBase;
use App\Services\RagService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RagServiceTest extends TestCase
{
    use DatabaseTransactions;

    private RagService $ragService;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable OpenAI to test without external dependencies
        config(['openai.api_key' => null]);

        $this->ragService = app(RagService::class);
    }

    // =========================================================================
    // findRelevantContext() tests
    // =========================================================================

    public function test_find_relevant_context_returns_matching_faqs_by_question(): void
    {
        $context = $this->ragService->findRelevantContext('пријавим за испит', 3);

        $this->assertStringContainsString('Релевантне информације из базе знања:', $context);
        $this->assertStringContainsString('Како да се пријавим за испит?', $context);
        $this->assertStringContainsString('За пријаву испита идите на /predmeti/', $context);
    }

    public function test_find_relevant_context_returns_matching_faqs_by_category(): void
    {
        $context = $this->ragService->findRelevantContext('испити', 3);

        $this->assertStringContainsString('Релевантне информације из базе знања:', $context);
        // Should match multiple FAQs in "испити" category
        $this->assertStringContainsString('📌 Питање:', $context);
    }

    public function test_find_relevant_context_returns_matching_faqs_by_answer(): void
    {
        $context = $this->ragService->findRelevantContext('школарине', 3);

        $this->assertStringContainsString('Релевантне информације из базе знања:', $context);
        $this->assertStringContainsString('школарине', $context);
    }

    public function test_find_relevant_context_respects_max_results_limit(): void
    {
        $context = $this->ragService->findRelevantContext('испит', 1);

        $occurrences = substr_count($context, '📌 Питање:');
        $this->assertEquals(1, $occurrences);
    }

    public function test_find_relevant_context_respects_max_results_limit_with_three(): void
    {
        $context = $this->ragService->findRelevantContext('испит', 3);

        $occurrences = substr_count($context, '📌 Питање:');
        $this->assertLessThanOrEqual(3, $occurrences);
    }

    public function test_find_relevant_context_ignores_short_words(): void
    {
        // Words with 2 or fewer characters should be ignored
        $context = $this->ragService->findRelevantContext('да је на то', 3);

        // Should return empty context since all words are too short
        $this->assertEquals("Релевантне информације из базе знања:\n\n", $context);
    }

    public function test_find_relevant_context_is_case_insensitive(): void
    {
        $contextLower = $this->ragService->findRelevantContext('испит', 3);
        $contextUpper = $this->ragService->findRelevantContext('ИСПИТ', 3);

        $this->assertEquals($contextLower, $contextUpper);
    }

    public function test_find_relevant_context_scores_question_matches_higher(): void
    {
        // Query that appears in question should rank higher
        $context = $this->ragService->findRelevantContext('пријавим', 1);

        $this->assertStringContainsString('Како да се пријавим за испит?', $context);
    }

    public function test_find_relevant_context_handles_multiple_query_words(): void
    {
        $context = $this->ragService->findRelevantContext('контактирам професора', 3);

        $this->assertStringContainsString('Како да контактирам професора?', $context);
    }

    public function test_find_relevant_context_returns_empty_for_no_matches(): void
    {
        $context = $this->ragService->findRelevantContext('xyzabc123', 3);

        $this->assertEquals("Релевантне информације из базе знања:\n\n", $context);
    }

    public function test_find_relevant_context_handles_cyrillic_text(): void
    {
        $context = $this->ragService->findRelevantContext('школарина', 3);

        $this->assertStringContainsString('Релевантне информације из базе знања:', $context);
        $this->assertStringContainsString('школарин', $context);
    }

    // =========================================================================
    // generateEnhancedPrompt() tests
    // =========================================================================

    public function test_generate_enhanced_prompt_includes_user_message(): void
    {
        $userMessage = 'Како да се пријавим?';
        $context = 'Релевантне информације...';

        $prompt = $this->ragService->generateEnhancedPrompt($userMessage, $context);

        $this->assertStringContainsString($userMessage, $prompt);
        $this->assertStringContainsString('Корисник пита: '.$userMessage, $prompt);
    }

    public function test_generate_enhanced_prompt_includes_context(): void
    {
        $userMessage = 'Како да се пријавим?';
        $context = 'Релевантне информације из базе знања...';

        $prompt = $this->ragService->generateEnhancedPrompt($userMessage, $context);

        $this->assertStringContainsString($context, $prompt);
    }

    public function test_generate_enhanced_prompt_includes_system_instructions(): void
    {
        $userMessage = 'Како да се пријавим?';
        $context = 'Релевантне информације...';

        $prompt = $this->ragService->generateEnhancedPrompt($userMessage, $context);

        $this->assertStringContainsString('AI асистент за Факултет за спорт', $prompt);
        $this->assertStringContainsString('студентску службу', $prompt);
    }

    public function test_generate_enhanced_prompt_formats_correctly(): void
    {
        $userMessage = 'Питање';
        $context = 'Контекст';

        $prompt = $this->ragService->generateEnhancedPrompt($userMessage, $context);

        // Should have structure: system instructions + context + user question
        $this->assertStringStartsWith('Ти си AI асистент', $prompt);
        $this->assertStringContainsString('Контекст', $prompt);
        $this->assertStringEndsWith('Корисник пита: Питање', $prompt);
    }

    // =========================================================================
    // findInKnowledgeBase() tests
    // =========================================================================

    public function test_find_in_knowledge_base_returns_null_when_empty(): void
    {
        $result = $this->ragService->findInKnowledgeBase('неки текст');

        $this->assertNull($result);
    }

    public function test_find_in_knowledge_base_finds_by_title(): void
    {
        KnowledgeBase::create([
            'title' => 'Упутство за пријаву',
            'content' => 'Садржај упутства...',
            'category' => 'испити',
            'metadata' => [],
        ]);

        $result = $this->ragService->findInKnowledgeBase('пријаву');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Упутство за пријаву', $result[0]['title']);
    }

    public function test_find_in_knowledge_base_finds_by_content(): void
    {
        KnowledgeBase::create([
            'title' => 'Општи водич',
            'content' => 'Садржај о школарини и испитима',
            'category' => 'општа питања',
            'metadata' => [],
        ]);

        $result = $this->ragService->findInKnowledgeBase('школарини');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Општи водич', $result[0]['title']);
    }

    public function test_find_in_knowledge_base_finds_by_category(): void
    {
        KnowledgeBase::create([
            'title' => 'Неко упутство',
            'content' => 'Неки садржај',
            'category' => 'испити',
            'metadata' => [],
        ]);

        $result = $this->ragService->findInKnowledgeBase('испити');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('испити', $result[0]['category']);
    }

    public function test_find_in_knowledge_base_limits_to_five_results(): void
    {
        // Create 10 entries
        for ($i = 1; $i <= 10; $i++) {
            KnowledgeBase::create([
                'title' => "Упутство {$i}",
                'content' => 'Садржај са кључном речју испит',
                'category' => 'испити',
                'metadata' => [],
            ]);
        }

        $result = $this->ragService->findInKnowledgeBase('испит');

        $this->assertIsArray($result);
        $this->assertCount(5, $result);
    }

    public function test_find_in_knowledge_base_handles_partial_matches(): void
    {
        KnowledgeBase::create([
            'title' => 'Упутство за школарину',
            'content' => 'Садржај',
            'category' => 'финансије',
            'metadata' => [],
        ]);

        $result = $this->ragService->findInKnowledgeBase('школар');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    public function test_find_in_knowledge_base_returns_multiple_matches(): void
    {
        KnowledgeBase::create([
            'title' => 'Испитни рокови',
            'content' => 'О испитним роковима',
            'category' => 'испити',
            'metadata' => [],
        ]);

        KnowledgeBase::create([
            'title' => 'Пријава испита',
            'content' => 'Како се пријављују испити',
            'category' => 'испити',
            'metadata' => [],
        ]);

        $result = $this->ragService->findInKnowledgeBase('испит');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    // =========================================================================
    // addToKnowledgeBase() tests
    // =========================================================================

    public function test_add_to_knowledge_base_creates_entry(): void
    {
        $entry = $this->ragService->addToKnowledgeBase(
            'Упутство',
            'Садржај упутства',
            'категорија',
            ['tag' => 'value']
        );

        $this->assertInstanceOf(KnowledgeBase::class, $entry);
        $this->assertEquals('Упутство', $entry->title);
        $this->assertEquals('Садржај упутства', $entry->content);
        $this->assertEquals('категорија', $entry->category);
        $this->assertEquals(['tag' => 'value'], $entry->metadata);
    }

    public function test_add_to_knowledge_base_persists_to_database(): void
    {
        $this->ragService->addToKnowledgeBase(
            'Тест наслов',
            'Тест садржај',
            'тест',
            []
        );

        $this->assertDatabaseHas('knowledge_base', [
            'title' => 'Тест наслов',
            'content' => 'Тест садржај',
            'category' => 'тест',
        ]);
    }

    public function test_add_to_knowledge_base_uses_default_category(): void
    {
        $entry = $this->ragService->addToKnowledgeBase(
            'Наслов',
            'Садржај'
        );

        $this->assertEquals('general', $entry->category);
    }

    public function test_add_to_knowledge_base_uses_default_empty_metadata(): void
    {
        $entry = $this->ragService->addToKnowledgeBase(
            'Наслов',
            'Садржај',
            'категорија'
        );

        $this->assertEquals([], $entry->metadata);
    }

    public function test_add_to_knowledge_base_returns_created_model(): void
    {
        $entry = $this->ragService->addToKnowledgeBase(
            'Наслов',
            'Садржај'
        );

        $this->assertTrue($entry->exists);
        $this->assertNotNull($entry->id);
    }

    public function test_add_to_knowledge_base_handles_complex_metadata(): void
    {
        $metadata = [
            'tags' => ['испит', 'пријава'],
            'priority' => 'high',
            'author' => 'admin',
            'nested' => [
                'key' => 'value',
            ],
        ];

        $entry = $this->ragService->addToKnowledgeBase(
            'Наслов',
            'Садржај',
            'тест',
            $metadata
        );

        $this->assertEquals($metadata, $entry->metadata);
    }

    public function test_add_to_knowledge_base_multiple_entries(): void
    {
        $this->ragService->addToKnowledgeBase('Наслов 1', 'Садржај 1', 'кат1');
        $this->ragService->addToKnowledgeBase('Наслов 2', 'Садржај 2', 'кат2');
        $this->ragService->addToKnowledgeBase('Наслов 3', 'Садржај 3', 'кат3');

        $count = KnowledgeBase::count();
        $this->assertEquals(3, $count);
    }

    // =========================================================================
    // Integration tests
    // =========================================================================

    public function test_full_workflow_add_and_find(): void
    {
        // Add entry to knowledge base
        $this->ragService->addToKnowledgeBase(
            'Како платити школарину',
            'Детаљна упутства за плаћање школарине...',
            'школарина',
            ['важност' => 'висока']
        );

        // Find it using findInKnowledgeBase
        $result = $this->ragService->findInKnowledgeBase('школарину');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Како платити школарину', $result[0]['title']);
    }

    public function test_find_relevant_context_with_all_categories(): void
    {
        // Test that FAQ data contains entries from all expected categories
        $categoriesFound = [];

        // Test each category by searching for category-specific keywords
        $testCases = [
            'испити' => 'пријав',
            'школарина' => 'школарин',
            'налог' => 'лозинк',
            'контакт' => 'професор',
            'статус' => 'обнов',
            'упис' => 'факултет',
        ];

        foreach ($testCases as $category => $keyword) {
            $context = $this->ragService->findRelevantContext($keyword, 5);
            if (mb_strpos($context, '📌 Питање:') !== false) {
                $categoriesFound[$category] = true;
            }
        }

        // Should find at least 5 different categories
        $this->assertGreaterThanOrEqual(5, count($categoriesFound));
    }
}
