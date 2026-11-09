<?php
namespace App\Controllers;
class ClubController {
    public function join(): void {
        $me=require_role('student','club_leader'); $id=valid_int($_POST['club_id']??null);
        $message=transaction(function () use ($me,$id): string {
            $club=row("SELECT id,name,leader_user_id FROM clubs WHERE id=? AND status='approved' FOR UPDATE",[$id]);
            if (!$club) return 'Club not found or unavailable.';
            if ((int)$club['leader_user_id']===(int)$me['id']) return 'You already manage this club.';
            $existing=row('SELECT id,status FROM club_memberships WHERE club_id=? AND user_id=?',[$id,$me['id']]);
            if ($existing && in_array($existing['status'],['pending','approved'],true)) return 'You have already requested or joined this club.';
            if ($existing) execute("UPDATE club_memberships SET status='pending',membership_role='member',joined_at=NOW() WHERE id=?",[$existing['id']]);
            else execute("INSERT INTO club_memberships(club_id,user_id,status) VALUES(?,?,'pending')",[$id,$me['id']]);
            if ($club['leader_user_id']) notify((int)$club['leader_user_id'],'Membership request',$me['full_name'].' asked to join '.$club['name'],'leader/members&club_id='.$id);
            audit('membership_requested','club',$id);
            return '';
        });
        flash($message?'warning':'success',$message?:'Membership request sent for review.');
        go($id?'club':'clubs',$id?['id'=>$id]:[]);
    }
    public function leave(): void {
        $me=require_role('student','club_leader'); $id=valid_int($_POST['club_id']??null);
        $changed=execute("UPDATE club_memberships SET status='left' WHERE club_id=? AND user_id=? AND membership_role='member' AND status IN ('approved','pending','rejected')",[$id,$me['id']]);
        flash($changed?'success':'warning',$changed?'Your club membership/request has been withdrawn.':'No membership request is available to withdraw.');
        go($id?'club':'clubs',$id?['id'=>$id]:[]);
    }
}
