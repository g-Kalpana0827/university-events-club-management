    <?= e(date('d',strtotime($eventItem['starts_at']))) ?>
    <?= e(date('M',strtotime($eventItem['starts_at']))) ?>
    
    <?= e($eventItem['club_name']??'Student society') ?>
   // <h3><a href="<?= e(url('event',['id'=>$eventItem['id']])) ?>"><?= e($eventItem['title']) ?></a></h3>

 <div class="meta-line">
   // <span>◷ <?= e(pretty_date($eventItem['starts_at'],'d M Y · g:i A')) ?></span>
    //<span>⌖ <?= e($eventItem['venue']) ?></span>
</div>
 <div class="card-bottom">
   // <span class="small-muted"><?= max(0,(int)$eventItem['capacity']-(int)($eventItem['booked']??0)) ?> seats remaining</span>
    //<a class="arrow-link" href="<?= e(url('event',['id'=>$eventItem['id']])) ?>">View details →</a></div>
</div>
</article>
