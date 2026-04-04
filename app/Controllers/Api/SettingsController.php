<?php

/**
 * SettingsController — Save and retrieve per-user model configurations.
 *
 * Slots 1–3 are standard AI models. Slot 4 is the Master Model (evaluator).
 *
 * Supported providers: gemini | deepseek | grok | openai
 */

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserSettingModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class SettingsController extends BaseController
{
    use ResponseTrait;

    /**
     * GET /api/settings
     * Returns the current user's model config for all 4 slots.
     * API keys are masked (only first/last 4 chars visible).
     */
    public function index(): ResponseInterface
    {
        $userId = auth()->id();
        $model  = new UserSettingModel();
        $raw    = $model->getForUser($userId);

        $result = [];
        foreach ([1, 2, 3, 4] as $slot) {
            $row = $raw[$slot] ?? [];
            $result[$slot] = [
                'model_slot' => $slot,
                'model_name' => $row['model_name'] ?? '',
                'provider'   => $row['provider'] ?? '',
                'api_key'    => !empty($row['api_key']) ? $this->maskKey($row['api_key']) : '',
                'has_key'    => !empty($row['api_key']),
            ];
        }

        return $this->respond($result);
    }

    /**
     * POST /api/settings
     *
     * Body (JSON):
     *   slots: [
     *     { slot: 1, model_name: "Gemini 1.5 Flash", provider: "gemini", api_key: "AIza..." },
     *     { slot: 2, model_name: "DeepSeek R1",      provider: "deepseek", api_key: "sk-..." },
     *     { slot: 3, model_name: "Grok-2",           provider: "grok",     api_key: "xai-..." },
     *     { slot: 4, model_name: "GPT-4o",           provider: "openai",   api_key: "sk-..." },
     *   ]
     *
     * If api_key is empty or starts with "****", the existing key is preserved.
     */
    public function save(): ResponseInterface
    {
        $userId = auth()->id();
        $body   = $this->request->getJSON(true);
        $slots  = $body['slots'] ?? [];

        if (empty($slots) || !is_array($slots)) {
            return $this->fail('No slots provided.', 422);
        }

        $model   = new UserSettingModel();
        $current = $model->getForUser($userId);

        foreach ($slots as $slot) {
            $slotNum    = (int) ($slot['slot'] ?? 0);
            $modelName  = trim($slot['model_name'] ?? '');
            $provider   = trim($slot['provider'] ?? '');
            $apiKey     = trim($slot['api_key'] ?? '');

            if ($slotNum < 1 || $slotNum > 4) {
                continue;
            }

            $data = [
                'model_name' => $modelName,
                'provider'   => $provider,
            ];

            // Only update key if a new one was provided (not masked placeholder)
            if ($apiKey !== '' && !str_starts_with($apiKey, '****')) {
                $data['api_key'] = $apiKey;
            } elseif (isset($current[$slotNum]['api_key'])) {
                $data['api_key'] = $current[$slotNum]['api_key'];
            }

            $model->saveSlot($userId, $slotNum, $data);
        }

        return $this->respond(['message' => 'Settings saved successfully.']);
    }

    private function maskKey(string $key): string
    {
        $len = strlen($key);
        if ($len <= 8) {
            return '****';
        }
        return substr($key, 0, 4) . str_repeat('*', min($len - 8, 20)) . substr($key, -4);
    }
}
