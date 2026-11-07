<?php
namespace App\Core;

use PDO;

/** CLI setup/upgrade only. MySQL DDL commits independently; every step is repeatable. */
final class Schema {
    public static function apply(PDO $pdo): void {
        $source = file_get_contents(dirname(__DIR__,2).'/database/schema.sql');
        $source = preg_replace('/^--.*$/m', '', $source);
        foreach (preg_split('/;\s*(?:\r?\n|$)/', $source) as $sql) {
            $sql = trim($sql);
            if ($sql === '' || preg_match('/^(CREATE DATABASE|USE)\b/i',$sql)) continue;
            $pdo->exec($sql);
        }
        // Upgrade the earlier seven-/nine-table edition without dropping rows.
        $columns = [
            'clubs'=>['contact_email'=>'VARCHAR(190) NULL'],
            'events'=>[
                'category_id'=>'INT UNSIGNED NULL', 'venue_id'=>'INT UNSIGNED NULL',
                'registration_deadline'=>'DATETIME NULL', 'review_note'=>'TEXT NULL'
            ],
            'announcements'=>[
                'status'=>"ENUM('published','hidden') NOT NULL DEFAULT 'published'",
                'updated_at'=>'TIMESTAMP NULL DEFAULT NULL'
            ],
            'event_feedback'=>['registration_id'=>'INT UNSIGNED NULL UNIQUE']
        ];
        foreach ($columns as $table=>$fields) {
            foreach ($fields as $column=>$definition) {
                $st = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=?');
                $st->execute([$table,$column]);
                if (!(int)$st->fetchColumn()) $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
            }
        }
        $st=$pdo->query("SELECT COLUMN_TYPE FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='events' AND column_name='status'");
        if (!str_contains((string)$st->fetchColumn(), "'completed'")) {
            $pdo->exec("ALTER TABLE events MODIFY COLUMN status ENUM('draft','pending','approved','rejected','cancelled','completed') NOT NULL DEFAULT 'pending'");
        }
        $pdo->exec("INSERT IGNORE INTO event_categories(name,description) VALUES
            ('Workshop','Practical learning sessions'),('Seminar','Academic presentations'),
            ('Competition','Student competitions'),('Sports','Sporting events'),
            ('Cultural','Cultural and artistic activities'),('Community','Community events'),('General','Other campus activities')");
        $pdo->exec("INSERT INTO venues(name,location,capacity)
            SELECT venue,'University of Vavuniya',GREATEST(1,MAX(capacity)) FROM events
            WHERE venue_id IS NULL AND TRIM(venue)<>'' GROUP BY venue
            ON DUPLICATE KEY UPDATE capacity=GREATEST(venues.capacity,VALUES(capacity))");
        $pdo->exec('UPDATE events e JOIN venues v ON v.name=e.venue SET e.venue_id=v.id WHERE e.venue_id IS NULL');
        $pdo->exec("UPDATE events SET category_id=(SELECT id FROM event_categories WHERE name='General' LIMIT 1) WHERE category_id IS NULL");
        $pdo->exec('UPDATE events SET registration_deadline=starts_at WHERE registration_deadline IS NULL');
        $pdo->exec('INSERT IGNORE INTO attendance(registration_id,is_present,marked_at) SELECT id,1,attended_at FROM event_registrations WHERE attended_at IS NOT NULL');
        $pdo->exec('UPDATE event_feedback f JOIN event_registrations r ON r.event_id=f.event_id AND r.user_id=f.user_id SET f.registration_id=r.id WHERE f.registration_id IS NULL');
        foreach ([
            ['events','category_id','event_categories'],
            ['events','venue_id','venues'],
            ['event_feedback','registration_id','event_registrations']
        ] as [$table,$column,$parent]) {
            $st=$pdo->prepare('SELECT COUNT(*) FROM information_schema.key_column_usage WHERE table_schema=DATABASE() AND table_name=? AND column_name=? AND referenced_table_name=?');
            $st->execute([$table,$column,$parent]);
            if (!(int)$st->fetchColumn()) {
                $name='fk_setup_'.$table.'_'.$column;
                $delete=$table==='event_feedback'?'CASCADE':'RESTRICT';
                $pdo->exec("ALTER TABLE `$table` ADD CONSTRAINT `$name` FOREIGN KEY (`$column`) REFERENCES `$parent`(id) ON DELETE $delete");
            }
        }
    }
}
