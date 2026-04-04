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
        foreach ($rows as $row) {
            $settings[$row['model_slot']] = $row;
        }
        return $settings;
    }

    /**
     * Upsert a model slot for a user
     */
    public function saveSlot(int $userId, int $slot, array $data): void
    {
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
