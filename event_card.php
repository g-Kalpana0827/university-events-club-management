    <?= e(date('d',strtotime($eventItem['starts_at']))) ?>
    <?= e(date('M',strtotime($eventItem['starts_at']))) ?>
    
    <?= e($eventItem['club_name']??'Student society') ?>
    <?= e(url('event',['id'=>$eventItem['id']])) ?>
    <?= e($eventItem['title']) ?>

 
    <?= e(pretty_date($eventItem['starts_at'],'d M Y · g:i A')) ?>
    <?= e($eventItem['venue']) ?>

    <?= max(0,(int)$eventItem['capacity']-(int)($eventItem['booked']??0)) ?>
    <?= e(url('event',['id'=>$eventItem['id']])) ?>

