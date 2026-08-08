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

    public static function allWithRules(PDO $db): array
    {
        return $db->query("
            SELECT b.*,
                er.rule_id, er.minimum_age, er.maximum_age, er.required_sector,
                er.required_income, er.household_limit, er.required_location,
                er.requires_disaster_affected,
                (SELECT COUNT(*) FROM benefit_requirements br WHERE br.benefit_id = b.benefit_id) AS requirement_count
            FROM benefits b
            LEFT JOIN eligibility_rules er ON er.benefit_id = b.benefit_id
            ORDER BY b.category, b.benefit_name
        ")->fetchAll();
    }
}
