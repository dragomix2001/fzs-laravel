@extends('layouts.layout')

@section('title', 'AI Chatbot - Факултет за спорт')

@section('page_heading', 'AI Chatbot')

@section('section')
<div class="col-span-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-primary-600 text-white">
                    <h5 class="text-base font-semibold mb-0">
                        <i class="fas fa-robot mr-2"></i>
                        AI Асистент
                    </h5>
                </div>
                <div>
                    <div id="chatMessages" class="chat-messages p-3" style="height: 400px; overflow-y: auto; background-color: #f8f9fa;">
                        <div class="message bot-message mb-3">
                            <div class="flex">
                                <div class="avatar bg-primary text-white rounded-full flex items-center justify-center mr-2 shrink-0" style="width: 40px; height: 40px;">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="message-content bg-white p-3 rounded-lg shadow-sm" style="max-width: 80%;">
                                    <p class="mb-0 text-sm text-secondary-700">Здраво! Ја сам AI асistent за Факултет за спорт. Како могу да вам помогнем?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chat-input p-3 border-t border-secondary-200">
                        <form id="chatForm" class="flex gap-2">
                            @csrf
                            <input type="text" 
                                   id="messageInput" 
                                   class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" 
                                   placeholder="Укуцајте ваше питање..." 
                                   autocomplete="off">
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                            <button type="button" id="clearHistory" class="inline-flex items-center justify-center px-4 py-2 bg-secondary-100 hover:bg-secondary-200 text-secondary-700 text-sm font-medium rounded-lg transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div>
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-cyan-600 text-white">
                    <h5 class="text-base font-semibold mb-0">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Брза питања
                    </h5>
                </div>
                <div class="p-4 space-y-2">
                    @foreach($quickQuestions as $index => $item)
                    <button type="button" 
                            class="w-full text-left px-3 py-2 rounded-lg hover:bg-secondary-50 border border-secondary-200 text-sm text-secondary-700 quick-question transition-colors"
                            data-question="{{ $item['question'] }}">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary-100 text-secondary-700 mr-2">{{ $item['category'] }}</span>
                        {{ $item['question'] }}
                    </button>
                    @endforeach
                </div>
            </div>
            
            <div class="bg-white rounded-lg border border-secondary-200 shadow-sm overflow-hidden mt-3">
                <div class="px-4 py-3 bg-warning-500 text-white">
                    <h5 class="text-base font-semibold mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        Информације
                    </h5>
                </div>
                <div class="p-4 space-y-2">
                    <p class="text-sm text-secondary-600 mb-2">
                        <i class="fas fa-check text-success-500 mr-2"></i>
                        Одговара на питања о испитима
                    </p>
                    <p class="text-sm text-secondary-600 mb-2">
                        <i class="fas fa-check text-success-500 mr-2"></i>
                        Пружа информације о предметима
                    </p>
                    <p class="text-sm text-secondary-600 mb-2">
                        <i class="fas fa-check text-success-500 mr-2"></i>
                        Помаже са процедурама
                    </p>
                    <p class="text-sm text-secondary-500 mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        За званичне информације контактирајте студентску службу
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-messages {
    display: flex;
    flex-direction: column;
}
.message {
    display: flex;
    margin-bottom: 1rem;
}
.user-message {
    justify-content: flex-end;
}
.user-message .flex {
    flex-direction: row-reverse;
}
.user-message .avatar {
    background-color: #6c757d !important;
}
.message-content {
    word-wrap: break-word;
}
.bot-message .message-content {
    background-color: #ffffff;
}
.user-message .message-content {
    background-color: #2563eb;
    color: #ffffff;
}
.typing-indicator {
    display: flex;
    gap: 4px;
    padding: 10px;
}
.typing-indicator span {
    width: 8px;
    height: 8px;
    background-color: #6c757d;
    border-radius: 50%;
    animation: typing 1.4s infinite ease-in-out;
}
.typing-indicator span:nth-child(1) { animation-delay: 0s; }
.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const chatMessages = document.getElementById('chatMessages');
    const clearHistoryBtn = document.getElementById('clearHistory');
    const quickQuestionBtns = document.querySelectorAll('.quick-question');
    
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    function addMessage(content, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'} mb-3`;
        const avatarIcon = isUser ? 'fa-user' : 'fa-robot';
        const avatarBg = isUser ? 'bg-secondary' : 'bg-primary';
        messageDiv.innerHTML = `
            <div class="flex">
                <div class="avatar ${avatarBg} text-white rounded-full flex items-center justify-center mr-2 shrink-0" style="width: 40px; height: 40px;">
                    <i class="fas ${avatarIcon}"></i>
                </div>
                <div class="message-content ${isUser ? 'bg-primary text-white' : 'bg-white'} p-3 rounded-lg shadow-sm" style="max-width: 80%;">
                    <p class="mb-0 text-sm">${content}</p>
                </div>
            </div>
        `;
        chatMessages.appendChild(messageDiv);
        scrollToBottom();
    }
    
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.id = 'typingIndicator';
        typingDiv.className = 'message bot-message mb-3';
        typingDiv.innerHTML = `
            <div class="flex">
                <div class="avatar bg-primary text-white rounded-full flex items-center justify-center mr-2 shrink-0" style="width: 40px; height: 40px;">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content bg-white p-3 rounded-lg shadow-sm">
                    <div class="typing-indicator">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>
        `;
        chatMessages.appendChild(typingDiv);
        scrollToBottom();
    }
    
    function removeTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) indicator.remove();
    }
    
    async function sendMessage(message) {
        if (!message.trim()) return;
        addMessage(message, true);
        messageInput.value = '';
        showTypingIndicator();
        try {
            const response = await fetch('{{ route("chatbot.chat") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ message: message }),
            });
            const data = await response.json();
            removeTypingIndicator();
            if (data.success) addMessage(data.message, false);
            else addMessage('Извините, дошло је до грешке. Молимо покушајте поново.', false);
        } catch (error) {
            console.error('Error:', error);
            removeTypingIndicator();
            addMessage('Извините, дошло је до грешке. Молимо покушајте поново.', false);
        }
    }
    
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (message) sendMessage(message);
    });
    
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (message) sendMessage(message);
        }
    });
    
    quickQuestionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            sendMessage(this.getAttribute('data-question'));
        });
    });
    
    clearHistoryBtn.addEventListener('click', async function() {
        if (confirm('Да ли сте сигурни да желите да обришете историју разговора?')) {
            try {
                const response = await fetch('{{ route("chatbot.clear") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                });
                const data = await response.json();
                if (data.success) {
                    chatMessages.innerHTML = `
                        <div class="message bot-message mb-3">
                            <div class="flex">
                                <div class="avatar bg-primary text-white rounded-full flex items-center justify-center mr-2 shrink-0" style="width: 40px; height: 40px;">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="message-content bg-white p-3 rounded-lg shadow-sm" style="max-width: 80%;">
                                    <p class="mb-0 text-sm text-secondary-700">Историја разговора је обрисана. Како могу да вам помогнем?</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
            } catch (error) { console.error('Error:', error); }
        }
    });
    
    scrollToBottom();
});
</script>
@endsection
