<section class="page-banner">
<div class="container">
<a class="back-link" href="<?= e(url('leader',['club_id'=>$clubId])) ?>">← Club studio</a>
<div class="section-label">ATTENDANCE & PARTICIPATION</div>
<h1><?= e($event['title']) ?></h1>
<p>Mark registered participants present after the event starts.</p></div></section>

<section class="section container">
    <div class="card panel"><div class="table-scroll">
        <table class="data-table"><thead><tr><th>Student</th>
        <th>Student number</th>
        <th>Faculty</th>
        <th>Registration</th>
        <th>Attendance</th>
        <th>Action</th></tr></thead>
        <tbody><?php foreach($registrations as $r): ?>
        <tr><td><strong><?= e($r['full_name']) ?></strong><br>

        <small><?= e($r['email']) ?></small></td><td><?= e($r['student_number']) ?></td><td>
            <?= e($r['faculty']) ?></td><td><?= status_pill($r['status']) ?></td><td>
    <?= $r['is_present']===null?'Not marked':((int)$r['is_present']===1?'<span class="success-note">Present</span>':'Absent') ?></td>
    <td><?php if($r['status']==='registered' && in_array($event['status'],['approved','completed'],true) && strtotime($event['starts_at'])<=time()): ?>
    </td>