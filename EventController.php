<?php
namespace App\Controllers;

class EventController {
    public function register(): void {
        $me = require_role('student', 'club_leader');
        $id = valid_int($_POST['event_id'] ?? null);
        if (!$id) { flash('danger', 'Invalid event.'); go('events'); }
        try {
            transaction(function () use ($me, $id): void {
                $event = row('SELECT e.*,c.status AS club_status FROM events e JOIN clubs c ON c.id=e.club_id WHERE e.id=? FOR UPDATE', [$id]);
                if (!$event || $event['status'] !== 'approved' || $event['club_status'] !== 'approved'
                    || !is_future($event['starts_at'])
                    || ($event['registration_deadline'] && !is_future($event['registration_deadline']))) {
                    throw new \DomainException('Registration is closed for this event.');
                }
                $prior = row('SELECT id,status FROM event_registrations WHERE event_id=? AND user_id=?', [$id,$me['id']]);
                if ($prior && $prior['status'] === 'registered') throw new \DomainException('You are already registered.');
                $booked = scalar("SELECT COUNT(*) FROM event_registrations WHERE event_id=? AND status='registered'", [$id]);
                if ($booked >= (int)$event['capacity']) throw new \DomainException('This event is fully booked.');
                if ($prior) {
                    execute('DELETE FROM attendance WHERE registration_id=?', [$prior['id']]);
                    execute('DELETE FROM event_feedback WHERE registration_id=?', [$prior['id']]);
                    execute("UPDATE event_registrations SET status='registered',attended_at=NULL,registered_at=NOW() WHERE id=?", [$prior['id']]);
                } else {
                    execute("INSERT INTO event_registrations(event_id,user_id,status) VALUES(?,?,'registered')", [$id,$me['id']]);
                }
                notify((int)$me['id'], 'Event registration confirmed', 'You are registered for '.$event['title'], 'event&id='.$id);
                audit('event_registered', 'event', $id);
            });
            flash('success', 'You are registered! See your dashboard for details.');
        } catch (\DomainException $error) {
            flash('warning', $error->getMessage());
        } catch (\Throwable $error) {
            error_log('[registration] '.$error);
            flash('danger', 'Registration failed. Please try again.');
        }
        go('event', ['id'=>$id]);
    }
    public function cancel(): void {
        $me = require_role('student', 'club_leader');
        $id = valid_int($_POST['event_id'] ?? null);
        $changed = transaction(function () use ($me, $id): bool {
            $event = row('SELECT starts_at,registration_deadline,status FROM events WHERE id=? FOR UPDATE', [$id]);
            if (!$event || $event['status'] !== 'approved' || !is_future($event['starts_at'])
                || ($event['registration_deadline'] && !is_future($event['registration_deadline']))) return false;
            $registration = row("SELECT id FROM event_registrations WHERE event_id=? AND user_id=? AND status='registered'", [$id,$me['id']]);
            if (!$registration) return false;
            execute('DELETE FROM attendance WHERE registration_id=?', [$registration['id']]);
            execute('DELETE FROM event_feedback WHERE registration_id=?', [$registration['id']]);
            execute("UPDATE event_registrations SET status='cancelled',attended_at=NULL WHERE id=?", [$registration['id']]);
            audit('registration_cancelled', 'event', $id);
            return true;
        });
        flash($changed?'success':'warning', $changed?'Your registration was cancelled.':'Cancellation is unavailable: registration has closed or you have no active booking.');
        go('event', ['id'=>$id]);
    }

    public function feedback(): void {
        $me = require_role('student', 'club_leader');
        $id = valid_int($_POST['event_id'] ?? null);
        $rating = valid_int($_POST['rating'] ?? null);
        $comment = post_str('comment');
        $saved = transaction(function () use ($me,$id,$rating,$comment): bool {
            // Attendance changes lock this same event row before changing eligibility.
            $event = row('SELECT status,ends_at FROM events WHERE id=? FOR UPDATE', [$id]);
            $registration = row("SELECT r.id,a.is_present FROM event_registrations r LEFT JOIN attendance a ON a.registration_id=r.id WHERE r.event_id=? AND r.user_id=? AND r.status='registered'", [$id,$me['id']]);
            if (!$event || !$registration || !(int)$registration['is_present'] || $event['status'] !== 'completed'
                || is_future($event['ends_at']) || $rating < 1 || $rating > 5 || text_length($comment) > 1500) return false;
            execute('INSERT INTO event_feedback(event_id,user_id,registration_id,rating,comment) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE registration_id=VALUES(registration_id),rating=VALUES(rating),comment=VALUES(comment),created_at=NOW()', [$id,$me['id'],$registration['id'],$rating,$comment?:null]);
            audit('event_feedback', 'event', $id);
            return true;
        });
        flash($saved?'success':'danger', $saved?'Thank you for sharing your feedback.':'Feedback requires a completed event, attendance marked present, a rating of 1–5 and a comment of at most 1,500 characters.');
        go('event', ['id'=>$id]);
    }
}


