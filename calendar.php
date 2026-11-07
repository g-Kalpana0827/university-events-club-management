<section class="page-banner">
    
    <div class="container">
        <div class="section-label">PLAN AHEAD</div>
            <h1>Campus calendar</h1>
                 <p>See the dates of approved student activities at a glance.</p>
    </div>
</section>

<section class="section container">
    <div class="calendar-toolbar">
        <a class="btn btn-outline" href="<?= e(url('calendar',['month'=>date('Y-m',strtotime('-1 month',$first))])) ?>">← Previous</a>
            <h2><?= e(date('F Y',$first)) ?></h2>
                <a class="btn btn-outline" href="<?= e(url('calendar',['month'=>date('Y-m',strtotime('+1 month',$first))])) ?>">Next →</a>
    </div>
        
    <div class="calendar-wrapper card">
        <div class="calendar-grid">
            <div class="calendar-weekday">Mon</div>
            <div class="calendar-weekday">Tue</div>
            <div class="calendar-weekday">Wed</div>
            <div class="calendar-weekday">Thu</div>
            <div class="calendar-weekday">Fri</div>
            <div class="calendar-weekday">Sat</div>
            <div class="calendar-weekday">Sun</div>
            <?php $offset=(int)date('N',$first)-1;$days=(int)date('t',$first);for($i=0;$i<$offset;$i++): ?>
                <div class="calendar-day muted-cell"></div>
            <?php endfor; ?>
            <?php for($d=1;$d<=$days;$d++): ?>
                <div class="calendar-day <?= ($ym.'-'.str_pad((string)$d,2,'0',STR_PAD_LEFT)===date('Y-m-d'))?'calendar-today':'' ?>">
                    <strong><?= $d ?></strong>
                    <?php foreach(($byDay[$d]??[]) as $ev): ?>
                        <a class="calendar-event" title="<?= e($ev['title']) ?>" href="<?= e(url('event',['id'=>$ev['id']])) ?>">
                            <?= e(date('g:i A',strtotime($ev['starts_at']))) ?> · <?= e($ev['title']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    <div class="mobile-calendar-note">On a small screen, scroll horizontally to view the entire week.</div>
</section>
