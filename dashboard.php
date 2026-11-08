

<div class="stat-card">
    <span>Event registrations</span>
    <strong><?= count(array_filter($registrations, fn($r) => $r['status'] === 'registered')) ?></strong>
    <small>Registered events</small>
</div>

<div class="stat-card">
    <span>Participation</span>
    <strong><?= count(array_filter($registrations, fn($r) => !empty($r['attended_at']))) ?></strong>
    <small>Events attended</small>
</div>

<div>
    <div class="section-heading slim">
        <h2>My events</h2>
        <a class="inline-arrow" href="<?= e(url('events')) ?>">Find more →</a>
    </div>

    <div class="card panel">
        <?php if ($registrations): ?>
            <?php foreach ($registrations as $r): ?>
                <div class="list-record">
                    <div>
                        <strong><a href="<?= e(url('event', ['id' => $r['id']])) ?>"><?= e($r['title']) ?></a></strong>
                        <div class="small-muted"><?= e(pretty_date($r['starts_at'])) ?> · <?= e($r['venue']) ?></div>
                    </div>
                    <?= status_pill($r['event_status'] === 'cancelled' ? 'cancelled' : $r['status']) ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="small-muted">You have not registered for an event yet.</p>
        <?php endif; ?>
    </div>
</div>

