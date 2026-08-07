<?php
class Document
{
    public static function forResident(PDO $db, int $residentId): array
    {
        $stmt = $db->prepare("SELECT * FROM documents 
                              WHERE resident_id=? 
                              ORDER BY upload_date DESC");
        $stmt->execute([$residentId]);
        return $stmt->fetchAll();
    }
}
