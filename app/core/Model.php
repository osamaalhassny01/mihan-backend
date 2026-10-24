<?php
/**
 * Base Model - thin query-builder style helpers on top of PDO.
 * Concrete models extend this and set $table.
 */
abstract class Model
{
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function all(?string $orderBy = null): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) $sql .= " ORDER BY $orderBy";
        return $this->db->query($sql)->fetchAll();
    }

    public function where(array $conditions, ?string $orderBy = null, ?int $limit = null): array
    {
        [$where, $params] = $this->buildWhere($conditions);
        $sql = "SELECT * FROM {$this->table} WHERE $where";
        if ($orderBy) $sql .= " ORDER BY $orderBy";
        if ($limit) $sql .= " LIMIT $limit";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function first(array $conditions)
    {
        $rows = $this->where($conditions, null, 1);
        return $rows[0] ?? null;
    }

    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            return (int) $this->db->query("SELECT COUNT(*) c FROM {$this->table}")->fetch()['c'];
        }
        [$where, $params] = $this->buildWhere($conditions);
        $stmt = $this->db->prepare("SELECT COUNT(*) c FROM {$this->table} WHERE $where");
        $stmt->execute($params);
        return (int) $stmt->fetch()['c'];
    }

    public function insert(array $data)
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => ":$c", $columns);
        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    public function update($id, array $data): bool
    {
        $set = implode(',', array_map(fn($c) => "$c = :$c", array_keys($data)));
        $data[$this->primaryKey] = $id;
        $stmt = $this->db->prepare("UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = :{$this->primaryKey}");
        return $stmt->execute($data);
    }

    public function delete($id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    protected function buildWhere(array $conditions): array
    {
        $parts = [];
        $params = [];
        foreach ($conditions as $col => $val) {
            $key = str_replace('.', '_', $col);
            $parts[] = "$col = :$key";
            $params[$key] = $val;
        }
        return [implode(' AND ', $parts), $params];
    }
}
