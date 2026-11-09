         <?php
         
         public function eventForm(): void {
        $clubId=valid_int($_GET['club_id']??null);$club=$this->manager($clubId);
        $event=null;$id=valid_int($_GET['id']??null);
        if($id){
            $event=row('SELECT * FROM events WHERE id=? AND club_id=?',[$id,$clubId]);
            if(!$event || !in_array($event['status'],['pending','rejected'],true)){
                flash('warning','Only pending or rejected event proposals can be edited.');go('leader',['club_id'=>$clubId]);
            }
        }
        $categories=rows('SELECT id,name FROM event_categories ORDER BY name');
        $venues=rows('SELECT id,name,location,capacity FROM venues ORDER BY name');
        render('event_form',compact('club','event','categories','venues')+['pageTitle'=>$event?'Edit event proposal':'Create event proposal']);
    }


    public function eventSave(): void {
        $clubId=valid_int($_POST['club_id']??null);$club=$this->manager($clubId);

        if($club['status']!=='approved'){flash('danger','Inactive clubs cannot submit events.');go('leader',['club_id'=>$clubId]);}

        $id=valid_int($_POST['id']??null);$title=post_str('title');$description=post_str('description');

        $categoryId=valid_int($_POST['category_id']??null);$venueId=valid_int($_POST['venue_id']??null);

        $starts=post_str('starts_at');$ends=post_str('ends_at');$deadline=post_str('registration_deadline');

        $capacity=valid_int($_POST['capacity']??null);$back=['club_id'=>$clubId];if($id)$back['id']=$id;

        $category=row('SELECT id FROM event_categories WHERE id=?',[$categoryId]);

        $venue=row('SELECT id,name,capacity FROM venues WHERE id=?',[$venueId]);

        $startTime=\DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$starts);

        $endTime=\DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$ends);

        $deadlineTime=\DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$deadline);

        $dates=$startTime&&$endTime&&$deadlineTime&&$startTime->format('Y-m-d\TH:i')===$starts&&$endTime->format('Y-m-d\TH:i')===$ends&&$deadlineTime->format('Y-m-d\TH:i')===$deadline;

        if(mb_strlen($title)<5||mb_strlen($title)>180||mb_strlen($description)<20||mb_strlen($description)>10000||
            !$category||!$venue||$capacity<1||$capacity>(int)($venue['capacity']??0)||!$dates||
            $endTime<=$startTime||$startTime->getTimestamp()<=time()||$deadlineTime>$startTime||$deadlineTime->getTimestamp()<=time()){
            flash('danger','Check the event title, description, category, venue, venue capacity, and future dates/deadline.');go('leader/event/form',$back);
        }

        $startSql=$startTime->format('Y-m-d H:i:s');$endSql=$endTime->format('Y-m-d H:i:s');$deadlineSql=$deadlineTime->format('Y-m-d H:i:s');

        if($id){
            $existing=row('SELECT id,status FROM events WHERE id=? AND club_id=?',[$id,$clubId]);
            if(!$existing||!in_array($existing['status'],['pending','rejected'],true)){
                flash('danger','This event cannot be edited.');go('leader',['club_id'=>$clubId]);
            }
            execute("UPDATE events SET title=?,description=?,category_id=?,venue_id=?,venue=?,starts_at=?,ends_at=?,registration_deadline=?,capacity=?,status='pending',review_note=NULL WHERE id=? AND club_id=?",
                [$title,$description,$categoryId,$venueId,$venue['name'],$startSql,$endSql,$deadlineSql,$capacity,$id,$clubId]);
        }
        else{
            execute("INSERT INTO events(club_id,created_by,title,description,category_id,venue_id,venue,starts_at,ends_at,registration_deadline,capacity,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,'pending')",
                [$clubId,require_login()['id'],$title,$description,$categoryId,$venueId,$venue['name'],$startSql,$endSql,$deadlineSql,$capacity]);
            $id=(int)db()->lastInsertId();
        }
        
        foreach(rows("SELECT id FROM users WHERE role='admin' AND status='active'") as $admin)
            notify((int)$admin['id'],'Event awaiting approval',$title.' from '.$club['name'].' needs review.','admin/events');
        audit('event_submitted','event',$id);flash('success','Event proposal submitted for approval.');go('leader',['club_id'=>$clubId]);
    }
   
   
    public function eventCancel(): void {
        $clubId=valid_int($_POST['club_id']??null);$this->manager($clubId);$id=valid_int($_POST['event_id']??null);
        $event=row('SELECT title,status FROM events WHERE id=? AND club_id=?',[$id,$clubId]);
        if($event && in_array($event['status'],['pending','approved'],true)) {
            execute("UPDATE events SET status='cancelled' WHERE id=? AND club_id=?",[$id,$clubId]);
            foreach(rows("SELECT user_id FROM event_registrations WHERE event_id=? AND status='registered'",[$id]) as $r) notify((int)$r['user_id'],'Event cancelled',$event['title'].' has been cancelled.','dashboard');
            audit('event_cancelled','event',$id);flash('success','Event cancelled and participants notified.');
        }
        go('leader',['club_id'=>$clubId]);
    }


    public function eventComplete(): void {
        $clubId=valid_int($_POST['club_id']??null);$this->manager($clubId);
        $id=valid_int($_POST['event_id']??null);
        $event=row('SELECT id,status,ends_at FROM events WHERE id=? AND club_id=?',[$id,$clubId]);
        if(!$event||$event['status']!=='approved'||strtotime($event['ends_at'])>time()){
            flash('warning','Only approved events that have ended can be marked complete.');
        }else{
            execute("UPDATE events SET status='completed' WHERE id=?",[$id]);
            audit('event_completed','event',$id);flash('success','Event marked completed. Attendees may submit feedback.');
        }
        go('leader',['club_id'=>$clubId]);
    }
?>
   
   
