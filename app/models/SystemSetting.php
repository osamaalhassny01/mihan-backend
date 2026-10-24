<?php
require_once __DIR__ . '/../core/Model.php';

class SystemSetting extends Model
{
    protected string $table = 'system_settings';

    public function allKeyed(): array
    {
        $rows = $this->all('`key` ASC');
        $out = [];
        foreach ($rows as $row) $out[$row['key']] = $row['value'];
        return $out;
    }

    public function get(string $key, $default = null)
    {
        $row = $this->first(['key' => $key]);
        return $row ? $row['value'] : $default;
    }

    public function set(string $key, $value): void
    {
        $stmt = $this->db->prepare("INSERT INTO system_settings (`key`,`value`) VALUES (:k,:v) ON DUPLICATE KEY UPDATE `value`=:v2");
        $stmt->execute(['k' => $key, 'v' => (string)$value, 'v2' => (string)$value]);
    }

    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) $this->set($key, $value);
    }
}
