<?php $viewer = user(); $pageTitle = $pageTitle ?? 'Campus life'; $flashes=$_SESSION['flash']??[];unset($_SESSION['flash']);
$unread = $viewer ? scalar('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0',[$viewer['id']]) : 0;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="A student-developed university events and clubs management system demonstration.">
<title><?= e($pageTitle) ?> | Vanni Campus Connect</title>
<link rel="icon" type="image/svg+xml" href="<?= e(asset('mark.svg')) ?>">
<link rel="stylesheet" href="<?= e(asset('app.css')) ?>">
<script src="<?= e(asset('app.js')) ?>" defer></script>
</head>
<body>
<div class="university-ribbon"><div class="container ribbon-inner"><span>University of Vavuniya · Student software project</span><a href="https://www.vau.ac.lk/" target="_blank" rel="noopener noreferrer">Official university website <span aria-hidden="true">↗</span></a></div></div>
<header class="site-header"><div class="container nav-shell">
 <a class="brand" href="<?= e(url('home')) ?>" aria-label="Vanni Campus Connect home">
   <img src="<?= e(asset('mark.svg')) ?>" width="42" height="42" alt=""><span class="brand-text"><strong>Vanni Campus Connect</strong><small>Events & clubs · Student project</small></span>
 </a>
 <button type="button" class="menu-toggle" aria-controls="primary-nav" aria-expanded="false" aria-label="Toggle navigation">☰</button>
 <nav id="primary-nav" class="primary-nav" aria-label="Primary navigation">
  <a href="<?= e(url('home')) ?>" class="<?= (($_GET['r']??'home')==='home'?'active':'') ?>">Home</a>
  <a href="<?= e(url('events')) ?>">Events</a><a href="<?= e(url('clubs')) ?>">Clubs & societies</a><a href="<?= e(url('calendar')) ?>">Calendar</a>
  <?php if ($viewer): ?><a href="<?= e(url('dashboard')) ?>">Dashboard</a>
  <?php if(in_array($viewer['role'],['club_leader','admin'],true)): ?><a href="<?= e(url('leader')) ?>">Club studio</a><?php endif; ?>
  <?php if($viewer['role']==='admin'): ?><a href="<?= e(url('admin')) ?>">Admin</a><?php endif; ?>
  <a class="nav-notice" href="<?= e(url('notifications')) ?>" aria-label="Notifications">Notices<?php if($unread): ?><span class="count-bubble"><?= $unread > 99 ? '99+' : $unread ?></span><?php endif;?></a>
  <a class="nav-user" href="<?= e(url('profile')) ?>"><?= e(explode(' ',$viewer['full_name'])[0]) ?></a>
  <form action="<?= e(url('logout')) ?>" method="post" class="inline-form"><?= csrf_field() ?><button class="text-button" type="submit">Sign out</button></form>
  <?php else: ?><a href="<?= e(url('login')) ?>">Sign in</a><a href="<?= e(url('register')) ?>" class="btn btn-sm btn-primary">Join platform</a><?php endif; ?>
 </nav>
 </div></header>
<main id="main-content">
 <?php if($flashes): ?><div class="container flash-stack" role="status" aria-live="polite"><?php foreach($flashes as $f): ?><div class="flash flash-<?= e($f['kind']) ?>"><span><?= e($f['message']) ?></span><button type="button" class="flash-close" aria-label="Dismiss notice">×</button></div><?php endforeach; ?></div><?php endif; ?>
 <?php require $viewFile; ?>
</main>
<footer class="site-footer"><div class="container footer-main">
 <div><div class="footer-brand"><img src="<?= e(asset('mark.svg')) ?>" width="34" height="34" alt=""><strong>Vanni Campus Connect</strong></div><p>A student-developed demonstration for managing campus events, societies, and community participation.</p></div>
 <div><strong>Explore</strong><a href="<?= e(url('events')) ?>">Campus events</a><a href="<?= e(url('clubs')) ?>">Clubs & societies</a><a href="<?= e(url('calendar')) ?>">Event calendar</a></div>
 <div><strong>Information</strong><a href="<?= e(url('about')) ?>">About this project</a><a href="https://www.vau.ac.lk/societies-and-clubs/" target="_blank" rel="noopener noreferrer">Official club directory ↗</a><a href="https://www.vau.ac.lk/" target="_blank" rel="noopener noreferrer">University of Vavuniya ↗</a></div>
</div><div class="container footer-bottom"><span>© <?= date('Y') ?> Vanni Campus Connect · Educational demonstration</span><span>Independent student project · Not an official UoV service · Sri Lanka Standard Time</span></div></footer>
</body></html>
