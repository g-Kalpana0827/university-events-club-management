<div class="section-heading slim"><h2>Participant feedback</h2></div>
<div class="card panel">
<?php if($feedback): ?>
  <?php foreach($feedback as $item): ?>
    <div class="stack-item">
      <div class="review-stars">
        <?= str_repeat('★',(int)$item['rating']) ?>
        <span><?= str_repeat('☆',5-(int)$item['rating']) ?></span>
      </div>
      <p><?= e($item['comment']?:'Rating only') ?></p>
      <small><?= e($item['full_name']) ?> · <?= e(pretty_date($item['created_at'],'d M Y')) ?></small>
    </div>
  <?php endforeach; ?>
<?php else: ?>
  <p class="small-muted">No reviews yet. Attendees can submit feedback after the event.</p>
<?php endif; ?>
</div>


<?php if($registration && $registration['status']==='registered' && (int)$registration['is_present']===1 && $event['status']==='completed' && strtotime($event['ends_at'])<=time()): ?>
<div class="card panel">
  <h2><?= $myFeedback?'Update your review':'Share your feedback' ?></h2>
  <form action="<?= e(url('event/feedback')) ?>" method="post" class="form-stack">
    <?= csrf_field() ?>
    <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
    <label>Rating (1 = low, 5 = excellent)
      <select name="rating" required>
        <option value="">Choose rating</option>
        <?php for($i=1;$i<=5;$i++): ?>
          <option value="<?= $i ?>" <?= (int)($myFeedback['rating']??0)===$i?'selected':'' ?>><?= $i ?> / 5</option>
        <?php endfor; ?>
      </select>
    </label>
    <label>Comment
      <textarea name="comment" rows="4" maxlength="1500" placeholder="What did you enjoy?"><?= e($myFeedback['comment']??'') ?></textarea>
    </label>
    <button class="btn btn-primary" type="submit">Send feedback</button>
  </form>
</div>
<?php endif; ?>


<aside>
<div class="card panel sticky-panel">
  <div class="section-label">EVENT REGISTRATION</div>
  <div class="sidebar-date"><?= e(pretty_date($event['starts_at'],'D, d M')) ?></div>
  <div class="small-muted"><?= e(pretty_date($event['starts_at'],'g:i A')) ?> onwards</div>

  <div class="capacity-track">
    <div style="width:<?= (int)min(100,round(((int)$event['booked']/max(1,(int)$event['capacity']))*100)) ?>%"></div>
  </div>
  <p class="small-muted"><?= max(0,(int)$event['capacity']-(int)$event['booked']) ?> of <?= (int)$event['capacity'] ?> seats left</p>

  <?php if($event['club_status']!=='approved'): ?>
    <div class="small-muted">This club is currently inactive. Registration is unavailable.</div>
  <?php elseif($event['status']!=='approved'): ?>
    <?= status_pill($event['status']) ?>
  <?php elseif(!user()): ?>
    <a class="btn btn-primary btn-block" href="<?= e(url('login')) ?>">Sign in to register</a>
  <?php elseif(user()['role']==='admin'): ?>
    <a class="btn btn-outline btn-block" href="<?= e(url('admin/events')) ?>">Review in admin panel</a>
  <?php elseif($registration && $registration['status']==='registered'): ?>
    <div class="success-note">✓ You are registered</div>
    <?php if(is_future($event['starts_at']) && (!$event['registration_deadline'] || is_future($event['registration_deadline']))): ?>
      <form method="post" action="<?= e(url('event/cancel')) ?>" data-confirm="Cancel your registration?">
        <?= csrf_field() ?>
        <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
        <button class="btn btn-outline btn-block">Cancel registration</button>
      </form>
    <?php endif; ?>
  <?php elseif(strtotime($event['starts_at'])<=time() || (!empty($event['registration_deadline']) && strtotime($event['registration_deadline'])<=time())): ?>
    <div class="small-muted">Registration has closed.</div>
  <?php elseif((int)$event['booked']>=(int)$event['capacity']): ?>
    <div class="small-muted">This event is fully booked.</div>
  <?php else: ?>
    <form action="<?= e(url('event/register')) ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
      <button class="btn btn-primary btn-block">Register for free →</button>
    </form>
  <?php endif; ?>

  <p class="small-muted">Registration is free and managed within this student project.</p>
</div>
</aside>