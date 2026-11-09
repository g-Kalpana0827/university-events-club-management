<?php
namespace App\Controllers;

final class SiteController {
    
    public function events(): void {
        $search=mb_substr(trim((string)($_GET['q'] ?? '')),0,80);
        $clubId=valid_int($_GET['club'] ?? null);
        $when=(string)($_GET['when'] ?? 'upcoming');
        $categoryId=valid_int($_GET['category_id']??null);
        $where=["e.status IN ('approved','completed')","c.status='approved'"];$args=[];
        if($categoryId){$where[]='e.category_id=?';$args[]=$categoryId;}
        if ($search!=='') { $where[]='(e.title LIKE ? OR e.description LIKE ? OR e.venue LIKE ?)';$term='%'.clean_like($search).'%';$args=array_merge($args,[$term,$term,$term]); }
        if ($clubId) { $where[]='e.club_id=?';$args[]=$clubId; }
        if ($when!=='all') $where[]=($when==='past' ? 'e.ends_at < NOW()' : 'e.ends_at >= NOW()');
        $events=rows("SELECT e.*,c.name AS club_name,c.category AS club_category,(SELECT COUNT(*) FROM event_registrations r WHERE r.event_id=e.id AND r.status='registered') AS booked FROM events e JOIN clubs c ON c.id=e.club_id WHERE ".implode(' AND ',$where).' ORDER BY e.starts_at '.($when==='past'?'DESC':'ASC').' LIMIT 100',$args);
        $clubs=rows("SELECT id,name FROM clubs WHERE status='approved' ORDER BY name");
        $categories=rows('SELECT id,name FROM event_categories ORDER BY name');
        render('events',compact('events','search','clubId','when','clubs','categories','categoryId')+['pageTitle'=>'Campus events']);
    }

    public function event(): void {
        $id=valid_int($_GET['id'] ?? null);
        $event=row("SELECT e.*,c.name AS club_name,c.category AS club_category,c.status AS club_status,u.full_name AS organizer,(SELECT COUNT(*) FROM event_registrations r WHERE r.event_id=e.id AND r.status='registered') AS booked FROM events e JOIN clubs c ON c.id=e.club_id JOIN users u ON u.id=e.created_by WHERE e.id=?",[$id]);
        if (!$event || ((!in_array($event['status'],['approved','completed'],true)||$event['club_status']!=='approved') && !(user() && (user()['role']==='admin' || (int)$event['created_by']===(int)user()['id'])))) { $this->notFound(); return; }
        $registration=user() ? row('SELECT status,attended_at FROM event_registrations WHERE user_id=? AND event_id=?',[user()['id'],$id]) : null;
        $myFeedback=user() ? row('SELECT id FROM event_feedback WHERE user_id=? AND event_id=?',[user()['id'],$id]) : null;
        $feedback=rows('SELECT f.rating,f.comment,f.created_at,u.full_name FROM event_feedback f JOIN users u ON u.id=f.user_id WHERE f.event_id=? ORDER BY f.created_at DESC LIMIT 15',[$id]);
        render('event',compact('event','registration','feedback','myFeedback')+['pageTitle'=>$event['title']]);
    }
    
    public function calendar(): void {
        $ym=(string)($_GET['month']??date('Y-m'));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$ym)) $ym=date('Y-m');
        $first=strtotime($ym.'-01');
        if ($first===false || (int)date('Y',$first)<2020 || (int)date('Y',$first)>2100) { $ym=date('Y-m'); $first=strtotime($ym.'-01'); }
        $start=date('Y-m-01 00:00:00',$first);
        $end=date('Y-m-01 00:00:00',strtotime('+1 month',$first));
        $events=rows("SELECT e.id,e.title,e.starts_at,c.name AS club_name FROM events e JOIN clubs c ON c.id=e.club_id WHERE e.status='approved' AND c.status='approved' AND e.starts_at>=? AND e.starts_at<? ORDER BY e.starts_at",[$start,$end]);
        $byDay=[];foreach($events as $event) $byDay[(int)date('j',strtotime($event['starts_at']))][]=$event;
        render('calendar',compact('ym','first','byDay')+['pageTitle'=>'Event calendar']);
    }
  
}
