<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\PrijavaIspita;
use App\Models\Predmet;
use App\Models\Profesor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    protected $model = 'gpt-4';
    protected $maxTokens = 500;
    protected $temperature = 0.7;

    public function __construct()
    {
        // Constructor - can be used for initialization
    }

    public function chat(string $message, array $conversationHistory = []): array
    {
        try {
            // Build system prompt with context about the system
            $systemPrompt = $this->buildSystemPrompt();
            
            // Build messages array
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
            ];
            
            // Add conversation history
            foreach ($conversationHistory as $history) {
                $messages[] = $history;
            }
            
            // Add current user message
            $messages[] = ['role' => 'user', 'content' => $message];
            
            // Call OpenAI API
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
            Log::error('Chatbot error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Извините, дошло је до грешке. Молимо покушајте поново.',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function buildSystemPrompt(): string
    {
        $user = Auth::user();
        $userName = $user->name ?? 'Корисник';
        
        $prompt = "Ти си AI асistent за факултетски систем. Твоја улога је да помогнеш студентима и администрацији.\n\n";
        $prompt .= "Информације о систему:\n";
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
        $prompt .= "- Ако не знаш одговор, реци то\n";
        $prompt .= "- Не измишљај информације\n";
        $prompt .= "- Не приступај осетљивим подацима (лозинке, лични подаци)\n\n";
        
        // Add context about current user
        if ($user) {
            $prompt .= "Тренутни корисник: {$userName}\n";
            
            // Add student-specific context if user is a student
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
            [
                'question' => 'Када је следећи испитни рок?',
                'category' => 'Испити',
            ],
            [
                'question' => 'Како да пријавим испит?',
                'category' => 'Испити',
            ],
            [
                'question' => 'Које предмете имам ове године?',
                'category' => 'Предмети',
            ],
            [
                'question' => 'Које су ми оцене?',
                'category' => 'Оцене',
            ],
            [
                'question' => 'Који је рок за уплату школарине?',
                'category' => 'Школарина',
            ],
            [
                'question' => 'Како да контактирам студентску службу?',
                'category' => 'Контакт',
            ],
        ];
    }

    public function getStudentInfo(int $kandidatId): array
    {
        try {
            $kandidat = Kandidat::find($kandidatId);
            
            if (!$kandidat) {
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
            Log::error('Error getting student info: ' . $e->getMessage());
            return ['error' => 'Грешка при учитавању података'];
        }
    }
}
