<?php
namespace App\Models;
final class Event {
    /** Public events coming up, including a transaction-independent registered count. */
    public static function featured(int $limit=6): array {
        $limit=max(1,min(30,$limit));
        return \rows("SELECT e.*, c.name AS club_name, c.category AS club_category, (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id=e.id AND r.status='registered') AS booked FROM events e JOIN clubs c ON c.id=e.club_id WHERE e.status='approved' AND c.status='approved' AND e.starts_at>=NOW() ORDER BY e.starts_at LIMIT ".$limit);
    }
}
