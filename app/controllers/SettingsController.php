<?php

class SettingsController
{
    private string $tenantId;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function get(): void
    {
        $model = new class($this->tenantId) extends Model {};
        $settings = $model->query(
            'SELECT setting_key, setting_value FROM tenant_settings WHERE tenant_id = ?',
            [$this->tenantId]
        );

        $result = [];
        foreach ($settings as $s) {
            $result[$s['setting_key']] = $s['setting_value'];
        }

        Response::success($result);
    }

    public function update(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $model = new class($this->tenantId) extends Model {};

        foreach ($input as $key => $value) {
            $existing = $model->queryOne(
                'SELECT id FROM tenant_settings WHERE tenant_id = ? AND setting_key = ?',
                [$this->tenantId, $key]
            );

            if ($existing) {
                $model->update('tenant_settings', ['setting_value' => $value], 'id = ?', [$existing['id']]);
            } else {
                $model->insert('tenant_settings', [
                    'tenant_id' => $this->tenantId,
                    'setting_key' => $key,
                    'setting_value' => $value,
                ]);
            }
        }

        Response::success(['updated' => true]);
    }
}
