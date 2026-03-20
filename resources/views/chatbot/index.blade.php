@extends('layouts.layout')

@section('title', 'AI Chatbot - Факултет за спорт')

@section('page_heading', 'AI Chatbot')

@section('section')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-robot me-2"></i>
                        AI Асистент
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div id="chatMessages" class="chat-messages p-3" style="height: 400px; overflow-y: auto; background-color: #f8f9fa;">
                        <div class="message bot-message mb-3">
                            <div class="d-flex">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="message-content bg-white p-3 rounded shadow-sm" style="max-width: 80%;">
                                    <p class="mb-0">Здраво! Ја сам AI асistent за Факултет за спорт. Како могу да вам помогнем?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chat-input p-3 border-top">
                        <form id="chatForm" class="d-flex gap-2">
                            @csrf
                            <input type="text" 
                                   id="messageInput" 
                                   class="form-control" 
                                   placeholder="Укуцајте ваше питање..." 
                                   autocomplete="off">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                            <button type="button" id="clearHistory" class="btn btn-outline-secondary">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Број питања
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($quickQuestions as $index => $item)
                        <button type="button" 
                                class="list-group-item list-group-item-action quick-question"
                                data-question="{{ $item['question'] }}">
                            <span class="badge bg-secondary me-2">{{ $item['category'] }}</span>
                            {{ $item['question'] }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Информације
                    </h5>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Одговара на питања о испитима
                    </p>
                    <p class="small mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Пружа информације о предметима
                    </p>
                    <p class="small mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Помаже са процедурама
                    </p>
                    <p class="small mb-0 text-muted">
                        <i class="fas fa-exclamation-triangle me-2"></i>
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

.user-message .d-flex {
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
    background-color: #007bff;
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

.typing-indicator span:nth-child(1) {
    animation-delay: 0s;
}

.typing-indicator span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% {
        transform: translateY(0);
    }
    30% {
        transform: translateY(-10px);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const chatMessages = document.getElementById('chatMessages');
    const clearHistoryBtn = document.getElementById('clearHistory');
    const quickQuestionBtns = document.querySelectorAll('.quick-question');
    
    // Scroll to bottom of chat
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Add message to chat
    function addMessage(content, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'} mb-3`;
        
        const avatarIcon = isUser ? 'fa-user' : 'fa-robot';
        const avatarBg = isUser ? 'bg-secondary' : 'bg-primary';
        
        messageDiv.innerHTML = `
            <div class="d-flex">
                <div class="avatar ${avatarBg} text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                    <i class="fas ${avatarIcon}"></i>
                </div>
                <div class="message-content ${isUser ? 'bg-primary text-white' : 'bg-white'} p-3 rounded shadow-sm" style="max-width: 80%;">
                    <p class="mb-0">${content}</p>
                </div>
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        scrollToBottom();
    }
    
    // Show typing indicator
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.id = 'typingIndicator';
        typingDiv.className = 'message bot-message mb-3';
        typingDiv.innerHTML = `
            <div class="d-flex">
                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content bg-white p-3 rounded shadow-sm">
                    <div class="typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        `;
        
        chatMessages.appendChild(typingDiv);
        scrollToBottom();
    }
    
    // Remove typing indicator
    function removeTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) {
            indicator.remove();
        }
    }
    
    // Send message
    async function sendMessage(message) {
        if (!message.trim()) return;
        
        // Add user message to chat
        addMessage(message, true);
        
        // Clear input
        messageInput.value = '';
        
        // Show typing indicator
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
            
            // Remove typing indicator
            removeTypingIndicator();
            
            if (data.success) {
                // Add bot response to chat
                addMessage(data.message, false);
            } else {
                addMessage('Извините, дошло је до грешке. Молимо покушајте поново.', false);
            }
            
        } catch (error) {
            console.error('Error:', error);
            removeTypingIndicator();
            addMessage('Извините, дошло је до грешке. Молимо покушајте поново.', false);
        }
    }
    
    // Handle form submission
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (message) {
            sendMessage(message);
        }
    });
    
    // Handle Enter key
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (message) {
                sendMessage(message);
            }
        }
    });
    
    // Handle quick questions
    quickQuestionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const question = this.getAttribute('data-question');
            sendMessage(question);
        });
    });
    
    // Clear history
    clearHistoryBtn.addEventListener('click', async function() {
        if (confirm('Да ли сте сигурни да желите да обришете историју разговора?')) {
            try {
                const response = await fetch('{{ route("chatbot.clear") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Clear chat messages
                    chatMessages.innerHTML = `
                        <div class="message bot-message mb-3">
                            <div class="d-flex">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="message-content bg-white p-3 rounded shadow-sm" style="max-width: 80%;">
                                    <p class="mb-0">Историја разговора је обрисана. Како могу да вам помогнем?</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
            } catch (error) {
                console.error('Error:', error);
            }
        }
    });
    
    // Scroll to bottom on load
    scrollToBottom();
});
</script>
@endsection
