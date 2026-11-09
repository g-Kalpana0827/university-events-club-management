<?php
/** Structural checks that do not require MySQL. They do not replace integration testing. */
$root = dirname(__DIR__);
$schema = file_get_contents($root.'/database/schema.sql');
$router = file_get_contents($root.'/public/index.php');
$requiredTables=['users','clubs','club_memberships','event_categories','venues','events',
    'event_registrations','attendance','event_feedback','announcements'];
$routes=['home','register','login','events','event','clubs','club','dashboard','profile',
    'leader','leader/members','leader/event/form','leader/attendees',
    'admin','admin/records','admin/reports','leader/reports'];
$tests=[];
foreach($requiredTables as $name) $tests['table '.$name]=str_contains($schema,'CREATE TABLE IF NOT EXISTS '.$name.' ')
    || str_contains($schema,'CREATE TABLE IF NOT EXISTS '.$name." (");
foreach($routes as $name) $tests['route '.$name]=str_contains($router,"'".$name."'=>");
$tests['event deadline']=str_contains($schema,'registration_deadline');
$tests['review note']=str_contains($schema,'review_note');
$tests['club contact email']=str_contains($schema,'contact_email');
$tests['announcements visibility']=str_contains($schema,"ENUM('published','hidden')");
$tests['separate attendance']=str_contains($schema,'registration_id INT UNSIGNED NOT NULL UNIQUE');
$tests['new admin records view']=is_file($root.'/app/Views/pages/admin_records.php');
$tests['new reports view']=is_file($root.'/app/Views/pages/reports.php');
$fail=0;
foreach($tests as $name=>$ok){echo ($ok?'PASS':'FAIL').' '.$name.PHP_EOL;if(!$ok)$fail++;}
echo sprintf("%d/%d static proposal checks passed (not DB-integrated tests)%s",count($tests)-$fail,count($tests),PHP_EOL);
exit($fail?1:0);
