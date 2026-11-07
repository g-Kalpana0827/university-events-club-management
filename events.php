<section class="page-banner">
    <div class="container">
        <div class="section-label">YOUR NEXT EXPERIENCE</div>
        <h1>Campus events</h1>
        <p>Explore upcoming gatherings, talks, competitions and community activities.</p>
</div>
</section>

<section class="section container">
    <form method="get" action="<?= e(base_path().'/index.php') ?>" class="filters card">
        <input type="hidden" name="r" value="events">
        <label><span>Search events</span>
             <input name="q" type="search" placeholder="Search title or venue" value="<?= e($search) ?>">
        </label>

        <label><span>Category</span>
        <select name="category_id">
            <option value="">All categories</option><?php foreach($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= $categoryId===(int)$cat['id']?'selected':'' ?>><?= e($cat['name']) ?></option><?php endforeach; ?>
        </select>
        </label>

        <label><span>Club</span>
            <select name="club">
                <option value="">All clubs</option><?php foreach($clubs as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $clubId===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach;?>
            </select>
            </label>
            
        <label><span>When</span>
            <select name="when">
                <option value="upcoming" <?= $when==='upcoming'?'selected':'' ?>>Upcoming</option><option value="past" <?= $when==='past'?'selected':'' ?>>Past</option><option value="all" <?= $when==='all'?'selected':'' ?>>All</option>
            </select>
        </label>
        
        <button class="btn btn-primary" type="submit">Apply filters</button>
</form>
<p class="result-count"><?= count($events) ?> events found</p><?php if($events): ?>
    
    <div class="cards-grid event-grid"><?php foreach($events as $eventItem)require dirname(__DIR__).'/partials/event_card.php';?></div><?php else: ?>
        <div class="empty-state">No events match your search right now.</div><?php endif;?>
</section>
