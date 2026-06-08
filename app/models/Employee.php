<?php

class Employee extends Model
{
    public function findAll(int $page = 1, int $limit = 20, string $search = ''): array
    {
        $params = [$this->tenantId];
        $where = 'WHERE e.tenant_id = ? AND e.is_active = true';

        if ($search) {
            $where .= ' AND (e.name LIKE ? OR e.employee_code LIKE ? OR e.email LIKE ?)';
            $searchTerm = "%{$search}%";
            array_push($params, $searchTerm, $searchTerm, $searchTerm);
        }

        $sql = "SELECT e.* FROM employees e {$where} ORDER BY e.name";

        return $this->paginate($sql, $params, $page, $limit);
    }

    public function findById(string $id): ?array
    {
        return $this->queryOne(
            'SELECT * FROM employees WHERE id = ? AND tenant_id = ?',
            [$id, $this->tenantId]
        );
    }

    public function findByCode(string $code): ?array
    {
        return $this->queryOne(
            'SELECT * FROM employees WHERE employee_code = ? AND tenant_id = ?',
            [$code, $this->tenantId]
        );
    }

    public function create(array $data): string
    {
        $data['tenant_id'] = $this->tenantId;
        $data['ktp_number'] = Security::encrypt($data['ktp_number']);
        $data['npwp_number'] = Security::encrypt($data['npwp_number']);
        $data['created_at'] = $this->now();
        $data['updated_at'] = $this->now();

        return $this->insert('employees', $data);
    }

    public function edit(string $id, array $data): int
    {
        if (isset($data['ktp_number'])) {
            $data['ktp_number'] = Security::encrypt($data['ktp_number']);
        }
        if (isset($data['npwp_number'])) {
            $data['npwp_number'] = Security::encrypt($data['npwp_number']);
        }

        return $this->update('employees', $data, 'id = ? AND tenant_id = ?', [$id, $this->tenantId]);
    }

    public function deactivate(string $id): int
    {
        return $this->update('employees', ['is_active' => false], 'id = ? AND tenant_id = ?', [$id, $this->tenantId]);
    }

    public function importBatch(array $rows): array
    {
        $imported = 0;
        $errors = [];

        foreach ($rows as $i => $row) {
            try {
                if (!empty($row['employee_code']) && $this->findByCode($row['employee_code'])) {
                    $errors[] = "Baris " . ($i + 1) . ": Kode karyawan '{$row['employee_code']}' sudah ada";
                    continue;
                }
                $this->create($row);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Baris " . ($i + 1) . ": " . $e->getMessage();
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
    }
}
