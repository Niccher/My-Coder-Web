<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSettingModel extends Model
{
    protected $table      = 'user_settings';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'model_slot', 'model_name', 'api_key', 'provider'];

    /**
     * Get all model slots for a user (slots 1–3 = regular models, slot 4 = master)
     */
    public function getForUser(int $userId): array
    {
        $rows = $this->where('user_id', $userId)->orderBy('model_slot', 'ASC')->findAll();
        $settings = [];
        $encrypter = clone \Config\Services::encrypter(); // safe copy

        foreach ($rows as $row) {
            // Attempt to decrypt the API key
            if (!empty($row['api_key'])) {
                try {
                    // We base64 encode/decode ciphertext to store safely in varchar/text
                    $ciphertext = base64_decode($row['api_key']);
                    if ($ciphertext !== false) {
                        $decrypted = $encrypter->decrypt($ciphertext);
                        $row['api_key'] = $decrypted;
                    }
                } catch (\Exception $e) {
                    // Fallback: This key might be from before encryption was enabled
                    // so we leave it as plain text.
                }
            }
            $settings[$row['model_slot']] = $row;
        }
        return $settings;
    }

    /**
     * Upsert a model slot for a user
     */
    public function saveSlot(int $userId, int $slot, array $data): void
    {
        if (isset($data['api_key']) && $data['api_key'] !== '') {
            $encrypter = \Config\Services::encrypter();
            // Store as base64 to avoid binary truncation issues in DB
            $data['api_key'] = base64_encode($encrypter->encrypt($data['api_key']));
        }

        $existing = $this->where('user_id', $userId)->where('model_slot', $slot)->first();
        $data['user_id']    = $userId;
        $data['model_slot'] = $slot;

        if ($existing) {
            $this->update($existing['id'], $data);
        } else {
            $this->insert($data);
        }
    }
}
