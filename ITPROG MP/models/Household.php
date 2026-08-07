<?php
class Household
{
    public static function members(PDO $db, int $householdId): array
    {
        $stmt = $db->prepare("SELECT r.*, u.email 
                              FROM residents r 
                              JOIN users u ON u.user_id=r.user_id 
                              WHERE r.household_id=? 
                              ORDER BY r.last_name,r.first_name");
        $stmt->execute([$householdId]);
        return $stmt->fetchAll();
    }
}
