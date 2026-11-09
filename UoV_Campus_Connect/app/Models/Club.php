<?php
namespace App\Models;
final class Club {
    public static function featured(int $limit=6): array {
        $limit=max(1,min(30,$limit));
        return \rows("SELECT c.*, (SELECT COUNT(*) FROM club_memberships m WHERE m.club_id=c.id AND m.status='approved') AS members FROM clubs c WHERE c.status='approved' ORDER BY c.id LIMIT ".$limit);
    }
}
