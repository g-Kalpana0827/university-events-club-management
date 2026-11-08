<?php
namespace App\Controllers;
class LeaderController {
    private function manager(int $clubId): array {
        $me=require_role('club_leader','admin');
        $club=row('SELECT * FROM clubs WHERE id=?',[$clubId]);
        if (!$club || ($me['role']!=='admin' && (int)$club['leader_user_id']!==(int)$me['id'])) {
            http_response_code(403);render('error',['pageTitle'=>'Access denied','errorMessage'=>'You can manage only your assigned club.']);exit;
        }
        return $club;
    }
   
    
      public function attendees(): void {
        $clubId=valid_int($_GET['club_id']??null);$this->manager($clubId);$id=valid_int($_GET['event_id']??null);
        $event=row('SELECT * FROM events WHERE id=? AND club_id=?',[$id,$clubId]);if(!$event) { flash('danger','Event not found.');go('leader',['club_id'=>$clubId]); }
        $registrations=rows("SELECT r.*,a.is_present,a.marked_at,u.full_name,u.student_number,u.faculty,u.email FROM event_registrations r JOIN users u ON u.id=r.user_id LEFT JOIN attendance a ON a.registration_id=r.id WHERE r.event_id=? ORDER BY FIELD(r.status,'registered','cancelled'),u.full_name",[$id]);
        render('attendees',compact('event','registrations','clubId')+['pageTitle'=>'Event attendance']);
    }
    public function attendance(): void {
        $clubId=valid_int($_POST['club_id']??null); $this->manager($clubId);
        $id=valid_int($_POST['event_id']??null); $rid=valid_int($_POST['registration_id']??null);
        $attend=post_str('attended');
        $message=transaction(function () use ($clubId,$id,$rid,$attend): string {
            $event=row('SELECT starts_at,status FROM events WHERE id=? AND club_id=? FOR UPDATE',[$id,$clubId]);
            if (!$event || !in_array($event['status'],['approved','completed'],true) || is_future($event['starts_at'])) {
                return 'Attendance can be recorded only after an approved event starts.';
            }
            $registration=row("SELECT id FROM event_registrations WHERE id=? AND event_id=? AND status='registered'",[$rid,$id]);
            if (!$registration) return 'Registration not found or cancelled.';
            if (!in_array($attend,['yes','no','clear'],true)) return 'Choose present, absent or clear.';
            if ($attend==='clear') {
                execute('DELETE FROM attendance WHERE registration_id=?',[$rid]);
                execute('UPDATE event_registrations SET attended_at=NULL WHERE id=?',[$rid]);
            } else {
                execute('INSERT INTO attendance(registration_id,is_present) VALUES(?,?) ON DUPLICATE KEY UPDATE is_present=VALUES(is_present),marked_at=NOW()',[$rid,$attend==='yes'?1:0]);
                execute('UPDATE event_registrations SET attended_at='.($attend==='yes'?'NOW()':'NULL').' WHERE id=?',[$rid]);
            }
            if ($attend!=='yes') execute('DELETE FROM event_feedback WHERE registration_id=? OR (event_id=? AND user_id=(SELECT user_id FROM event_registrations WHERE id=?))',[$rid,$id,$rid]);
            audit('attendance_updated','event',$id);
            return '';
        });
        flash($message?'warning':'success',$message?:'Attendance updated.');
        go('leader/attendees',['club_id'=>$clubId,'event_id'=>$id]);
    }
}