<?php
class Application
{
    public static function forResident(PDO $db, int $residentId): array
    {
        $sql = "SELECT a.*, b.benefit_name, b.category,
                       CONCAT(r.first_name,' ',r.last_name) AS beneficiary_name
                FROM applications a
                JOIN benefits b ON b.benefit_id=a.benefit_id
                JOIN residents r ON r.resident_id=a.resident_id
                WHERE a.resident_id=? OR COALESCE(a.submitted_by_resident_id,a.resident_id)=?
                ORDER BY a.application_date DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$residentId, $residentId]);
        return $stmt->fetchAll();
    }
}
