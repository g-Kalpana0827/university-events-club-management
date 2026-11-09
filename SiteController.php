<?php
$registration = $viewer ? row('SELECT r.status, r.attended_at, a.is_present FROM event_registrations r LEFT JOIN attendance a ON a.registration_id = r.id WHERE r.user_id = ? AND r.event_id = ?', [$viewer['id'], $id]) : null;


$myFeedback = $viewer ? row('SELECT id, rating, comment FROM event_feedback WHERE user_id = ? AND event_id = ?', [user()['id'], $id]) : null;
$feedback = rows('SELECT f.rating, f.comment, f.created_at, u.full_name FROM event_feedback f JOIN users u ON u.id = f.user_id WHERE f.event_id = ? ORDER BY f.created_at DESC LIMIT 15', [$id]);


$memberships = rows("SELECT m.status, m.joined_at, c.id, c.name, c.category FROM club_memberships m JOIN clubs c ON c.id = m.club_id WHERE m.user_id = ? ORDER BY m.joined_at DESC", [$me['id']]);
$registrations = rows("SELECT r.status, r.attended_at, e.id, e.title, e.starts_at, e.venue, e.status AS event_status, c.name AS club_name FROM event_registrations r JOIN events e ON e.id = r.event_id JOIN clubs c ON c.id = e.club_id WHERE r.user_id = ? ORDER BY e.starts_at DESC", [$me['id']]);
$notifications = rows('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5', [$me['id']]);

?>