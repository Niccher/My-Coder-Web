<?php

/**
 * ChatController — Handles real multi-model AI requests and master model evaluation.
 *
 * Slot assignments:
 *   Slots 1–3: Regular AI models (Gemini, DeepSeek, Grok, etc.)
 *   Slot 4:    Master Model — evaluates the 3 responses and picks the best
 *
 * Each model is identified by a 'provider' to determine which API to call.
 * Supported providers: gemini | deepseek | grok | openai
 */

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ConversationModel;
use App\Models\MessageModel;
use App\Models\UserSettingModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class ChatController extends BaseController
{
    use ResponseTrait;

    /**
     * POST /api/chat
     *
     * Body (JSON):
     *   prompt          string  — The user's message
     *   conversation_id int?    — If null, creates a new conversation
     *   file_context    string? — Text extracted from uploaded files
     */
    public function send(): ResponseInterface
    {
        $body           = $this->request->getJSON(true);
        $prompt         = trim($body['prompt'] ?? '');
        $fileContext    = trim($body['file_context'] ?? '');
        $conversationId = (int) ($body['conversation_id'] ?? 0);
        $personaId      = (int) ($body['persona_id'] ?? 0);

        if ($prompt === '') {
            return $this->fail('Prompt cannot be empty.', 422);
        }

        $userId = auth()->id();

        // ── Conversation management ───────────────────────────────────────────
        $convModel = new ConversationModel();
        if ($conversationId === 0) {
            $title = substr($prompt, 0, 40) . (strlen($prompt) > 40 ? '...' : '');
            $conversationId = $convModel->insert([
                'user_id'    => $userId, 
                'title'      => $title,
                'persona_id' => $personaId > 0 ? $personaId : null
            ], true);
        } else {
            // Validate ownership
            $conv = $convModel->findForUser($conversationId, $userId);
            if (!$conv) {
                return $this->failNotFound('Conversation not found.');
            }
            // If personaId was NOT provided in request, use the one from DB
            if ($personaId <= 0 && !empty($conv['persona_id'])) {
                $personaId = (int) $conv['persona_id'];
            }
            
            // If personaId was provided and differs from DB, update it
            if ($personaId > 0 && (int) ($conv['persona_id'] ?? 0) !== $personaId) {
                $convModel->update($conversationId, ['persona_id' => $personaId]);
            }
        }

        // ── Save the user message ─────────────────────────────────────────────
        $msgModel = new MessageModel();
        $userMsgId = $msgModel->saveUserMessage($conversationId, $prompt);

        // ── Load model settings ───────────────────────────────────────────────
        $settingModel = new UserSettingModel();
        $settings     = $settingModel->getForUser($userId); // keyed by slot 1–4
        
        // Filter active standard slots (1–3)
        $activeModels = [];
        foreach ([1, 2, 3] as $slot) {
            if (!empty($settings[$slot]['api_key']) && !empty($settings[$slot]['provider'])) {
                $activeModels[$slot] = $settings[$slot];
            }
        }

        if (empty($activeModels)) {
            return $this->fail('No AI models configured. Please add API keys in Settings.', 428);
        }

        // ── Persona / System Instructions ────────────────────────────────────
        $systemInstructions = '';
        if ($personaId > 0) {
            $personaModel = new \App\Models\PersonaModel();
            $persona = $personaModel->where('id', $personaId)->where('user_id', $userId)->first();
            if ($persona) {
                $systemInstructions = $persona['instructions'];
            }
        }

        // ── Build full prompt (with file context if any) ──────────────────────
        // Note: system instructions are passed natively to each provider's system field.
        $fullPrompt = $prompt;
        if ($fileContext !== '') {
            $fullPrompt = "Context from attached files:\n---\n{$fileContext}\n---\n\nUser question: {$prompt}";
        }

        // ── Load conversation history (includes the just-saved user message) ──
        $history = $msgModel->getForConversation($conversationId);

        // Inject file context into the last message for this API call only.
        // File context is intentionally NOT persisted to the DB.
        if ($fileContext !== '' && !empty($history)) {
            $lastKey = array_key_last($history);
            $history[$lastKey]['content'] = $fullPrompt;
        }

        // ── Call active models in parallel (curl_multi) ───────────────────────
        $responses      = $this->queryModelsParallel($activeModels, $history, $systemInstructions);
        $modelResults   = [];

        foreach ($responses as $slot => $response) {
            $modelName = $settings[$slot]['model_name'] ?? "Model {$slot}";
            $msgModel->saveAiMessage($conversationId, $modelName, $response['content']);
            $modelResults[] = [
                'slot'       => $slot,
                'model_name' => $modelName,
                'provider'   => $settings[$slot]['provider'],
                'content'    => $response['content'],
                'error'      => $response['error'] ?? null,
            ];
        }

        // ── Master Model Evaluation (slot 4) ──────────────────────────────────
        $masterEval = null;
        if (!empty($settings[4]['api_key']) && !empty($settings[4]['provider']) && count($modelResults) > 1) {
            $masterEval = $this->queryMasterModel($settings[4], $prompt, $modelResults);
            if ($masterEval !== null) {
                $masterName = $settings[4]['model_name'] ?? 'Master';
                $msgModel->saveAiMessage($conversationId, $masterName . ' [Master]', $masterEval);
            }
        }

        // ── Update conversation timestamp ─────────────────────────────────────
        $convModel->builder()->set('updated_at', date('Y-m-d H:i:s'))->where('id', $conversationId)->update();

        return $this->respond([
            'conversation_id' => $conversationId,
            'persona_id'      => $personaId,
            'user_message_id' => $userMsgId,
            'models'          => $modelResults,
            'master_eval'     => $masterEval,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PARALLEL MULTI-MODEL QUERYING
    // ──────────────────────────────────────────────────────────────────────────

    private function queryModelsParallel(array $models, array $history, string $systemInstructions): array
    {
        $curlHandles = [];
        $multiCurl   = curl_multi_init();
        $results     = [];

        foreach ($models as $slot => $model) {
            // Apply this slot's history limit (null = unlimited)
            $limit       = (int) ($model['history_limit'] ?? 0);
            $slotHistory = ($limit > 0 && count($history) > $limit)
                ? array_slice($history, -$limit)
                : $history;

            $ch = $this->buildCurlHandle($model, $slotHistory, $systemInstructions);
            if ($ch) {
                $curlHandles[$slot] = $ch;
                curl_multi_add_handle($multiCurl, $ch);
            }
        }

        // Execute all in parallel
        $running = null;
        do {
            curl_multi_exec($multiCurl, $running);
            curl_multi_select($multiCurl);
        } while ($running > 0);

        // Collect results
        foreach ($curlHandles as $slot => $ch) {
            $rawResponse = curl_multi_getcontent($ch);
            $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($multiCurl, $ch);
            curl_close($ch);

            $results[$slot] = $this->parseApiResponse($models[$slot]['provider'], $rawResponse, $httpCode);
        }

        curl_multi_close($multiCurl);

        return $results;
    }

    private function buildCurlHandle(array $model, array $history, string $systemInstructions = ''): ?\CurlHandle
    {
        $provider = $model['provider'];
        $apiKey   = $model['api_key'];
        $name     = $model['model_name'] ?? '';

        switch ($provider) {
            case 'gemini':
                $modelId  = $this->resolveGeminiModelId($name);
                $url      = "https://generativelanguage.googleapis.com/v1beta/models/{$modelId}:generateContent?key={$apiKey}";
                $body     = ['contents' => $this->buildGeminiContents($history, $name)];
                if ($systemInstructions !== '') {
                    $body['system_instruction'] = ['parts' => [['text' => $systemInstructions]]];
                }
                return $this->createCurlPost($url, json_encode($body), ['Content-Type: application/json']);

            case 'deepseek':
                $url     = 'https://api.deepseek.com/v1/chat/completions';
                $payload = json_encode(['model' => $this->resolveDeepSeekModelId($name), 'messages' => $this->buildOpenAiMessages($history, $name, $systemInstructions)]);
                return $this->createCurlPost($url, $payload, ['Content-Type: application/json', "Authorization: Bearer {$apiKey}"]);

            case 'grok':
                $url     = 'https://api.x.ai/v1/chat/completions';
                $payload = json_encode(['model' => $this->resolveGrokModelId($name), 'messages' => $this->buildOpenAiMessages($history, $name, $systemInstructions)]);
                return $this->createCurlPost($url, $payload, ['Content-Type: application/json', "Authorization: Bearer {$apiKey}"]);

            case 'openai':
                $url     = 'https://api.openai.com/v1/chat/completions';
                $payload = json_encode(['model' => $this->resolveOpenAIModelId($name), 'messages' => $this->buildOpenAiMessages($history, $name, $systemInstructions)]);
                return $this->createCurlPost($url, $payload, ['Content-Type: application/json', "Authorization: Bearer {$apiKey}"]);

            case 'kimi':
                $url     = 'https://api.moonshot.cn/v1/chat/completions';
                $payload = json_encode(['model' => $this->resolveKimiModelId($name), 'messages' => $this->buildOpenAiMessages($history, $name, $systemInstructions)]);
                return $this->createCurlPost($url, $payload, ['Content-Type: application/json', "Authorization: Bearer {$apiKey}"]);

            case 'minimax':
                $url     = 'https://api.minimax.io/v1/chat/completions';
                $payload = json_encode(['model' => $this->resolveMiniMaxModelId($name), 'messages' => $this->buildOpenAiMessages($history, $name, $systemInstructions)]);
                return $this->createCurlPost($url, $payload, ['Content-Type: application/json', "Authorization: Bearer {$apiKey}"]);

            case 'groq':
                $url     = 'https://api.groq.com/openai/v1/chat/completions';
                $payload = json_encode(['model' => $this->resolveGroqModelId($name), 'messages' => $this->buildOpenAiMessages($history, $name, $systemInstructions)]);
                return $this->createCurlPost($url, $payload, ['Content-Type: application/json', "Authorization: Bearer {$apiKey}"]);

            case 'openrouter':
                $url     = 'https://openrouter.ai/api/v1/chat/completions';
                $payload = json_encode(['model' => $this->resolveOpenRouterModelId($name), 'messages' => $this->buildOpenAiMessages($history, $name, $systemInstructions)]);
                return $this->createCurlPost($url, $payload, [
                    'Content-Type: application/json',
                    "Authorization: Bearer {$apiKey}",
                    "HTTP-Referer: https://mycoder.chegecache.co.ke",
                    "X-Title: My Coder Chat"
                ]);

            default:
                return null;
        }
    }

    private function createCurlPost(string $url, string $payload, array $headers): \CurlHandle
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 300,
        ]);
        return $ch;
    }

    private function parseApiResponse(string $provider, ?string $raw, int $httpCode): array
    {
        if (!$raw || $httpCode >= 400) {
            $decoded = json_decode($raw ?? '', true);
            $errMsg  = $decoded['error']['message'] ?? "HTTP {$httpCode} error.";
            return ['content' => '', 'error' => $errMsg];
        }

        $data = json_decode($raw, true);

        try {
            $content = match ($provider) {
                'gemini'   => $data['candidates'][0]['content']['parts'][0]['text'] ?? '',
                'deepseek',
                'grok',
                'openai',
                'kimi',
                'minimax',
                'groq',
                'openrouter' => $data['choices'][0]['message']['content'] ?? '',
                default      => '',
            };
        } catch (\Throwable) {
            $content = '';
        }

        return ['content' => $content ?: '(No response)', 'error' => null];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // MASTER MODEL EVALUATION
    // ──────────────────────────────────────────────────────────────────────────

    private function queryMasterModel(array $masterConfig, string $userPrompt, array $modelResults): ?string
    {
        // Build a rich evaluation prompt for the master model
        $evaluationPrompt = "You are an expert AI evaluator. A user asked the following question:\n\n";
        $evaluationPrompt .= "\"{$userPrompt}\"\n\n";
        $evaluationPrompt .= "The following AI models provided answers:\n\n";

        foreach ($modelResults as $result) {
            if (!empty($result['content']) && !$result['error']) {
                $evaluationPrompt .= "--- {$result['model_name']} ---\n{$result['content']}\n\n";
            }
        }

        $evaluationPrompt .= "Evaluate these responses and:\n";
        $evaluationPrompt .= "1. Identify which response is the **best** and explain why briefly.\n";
        $evaluationPrompt .= "2. Point out any incorrect or misleading information in any response.\n";
        $evaluationPrompt .= "3. If appropriate, provide a synthesized final answer combining the best parts.\n";
        $evaluationPrompt .= "Keep your evaluation concise and focused (2-4 paragraphs).";

        $result = $this->querySingleModel($masterConfig, $evaluationPrompt);
        return $result['error'] ? null : $result['content'];
    }

    private function querySingleModel(array $model, string $prompt): array
    {
        // Build a minimal single-turn history for the master model evaluation prompt.
        $singleHistory = [['role' => 'user', 'model_name' => null, 'content' => $prompt]];
        $ch = $this->buildCurlHandle($model, $singleHistory, '');
        if (!$ch) {
            return ['content' => '', 'error' => 'Unknown provider.'];
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $this->parseApiResponse($model['provider'], $raw ?: null, $httpCode);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // MESSAGE HISTORY BUILDERS
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Build an OpenAI-compatible messages array from conversation history.
     *
     * AI messages are filtered to only include responses from THIS specific model
     * (identified by model_name), so each model gets a clean, alternating
     * user/assistant thread without cross-model contamination.
     */
    private function buildOpenAiMessages(array $history, string $modelName, string $systemInstructions): array
    {
        $messages = [];

        if ($systemInstructions !== '') {
            $messages[] = ['role' => 'system', 'content' => $systemInstructions];
        }

        foreach ($history as $msg) {
            if ($msg['role'] === 'user') {
                $messages[] = ['role' => 'user', 'content' => $msg['content']];
            } elseif ($msg['role'] === 'ai' && ($msg['model_name'] ?? '') === $modelName) {
                $messages[] = ['role' => 'assistant', 'content' => $msg['content']];
            }
        }

        return $messages;
    }

    /**
     * Build a Gemini-compatible contents array from conversation history.
     *
     * Uses 'user' / 'model' roles. Filters AI messages to only include
     * this model's responses. Merges consecutive user messages to prevent
     * API errors caused by missed AI turns (e.g. when a model failed a prior turn).
     */
    private function buildGeminiContents(array $history, string $modelName): array
    {
        $contents = [];
        $lastRole = null;

        foreach ($history as $msg) {
            if ($msg['role'] === 'user') {
                if ($lastRole === 'user' && !empty($contents)) {
                    // Merge into the previous user turn to avoid consecutive same-role error.
                    $last = count($contents) - 1;
                    $contents[$last]['parts'][0]['text'] .= "\n\n" . $msg['content'];
                } else {
                    $contents[] = ['role' => 'user', 'parts' => [['text' => $msg['content']]]];
                    $lastRole   = 'user';
                }
            } elseif ($msg['role'] === 'ai' && ($msg['model_name'] ?? '') === $modelName) {
                $contents[] = ['role' => 'model', 'parts' => [['text' => $msg['content']]]];
                $lastRole   = 'model';
            }
        }

        return $contents;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // MODEL ID RESOLVERS
    // ──────────────────────────────────────────────────────────────────────────

    private function resolveGeminiModelId(string $name): string
    {
        $map = [
            'Gemini 2.0 Flash'   => 'gemini-2.0-flash',
            'Gemini 2.0 Pro'     => 'gemini-2.0-pro-exp-02-05',
            'Gemini 1.5 Flash'   => 'gemini-1.5-flash',
            'Gemini 1.5 Pro'     => 'gemini-1.5-pro',
        ];
        return $map[$name] ?? 'gemini-2.0-flash';
    }

    private function resolveDeepSeekModelId(string $name): string
    {
        $map = [
            'DeepSeek R1'        => 'deepseek-reasoner',
            'DeepSeek V3'        => 'deepseek-chat',
        ];
        return $map[$name] ?? 'deepseek-chat';
    }

    private function resolveGrokModelId(string $name): string
    {
        $map = [
            'Grok-2'             => 'grok-2',
            'Grok-2 Mini'        => 'grok-2-mini',
            'Grok-3'             => 'grok-3',
        ];
        return $map[$name] ?? 'grok-2';
    }

    private function resolveOpenAIModelId(string $name): string
    {
        $map = [
            'GPT-4o'             => 'gpt-4o',
            'GPT-4o Mini'        => 'gpt-4o-mini',
            'GPT-4 Turbo'        => 'gpt-4-turbo',
            'o1'                 => 'o1',
            'o3 Mini'            => 'o3-mini',
        ];
        return $map[$name] ?? 'gpt-4o';
    }

    private function resolveKimiModelId(string $name): string
    {
        $map = [
            'Kimi k1.5'          => 'kimi-k1.5',
            'moonshot-v1-8k'     => 'moonshot-v1-8k',
            'moonshot-v1-32k'    => 'moonshot-v1-32k',
            'moonshot-v1-128k'   => 'moonshot-v1-128k',
        ];
        return $map[$name] ?? 'moonshot-v1-8k';
    }

    private function resolveMiniMaxModelId(string $name): string
    {
        $map = [
            'MiniMax-Text-01'    => 'minimax-text-01',
            'abab6.5s-chat'      => 'abab6.5s-chat',
            'abab5.5-chat'       => 'abab5.5-chat',
        ];
        return $map[$name] ?? 'abab6.5s-chat';
    }

    private function resolveGroqModelId(string $name): string
    {
        $map = [
            'Llama 3.3 70B Versatile' => 'llama-3.3-70b-versatile',
            'Llama 3.1 8B Instant'    => 'llama-3.1-8b-instant',
            'Qwen3 32B'               => 'qwen/qwen3-32b',
            'Grok Compound'           => 'groq/compound',
        ];
        return $map[$name] ?? 'llama-3.3-70b-versatile';
    }

    private function resolveOpenRouterModelId(string $name): string
    {
        $map = [
            'Llama 3.3 70B (Free)'   => 'meta-llama/llama-3.3-70b-instruct:free',
            'Llama 3.2 3B (Free)'    => 'meta-llama/llama-3.2-3b-instruct:free',
            'Gemma 3 27B (Free)'     => 'google/gemma-3-27b-it:free',
            'Gemma 3 12B (Free)'     => 'google/gemma-3-12b-it:free',
        ];
        return $map[$name] ?? 'meta-llama/llama-3.3-70b-instruct:free';
    }
}
