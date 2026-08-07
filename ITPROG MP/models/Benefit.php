<?php
class Benefit
{
    public static function active(PDO $db, ?string $category = null): array
    {
        if ($category && $category !== 'All') {
            $stmt = $db->prepare("SELECT * FROM benefits WHERE active=1 AND category=? ORDER BY benefit_name");
            $stmt->execute([$category]);
            return $stmt->fetchAll();
        }
        return $db->query("SELECT * FROM benefits WHERE active=1 ORDER BY category, benefit_name")->fetchAll();
    }

    public static function find(PDO $db, int $id): ?array
    {
        $stmt = $db->prepare("SELECT * FROM benefits WHERE benefit_id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
