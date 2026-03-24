<?php

namespace App\Services;

use App\Models\KnowledgeBase;

class RagService
{
    protected array $faqData = [];

    protected bool $openAiConfigured = false;

    public function __construct()
    {
        $this->faqData = $this->loadFaqData();
        $this->openAiConfigured = ! empty(env('OPENAI_API_KEY')) && env('OPENAI_API_KEY') !== 'sk-your-openai-api-key-here';
    }

    protected function loadFaqData(): array
    {
        return [
            [
                'question' => 'Како да се пријавим за испит?',
                'answer' => 'За пријаву испита идите на /predmeti/ или /prijava/student/{id}. Изаберите предмет и испитни рок, па попуните пријаву.',
                'category' => 'испити',
            ],
            [
                'question' => 'Когда су рокови за плаћање школарине?',
                'answer' => 'Рокови за уплату школарине су обично на почетку сваког семестра. Погледајте детаље на /skolarina/{id}.',
                'category' => 'школарина',
            ],
            [
                'question' => 'Како да видим своје резултате испита?',
                'answer' => 'Резултате испита можете видети на страници запосlenог студента или преко /prijava/zaStudenta/{id}.',
                'category' => 'испити',
            ],
            [
                'question' => 'Шта ако сам заборавио лозинку?',
                'answer' => 'Контактирајте администратора система или студентску службу за ресетовање лозинке.',
                'category' => 'налог',
            ],
            [
                'question' => 'Како да контактирам професора?',
                'answer' => 'Користите страницу /profesor за преглед свих професора или питајте преко обавештења.',
                'category' => 'контакт',
            ],
            [
                'question' => 'Када су испитни рокови?',
                'answer' => 'Испитни рокови су објављени на /kalendar/. Ту можете видети све термине.',
                'category' => 'испити',
            ],
            [
                'question' => 'Како да обновим годину студија?',
                'answer' => 'Идите на /student/{id}/obnova где можете обновити годину студија.',
                'category' => 'статус',
            ],
            [
                'question' => 'Шта је потребно за упис на факултет?',
                'answer' => 'За упис потребна је диплома средње школе, JMBG, и остала документа. Контактирајте студентску службу за детаље.',
                'category' => 'упис',
            ],
        ];
    }

    public function findRelevantContext(string $query, int $maxResults = 3): string
    {
        $queryLower = mb_strtolower($query);
        $queryWords = preg_split('/\s+/', $queryLower);

        $scoredFaqs = [];

        foreach ($this->faqData as $faq) {
            $score = 0;
            $questionLower = mb_strtolower($faq['question']);
            $answerLower = mb_strtolower($faq['answer']);
            $categoryLower = mb_strtolower($faq['category']);

            foreach ($queryWords as $word) {
                if (mb_strlen($word) > 2) {
                    if (mb_strpos($questionLower, $word) !== false) {
                        $score += 3;
                    }
                    if (mb_strpos($answerLower, $word) !== false) {
                        $score += 1;
                    }
                    if (mb_strpos($categoryLower, $word) !== false) {
                        $score += 2;
                    }
                }
            }

            if ($score > 0) {
                $scoredFaqs[] = [
                    'faq' => $faq,
                    'score' => $score,
                ];
            }
        }

        usort($scoredFaqs, fn ($a, $b) => $b['score'] <=> $a['score']);
        $topResults = array_slice($scoredFaqs, 0, $maxResults);

        $context = "Релевантне информације из базе знања:\n\n";

        foreach ($topResults as $result) {
            $context .= "📌 Питање: {$result['faq']['question']}\n";
            $context .= "Одговор: {$result['faq']['answer']}\n\n";
        }

        return $context;
    }

    public function generateEnhancedPrompt(string $userMessage, string $context): string
    {
        return "Ти си AI асистент за Факултет за спорт. Користи само информације из датог контекста или своје опште знање о академском образовању.\n\n".
               "Ако не знаш одговор, реци кориснику да контактира студентску службу.\n\n".
               "{$context}\n\n".
               "Корисник пита: {$userMessage}";
    }

    public function findInKnowledgeBase(string $query): ?array
    {
        $results = KnowledgeBase::where('title', 'LIKE', "%{$query}%")
            ->orWhere('content', 'LIKE', "%{$query}%")
            ->orWhere('category', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get();

        if ($results->isEmpty()) {
            return null;
        }

        return $results->toArray();
    }

    public function addToKnowledgeBase(string $title, string $content, string $category = 'general', array $metadata = []): KnowledgeBase
    {
        return KnowledgeBase::create([
            'title' => $title,
            'content' => $content,
            'category' => $category,
            'metadata' => $metadata,
        ]);
    }
}
