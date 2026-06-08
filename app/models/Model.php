<?php

require_once __DIR__ . '/../../config/database.php';

abstract class Model
{
    protected PDO $db;
    protected string $tenantId;

    public function __construct(string $tenantId)
    {
        $this->db = Database::getInstance();
        $this->tenantId = $tenantId;
    }

    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    protected function insert(string $table, array $data): string
    {
        $id = $this->uuid();
        $data['id'] = $id;

        if (!isset($data['tenant_id'])) {
            $data['tenant_id'] = $this->tenantId;
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));

        return $id;
    }

    protected function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $sets = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));

        $sql = "UPDATE {$table} SET {$sets} WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    protected function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    protected function paginate(string $sql, array $params, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as sub";

        $total = (int) $this->queryOne($countSql, $params)['total'];

        $paginatedSql = "{$sql} LIMIT {$limit} OFFSET {$offset}";
        $data = $this->query($paginatedSql, $params);

        return [
            'data' => $data,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) ceil($total / $limit),
            ],
        ];
    }

    protected function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    protected function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
