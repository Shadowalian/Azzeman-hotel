// Chatbot JavaScript
// Chatbot functionality and API integration

let chatbotOpen = false;
let chatbotMessages = [];

function toggleChatbot() {
    chatbotOpen = !chatbotOpen;
    const window = document.getElementById('chatbotWindow');
    const openIcon = document.getElementById('chatbotOpenIcon');
    const closeIcon = document.getElementById('chatbotCloseIcon');
    
    if (!window || !openIcon || !closeIcon) return;
    
    if (chatbotOpen) {
        window.classList.remove('scale-0', 'opacity-0');
        window.classList.add('scale-100', 'opacity-100');
        openIcon.classList.add('hidden');
        closeIcon.classList.remove('hidden');
        document.getElementById('chatbotInput')?.focus();
    } else {
        window.classList.remove('scale-100', 'opacity-100');
        window.classList.add('scale-0', 'opacity-0');
        openIcon.classList.remove('hidden');
        closeIcon.classList.add('hidden');
    }
}

function addChatbotMessage(sender, text) {
    const messagesContainer = document.getElementById('chatbotMessages');
    if (!messagesContainer) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex items-end gap-2 ${sender === 'user' ? 'justify-end' : 'justify-start'} mb-4`;
    
    if (sender === 'bot') {
        messageDiv.innerHTML = `
            <i data-lucide="bot" class="h-6 w-6 text-brand-green flex-shrink-0"></i>
            <div class="max-w-xs md:max-w-sm px-4 py-2 rounded-2xl bg-gray-200 text-gray-800 rounded-bl-none">
                <p class="text-sm">${escapeHtml(text)}</p>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="max-w-xs md:max-w-sm px-4 py-2 rounded-2xl bg-brand-gold text-white rounded-br-none">
                <p class="text-sm">${escapeHtml(text)}</p>
            </div>
            <i data-lucide="user" class="h-6 w-6 text-gray-400 flex-shrink-0"></i>
        `;
    }
    
    // Initialize Lucide icons for new message
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function sendChatbotMessage() {
    const input = document.getElementById('chatbotInput');
    const message = input?.value.trim();
    if (!message || !input) return;
    
    const sendBtn = document.getElementById('chatbotSendBtn');
    if (sendBtn) sendBtn.disabled = true;
    
    // Add user message
    addChatbotMessage('user', message);
    input.value = '';
    
    // Show loading
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'flex items-end gap-2 justify-start mb-4';
    loadingDiv.id = 'chatbotLoading';
    loadingDiv.innerHTML = `
        <i data-lucide="bot" class="h-6 w-6 text-brand-green flex-shrink-0"></i>
        <div class="max-w-xs md:max-w-sm px-4 py-2 rounded-2xl bg-gray-200 text-gray-800 rounded-bl-none">
            <div class="flex items-center space-x-1">
                <span class="h-2 w-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: -0.3s"></span>
                <span class="h-2 w-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: -0.15s"></span>
                <span class="h-2 w-2 bg-gray-400 rounded-full animate-bounce"></span>
            </div>
        </div>
    `;
    
    // Initialize Lucide icons for loading
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    const messagesContainer = document.getElementById('chatbotMessages');
    if (messagesContainer) {
        messagesContainer.appendChild(loadingDiv);
    }
    
    try {
        // Get base path from data attribute or calculate it
        const chatbotBtn = document.getElementById('chatbotToggleBtn');
        let basePath = '';
        
        if (chatbotBtn && chatbotBtn.dataset.basePath) {
            basePath = chatbotBtn.dataset.basePath;
        } else {
            // Fallback: calculate from current path
            const pathname = window.location.pathname;
            if (pathname.includes('/azzemanhotel_PHP')) {
                basePath = '/azzemanhotel_PHP';
            } else {
                // Get the directory path
                const pathParts = pathname.split('/').filter(p => p && p !== 'index.php');
                if (pathParts.length > 0 && pathParts[pathParts.length - 1] !== '') {
                    // Remove the last part if it's a file
                    const lastPart = pathParts[pathParts.length - 1];
                    if (lastPart.includes('.')) {
                        pathParts.pop();
                    }
                }
                basePath = pathParts.length > 0 ? '/' + pathParts.join('/') : '';
            }
        }
        
        // Ensure basePath doesn't end with a slash
        basePath = basePath.replace(/\/$/, '');
        
        const apiUrl = basePath + '/api/chatbot';
        
        // Debug logging (remove in production)
        if (typeof console !== 'undefined' && console.log) {
            console.log('Chatbot API URL:', apiUrl);
        }
        
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: message }),
        });
        
        // Remove loading first
        const loading = document.getElementById('chatbotLoading');
        if (loading) loading.remove();

        const data = await response.json();
        
        if (!response.ok || data.error) {
            // Use the server's friendly error message if available
            let errMsg = data.error || 'Sorry, I\'m having trouble responding right now.';
            addChatbotMessage('bot', errMsg);
        } else {
            addChatbotMessage('bot', data.response || data.text || 'Sorry, I didn\'t understand that. Can you please rephrase?');
        }
    } catch (error) {
        console.error('Chatbot error:', error);
        const loading = document.getElementById('chatbotLoading');
        if (loading) loading.remove();
        
        addChatbotMessage('bot', 'Sorry, I\'m having trouble connecting right now. Please try again in a moment.');
    } finally {
        if (sendBtn) sendBtn.disabled = false;
    }
}

// Initialize chatbot on page load
document.addEventListener('DOMContentLoaded', () => {
    // Add welcome message if no messages exist
    const messagesContainer = document.getElementById('chatbotMessages');
    if (messagesContainer && messagesContainer.children.length === 1) {
        // Welcome message already in HTML
    }
});

