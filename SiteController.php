<?php


$registration = $viewer ? row('SELECT r.status, r.attended_at, a.is_present FROM event_registrations r LEFT JOIN attendance a ON a.registration_id = r.id WHERE r.user_id = ? AND r.event_id = ?', [$viewer['id'], $id]) : null;

$myFeedback = $viewer ? row('SELECT id, rating, comment FROM event_feedback WHERE user_id = ? AND event_id = ?', [user()['id'], $id]) : null;

$feedback = rows('SELECT f.rating, f.comment, f.created_at, u.full_name FROM event_feedback f JOIN users u ON u.id = f.user_id WHERE f.event_id = ? ORDER BY f.created_at DESC LIMIT 15', [$id]);

?>
