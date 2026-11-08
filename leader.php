
<td><?= (int)$event['booked'] ?> / <?= (int)$event['capacity'] ?></td>

<a class="btn btn-outline btn-sm" href="<?= e(url('leader/attendees',['club_id'=>$club['id'],'event_id'=>$event['id']])) ?>">
    Attendance</a>
