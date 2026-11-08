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