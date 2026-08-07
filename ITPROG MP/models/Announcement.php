<?php
class Announcement
{
    public static function all(PDO $db, ?string $category = null): array
    {
        if ($category && $category !== 'All') {
            $stmt = $db->prepare("SELECT a.*, u.email AS admin_email 
                                  FROM announcements a 
                                  JOIN users u ON u.user_id=a.admin_id 
                                  WHERE a.category=? 
                                  ORDER BY COALESCE(a.event_date,'9999-12-31'), a.created_at DESC");

            $stmt->execute([$category]);
            return $stmt->fetchAll();
        }
        return $db->query("SELECT a.*, u.email AS admin_email 
                           FROM announcements a 
                           JOIN users u ON u.user_id=a.admin_id 
                           ORDER BY COALESCE(a.event_date,'9999-12-31'), a.created_at DESC")->fetchAll();
    }
}
