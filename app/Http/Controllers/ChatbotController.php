<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatMessageRequest;
use App\Http\Requests\QuickQuestionRequest;
use App\Services\ChatbotService;
use Illuminate\Support\Facades\Session;

class ChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->middleware('auth');
        $this->chatbotService = $chatbotService;
    }

    public function index()
    {
        $quickQuestions = $this->chatbotService->getQuickQuestions();

        return view('chatbot.index', compact('quickQuestions'));
    }

    public function chat(ChatMessageRequest $request)
    {
        $message = $request->input('message');

        // Get conversation history from session
        $conversationHistory = Session::get('chatbot_history', []);

        // Add user message to history
        $conversationHistory[] = ['role' => 'user', 'content' => $message];

        // Get response from chatbot
        $response = $this->chatbotService->chat($message, $conversationHistory);

        if ($response['success']) {
            // Add assistant response to history
            $conversationHistory[] = ['role' => 'assistant', 'content' => $response['message']];

            // Keep only last 10 messages to avoid token limit
            if (count($conversationHistory) > 10) {
                $conversationHistory = array_slice($conversationHistory, -10);
            }

            // Save to session
            Session::put('chatbot_history', $conversationHistory);

            return response()->json([
                'success' => true,
                'message' => $response['message'],
                'usage' => $response['usage'] ?? null,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $response['message'],
            ], 500);
        }
    }

    public function clearHistory()
    {
        Session::forget('chatbot_history');

        return response()->json([
            'success' => true,
            'message' => 'Историја разговора је обрисана',
        ]);
    }

    public function quickQuestion(QuickQuestionRequest $request)
    {
        $question = $request->input('question');

        // Get conversation history from session
        $conversationHistory = Session::get('chatbot_history', []);

        // Get response from chatbot
        $response = $this->chatbotService->chat($question, $conversationHistory);

        if ($response['success']) {
            // Add both question and response to history
            $conversationHistory[] = ['role' => 'user', 'content' => $question];
            $conversationHistory[] = ['role' => 'assistant', 'content' => $response['message']];

            // Keep only last 10 messages
            if (count($conversationHistory) > 10) {
                $conversationHistory = array_slice($conversationHistory, -10);
            }

            // Save to session
            Session::put('chatbot_history', $conversationHistory);

            return response()->json([
                'success' => true,
                'message' => $response['message'],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $response['message'],
            ], 500);
        }
    }
}
