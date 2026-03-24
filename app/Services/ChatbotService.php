<?php

namespace App\Services;

use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\PrijavaIspita;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class ChatbotService
{
    protected $model = 'gpt-4';

    protected $maxTokens = 500;

    protected $temperature = 0.7;

    protected RagService $ragService;

    public function __construct(RagService $ragService)
    {
        $this->ragService = $ragService;
    }

    public function chat(string $message, array $conversationHistory = []): array
    {
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey) || $apiKey === 'sk-your-openai-api-key-here') {
            return $this->getMockResponse($message);
        }

        try {
            $context = $this->ragService->findRelevantContext($message);
            $systemPrompt = $this->buildSystemPrompt($context);

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
            ];

            foreach ($conversationHistory as $history) {
                $messages[] = $history;
            }

            $messages[] = ['role' => 'user', 'content' => $message];

            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ]);

            $assistantMessage = $response->choices[0]->message->content;

            return [
                'success' => true,
                'message' => $assistantMessage,
                'usage' => $response->usage,
            ];

        } catch (\Exception $e) {
            Log::error('Chatbot error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Извините, дошло је до грешке. Молимо покушајте поново.',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function getMockResponse(string $message): array
    {
        $lowerMessage = mb_strtolower($message);

        $keywordResponses = [
            'примет' => 'За пријаву испита идите на /predmeti/. Изаберите предмет и испитни рок.',
            'школарин' => 'Рокови за уплату школарине су на почетку сваког семестра. Погледајте /skolarina/{id}.',
            'рок' => 'Испитни рокови су објављени на /kalendar/.',
            'оцен' => 'Ваше оцене можете видети на страници запосленог студента.',
            'контакт' => 'Контактирајте студентску службу или користите страницу /profesor.',
            'привет' => 'Здраво! Како могу да вам помогнем? Могу одговорити на питања о испитима, предметима, школарини.',
            'здраво' => 'Здраво! Како могу да вам помогнем?',
            'хвала' => 'Нема на чему! Ако имате jos питања, слободно питајте.',
            'како' => 'За детаљна упутства, контактирајте студентску службу или погледајте /obavestenja.',
        ];

        foreach ($keywordResponses as $keyword => $response) {
            if (mb_strpos($lowerMessage, $keyword) !== false) {
                return [
                    'success' => true,
                    'message' => $response,
                    'usage' => null,
                ];
            }
        }

        $context = $this->ragService->findRelevantContext($message);

        return [
            'success' => true,
            'message' => "Користим базу знања да вам помогнем. {$context}За детаљне информације контактирајте студентску службу.",
            'usage' => null,
        ];
    }

    protected function buildSystemPrompt(string $context = ''): string
    {
        $user = Auth::user();
        $userName = $user->name ?? 'Корисник';

        $prompt = "Ти си AI асistent за Факултет за спорт. Твоја улога је да помогнеш студентима и администрацији.\n\n";
        $prompt .= "База знања:\n{$context}\n";
        $prompt .= "Опште информације:\n";
        $prompt .= "- Факултет за спорт\n";
        $prompt .= "- Систем за управљање студентима, испитима, предметима\n\n";

        $prompt .= "Твоје способности:\n";
        $prompt .= "- Одговарање на питања о испитима, пријавама, оценама\n";
        $prompt .= "- Пружање информација о предметима и професорима\n";
        $prompt .= "- Помоћ при навигацији у систему\n";
        $prompt .= "- Објашњавање процедура и правила\n\n";

        $prompt .= "Правила:\n";
        $prompt .= "- Увек одговарај на српском језику\n";
        $prompt .= "- Буди љубазан и користан\n";
        $prompt .= "- Ако не знаш одговор, реци да контактира студентску службу\n";
        $prompt .= "- Не измишљај информације\n";
        $prompt .= "- Користи информације из базе знања када су релевантне\n\n";

        if ($user) {
            $prompt .= "Тренутни корисник: {$userName}\n";

            $kandidat = Kandidat::where('email', $user->email)->first();
            if ($kandidat) {
                $prompt .= "Корисник је студент.\n";
            }
        }

        return $prompt;
    }

    public function getQuickQuestions(): array
    {
        return [
            ['question' => 'Када је следећи испитни рок?', 'category' => 'Испити'],
            ['question' => 'Како да пријавим испит?', 'category' => 'Испити'],
            ['question' => 'Које предмете имам ове godine?', 'category' => 'Предмети'],
            ['question' => 'Које су ми оцене?', 'category' => 'Оцене'],
            ['question' => 'Који је рок за уплату школарине?', 'category' => 'Школарина'],
            ['question' => 'Како да контактирам студентску службу?', 'category' => 'Контакт'],
        ];
    }

    public function getStudentInfo(int $kandidatId): array
    {
        try {
            $kandidat = Kandidat::find($kandidatId);

            if (! $kandidat) {
                return ['error' => 'Студент није пронађен'];
            }

            $polozeni = PolozeniIspiti::where('kandidat_id', $kandidatId)
                ->with('predmet')
                ->get();

            $prijave = PrijavaIspita::where('kandidat_id', $kandidatId)
                ->with(['predmet', 'rok'])
                ->get();

            return [
                'student' => $kandidat,
                'polozeni_ispiti' => $polozeni,
                'prijave' => $prijave,
            ];

        } catch (\Exception $e) {
            Log::error('Error getting student info: '.$e->getMessage());

            return ['error' => 'Грешка при учитавању података'];
        }
    }
}
