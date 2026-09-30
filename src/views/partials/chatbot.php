<!-- Chatbot Component -->
<?php
// Get base path for chatbot API
$basePath = defined('APP_URL') && APP_URL !== 'https://yourdomain.com' 
    ? rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/')
    : '';
if (empty($basePath) || strpos($_SERVER['REQUEST_URI'], '/azzemanhotel_PHP') !== false) {
    $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $basePath = rtrim($scriptPath, '/');
}
?>
<button onclick="toggleChatbot()" class="fixed bottom-6 right-6 bg-brand-green text-white p-4 rounded-full shadow-lg hover:brightness-110 transition-transform transform hover:scale-110 z-50" aria-label="Open Chat" id="chatbotToggleBtn" data-base-path="<?= ViewHelper::e($basePath) ?>">
    <i data-lucide="message-square" id="chatbotOpenIcon" class="w-7 h-7"></i>
    <i data-lucide="x" id="chatbotCloseIcon" class="w-7 h-7 hidden"></i>
</button>

<div id="chatbotWindow" class="fixed bottom-24 right-6 w-80 md:w-96 h-[500px] bg-white rounded-xl shadow-2xl flex flex-col transition-all duration-300 origin-bottom-right z-50 scale-0 opacity-0">
    <header class="bg-brand-green text-white p-4 rounded-t-xl flex justify-between items-center">
        <h3 class="text-lg font-bold">Azzeman Hotel Assistant</h3>
    </header>
    <div class="flex-1 p-4 overflow-y-auto bg-gray-50" id="chatbotMessages">
        <div class="flex items-end gap-2 justify-start">
            <i data-lucide="bot" class="h-6 w-6 text-brand-green flex-shrink-0"></i>
            <div class="max-w-xs md:max-w-sm px-4 py-2 rounded-2xl bg-gray-200 text-gray-800 rounded-bl-none">
                <p class="text-sm">Welcome to Azzeman Hotel! How can I assist you today? I can answer questions about our rooms, spa, and meeting facilities.</p>
            </div>
        </div>
    </div>
    <footer class="p-3 border-t border-gray-200">
        <div class="flex items-center space-x-2">
            <input type="text" id="chatbotInput" placeholder="Ask a question..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-brand-green bg-white text-gray-900 placeholder:text-gray-500" onkeypress="if(event.key === 'Enter') sendChatbotMessage()">
            <button onclick="sendChatbotMessage()" id="chatbotSendBtn" class="bg-brand-green text-white p-2.5 rounded-full hover:brightness-110 disabled:bg-gray-400">
                <i data-lucide="send" class="w-4.5 h-4.5"></i>
            </button>
        </div>
    </footer>
</div>


