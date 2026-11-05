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

}