 <section class="page-banner">
    <div class="container">
        <div class="section-label">CLUB STUDIO</div>
        <h1>Manage your community</h1>
        <p>Membership requests, event planning, and club announcements in one workspace.</p></div>
    </section>

    
    <section class="section container">
<?php if(!$club): ?>
    <div class="empty-state">
        <h2>No clubs assigned</h2>
        <p>An administrator must assign an approved club to your account before you can manage it.</p>
        <a href="<?= e(url('dashboard')) ?>" class="btn btn-primary">My dashboard</a>
    </div>
<?php else: ?>
    <div class="management-top">
        <form method="get" action="<?= e(base_path().'/index.php') ?>">
            <input type="hidden" name="r" value="leader">
            <label>Managing club 
                <select name="club_id" data-auto-submit><?php foreach($clubs as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id']===(int)$club['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
                 </select>
            </label>
        </form>
            <div class="button-row">
                <a class="btn btn-outline" href="<?= e(url('leader/reports',['club_id'=>$club['id']])) ?>">Reports</a>
                <a class="btn btn-outline" href="<?= e(url('leader/club/form',['club_id'=>$club['id']])) ?>">Edit description</a>
                <a class="btn btn-primary" href="<?= e(url('leader/event/form',['club_id'=>$club['id']])) ?>">+ Create event</a>
            </div>
        </div>

<div class="quick-stats">
    <div class="stat-card"><span>Pending members</span>
    <strong><?= count($pending) ?></strong>
    <small>Awaiting review</small>
</div>
<div class="stat-card"><span>Club events</span><strong><?= count($events) ?></strong><small>Recent and upcoming</small></div>
<div class="stat-card"><span>Club notices</span><strong><?= count($notices) ?></strong><small>Latest announcements</small></div></div>


<div class="section-heading slim">
    <h2>Membership requests</h2>
    <a class="inline-arrow" href="<?= e(url('leader/members',['club_id'=>$club['id']])) ?>">All members →</a>
</div>

<div class="card panel">
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Student number</th>
                    <th>Faculty</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody><?php if($pending): ?><?php foreach($pending as $m): ?>
                <tr><td><strong><?= e($m['full_name']) ?></strong></td>
                <td><?= e($m['student_number']) ?></td><td><?= e($m['faculty']) ?></td>
                <td><div class="row-actions">
                    <form method="post" action="<?= e(url('leader/membership')) ?>"><?= csrf_field() ?>
                    <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
                    <input type="hidden" name="membership_id" value="<?= (int)$m['id'] ?>">
                    <button type="submit" class="btn btn-success btn-sm" name="status" value="approved">Approve</button>
                </form>
                <form method="post" action="<?= e(url('leader/membership')) ?>"><?= csrf_field() ?>
                <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
                <input type="hidden" name="membership_id" value="<?= (int)$m['id'] ?>">
                <button type="submit" class="btn btn-outline btn-sm" name="status" value="rejected">Decline</button>
            </form>
        </div>
    </td>
</tr>
                <?php endforeach; ?><?php else: ?>
                    <tr><td colspan="4" class="small-muted">No membership requests pending.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<div class="section-heading slim">
    <h2>Club events</h2>
</div>
<div class="card panel">
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Registrations</th>
                    <th>Status</th>
                    <th>Manage</th>
                </tr>
            </thead>
            
            <tbody>
                <?php if($events): ?><?php foreach($events as $event): ?>
                <tr>
            <td>
                <strong><?= e($event['title']) ?></strong>
                <div>
                    <a href="<?= e(url('event',['id'=>$event['id']])) ?>">View page</a>
                </div>
            </td>
                    <td><?= e(pretty_date($event['starts_at'],'d M Y · g:i A')) ?></td>
                    <td><?= (int)$event['booked'] ?> / <?= (int)$event['capacity'] ?></td>
                    <td><?= status_pill($event['status']) ?><?php if($event['review_note']): ?><br>
                        <small><?= e(short_text($event['review_note'],100)) ?></small>
                        <?php endif; ?>
                    </td>
                        
                        <td>
                            <div class="row-actions">

                                <a class="btn btn-outline btn-sm" href="<?= e(url('leader/attendees',['club_id'=>$club['id'],'event_id'=>$event['id']])) ?>">Attendance</a>
                                <?php if(in_array($event['status'],['pending','rejected'],true)): ?>
                                    <a class="btn btn-outline btn-sm" href="<?= e(url('leader/event/form',['club_id'=>$club['id'],'id'=>$event['id']])) ?>">Edit</a>
                                    <?php endif; ?><?php if(in_array($event['status'],['pending','approved'],true)): ?>
                                        <form method="post" action="<?= e(url('leader/event/cancel')) ?>" data-confirm="Cancel this event and notify registered participants?"><?= csrf_field() ?>
                                        <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
                                        <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
                                        <button class="btn btn-danger-outline btn-sm">Cancel</button>
                                        </form>
                                        
                                        <?php endif; ?><?php if($event['status']==='approved' && strtotime($event['ends_at'])<=time()): ?>

                                            <form method="post" action="<?= e(url('leader/event/complete')) ?>"><?= csrf_field() ?>
                                            <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
                                            <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
                                            <button class="btn btn-success btn-sm">Complete</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?><?php else: ?>
                                <tr>
                                    <td colspan="5" class="small-muted">No events yet. Create the first event.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


<div class="columns-2 gap-large">
    <div>
        <div class="section-heading slim">
            <h2>Post an announcement</h2>
        </div>
        
        <div class="card panel">
            <form action="<?= e(url('leader/announcement')) ?>" method="post" class="form-stack"><?= csrf_field() ?>
            <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
            <label>Title<input name="title" required minlength="4" maxlength="180" placeholder="Announcement headline"></label>
            <label>Message<textarea name="body" required minlength="10" maxlength="5000" rows="5" placeholder="Share the latest update..."></textarea></label>
            <button class="btn btn-primary">Publish announcement</button>
        </form>
    </div>
</div>

<div>
    <div class="section-heading slim">
        <h2>Latest announcements</h2>
    </div>
    <div class="card panel"><?php if($notices): ?><?php foreach($notices as $n): ?>
        <div class="stack-item">
            <strong><?= e($n['title']) ?></strong>
            <p><?= e(short_text($n['body'],240)) ?></p>
            <small><?= e(pretty_date($n['created_at'])) ?> · <?= e($n['status']) ?></small>
<form class="form-stack" method="post" action="<?= e(url('leader/announcement')) ?>"><?= csrf_field() ?>
    <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
    <input type="hidden" name="announcement_id" value="<?= (int)$n['id'] ?>">
    <label>Title<input name="title" required maxlength="180" value="<?= e($n['title']) ?>"></label>
    <label>Message<textarea name="body" required rows="3" maxlength="5000"><?= e($n['body']) ?></textarea></label>
    <button class="btn btn-outline btn-sm">Save & publish</button>
</form>

<form method="post" action="<?= e(url('leader/announcement/status')) ?>"><?= csrf_field() ?>
    <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
    <input type="hidden" name="announcement_id" value="<?= (int)$n['id'] ?>">
    <button class="btn btn-outline btn-sm" name="status" value="<?= $n['status']==='published'?'hidden':'published' ?>"><?= $n['status']==='published'?'Hide':'Publish' ?></button>
</form>
</div><?php endforeach; ?><?php else: ?><p class="small-muted">No announcements yet.</p><?php endif; ?></div></div></div>
<?php endif; ?></section>
