<?php

class ClientModel extends Model
{
    public function findAll(): array
    {
        return $this->query(
            'SELECT * FROM clients WHERE tenant_id = ? AND is_active = true ORDER BY name',
            [$this->tenantId]
        );
    }

    public function findById(string $id): ?array
    {
        return $this->queryOne(
            'SELECT * FROM clients WHERE id = ? AND tenant_id = ?',
            [$id, $this->tenantId]
        );
    }

    public function create(array $data): string
    {
        $data['tenant_id'] = $this->tenantId;
        $data['created_at'] = $this->now();
        $data['updated_at'] = $this->now();
        return $this->insert('clients', $data);
    }

    public function edit(string $id, array $data): int
    {
        return $this->update('clients', $data, 'id = ? AND tenant_id = ?', [$id, $this->tenantId]);
    }

    public function remove(string $id): int
    {
        return $this->update('clients', ['is_active' => false], 'id = ? AND tenant_id = ?', [$id, $this->tenantId]);
    }
}
