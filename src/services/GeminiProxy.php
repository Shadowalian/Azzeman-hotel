<?php
/**
 * AI Proxy Service
 *
 * Chatbot: OpenRouter API (free models, OpenAI-compatible)
 * Image generation: Google Gemini Imagen API
 */

class GeminiRateLimitException extends Exception {}

class GeminiProxy {
    private $openRouterKey;
    private $geminiKey;

    // Free OpenRouter models tried in order if one is busy/unavailable
    private $chatModels = [
        'meta-llama/llama-3.3-70b-instruct:free',
        'meta-llama/llama-3.2-3b-instruct:free',
        'qwen/qwen3-coder:free',
        'google/gemma-4-31b-it:free',
    ];

    public function __construct() {
        $this->openRouterKey = defined('OPENROUTER_API_KEY') ? OPENROUTER_API_KEY : '';
        $this->geminiKey     = defined('GEMINI_API_KEY')     ? GEMINI_API_KEY     : '';

        if (empty($this->openRouterKey) || $this->openRouterKey === 'your_openrouter_api_key_here') {
            throw new Exception('OpenRouter API key not configured');
        }
    }

    /**
     * Make a single OpenRouter chat completion request
     */
    private function callOpenRouter($model, $messages) {
        $payload = [
            'model'    => $model,
            'messages' => $messages,
        ];

        $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openRouterKey,
            'HTTP-Referer: https://azzemanhotel.com',
            'X-Title: Azzeman Hotel Chatbot',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('OpenRouter curl error: ' . $curlError);
            throw new Exception('Failed to connect to OpenRouter API');
        }

        return ['httpCode' => $httpCode, 'body' => $response];
    }

    /**
     * Chat — uses OpenRouter free models with automatic fallback
     */
    public function chat($message) {
        $systemPrompt = "You are a friendly and helpful informational assistant for Azzeman Hotel. "
            . "Your role is to answer questions about the hotel's facilities, services, and location. "
            . "You CANNOT make bookings for rooms, spa treatments, or meetings. "
            . "If a guest wants to book something, please politely guide them to use the \"Book Now\" "
            . "or \"Enquire Now\" buttons on the website. Do not make up information.\n\n"
            . "Hotel info: A four-star hotel in Addis Ababa, 2km from Bole International Airport. "
            . "Surrounded by shops and restaurants. Rooms have flat-screen TVs, Wi-Fi, mini-bar, AC, and more. "
            . "Services include a 24-hour front desk, airport transport, bar, free breakfast, gym, sauna, and laundry. "
            . "Room types: King, Twin, Deluxe, and Suites. "
            . "Conference/event halls: Entoto and Tiya (four halls total). "
            . "Spa services available. Contact: reservation@azzemanhotel.com | +251 116 393 131.";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $message],
        ];

        $lastHttpCode = 0;
        $lastResponse = '';

        foreach ($this->chatModels as $model) {
            $result   = $this->callOpenRouter($model, $messages);
            $httpCode = $result['httpCode'];
            $body     = $result['body'];

            if ($httpCode === 200) {
                $data = json_decode($body, true);
                $text = $data['choices'][0]['message']['content'] ?? null;
                if ($text !== null && $text !== '') {
                    return trim($text);
                }
                error_log('OpenRouter unexpected response (' . $model . '): ' . $body);
                throw new Exception('Unexpected response from OpenRouter API');
            }

            // 429 = rate limited, 503 = model busy — try next model
            if ($httpCode === 429 || $httpCode === 503) {
                error_log('OpenRouter quota/busy for model ' . $model . ' (HTTP ' . $httpCode . '), trying next...');
                $lastHttpCode = $httpCode;
                $lastResponse = $body;
                continue;
            }

            // Any other error — log and stop
            error_log('OpenRouter API error: HTTP ' . $httpCode . ' - ' . $body);
            throw new Exception('OpenRouter API returned an error');
        }

        // All models exhausted
        error_log('OpenRouter API exhausted all models. Last HTTP ' . $lastHttpCode . ' - ' . $lastResponse);
        throw new GeminiRateLimitException('All AI models are currently busy. Please try again in a moment.');
    }

    /**
     * Generate image with Gemini Imagen (unchanged)
     */
    public function generateImage($prompt) {
        if (empty($this->geminiKey)) {
            throw new Exception('Gemini API key not configured for image generation');
        }

        $fullPrompt = "A beautiful, luxurious hotel scene, inspired by Azzeman Hotel. {$prompt}. High-quality, photorealistic, elegant.";

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_IMAGE_MODEL . ':generateContent?key=' . $this->geminiKey;

        $payload = [
            'contents' => [
                [
                    'parts' => [['text' => $fullPrompt]],
                ]
            ],
            'generationConfig' => [
                'numberOfImages' => 1,
                'aspectRatio'    => '16:9',
            ],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log('Gemini Imagen API curl error: ' . $error);
            throw new Exception('Failed to connect to Gemini Imagen API');
        }

        if ($httpCode !== 200) {
            error_log('Gemini Imagen API error: HTTP ' . $httpCode . ' - ' . $response);
            throw new Exception('Gemini Imagen API returned an error');
        }

        $data = json_decode($response, true);

        if (!isset($data['candidates'][0]['content']['parts'][0]['inlineData']['data'])) {
            error_log('Gemini Imagen API unexpected response: ' . $response);
            throw new Exception('Unexpected response from Gemini Imagen API');
        }

        $imageData = $data['candidates'][0]['content']['parts'][0]['inlineData']['data'];
        $mimeType  = $data['candidates'][0]['content']['parts'][0]['inlineData']['mimeType'] ?? 'image/jpeg';

        return 'data:' . $mimeType . ';base64,' . $imageData;
    }
}
