<?php
/** Run from command line ONLY: php scripts/seed.php "Your-Strong-Demo-Password" */
if (PHP_SAPI !== 'cli') { http_response_code(403);exit('CLI only'); }
require_once dirname(__DIR__).'/vendor_autoload.php';
$password=(string)($argv[1]??'');
if (strlen($password)<10 || strlen($password)>72) {fwrite(STDERR,"Usage: php scripts/seed.php \"A-Strong-Demo-Password\" (10-72 characters)\n");exit(1);}
try { $pdo=db(); } catch(Throwable $e) { fwrite(STDERR,"DB connection failed: ".$e->getMessage()."\n");exit(1); }
if(scalar('SELECT COUNT(*) FROM users')>0) {fwrite(STDERR,"Database already contains users. Seeding aborted to protect existing data. Use a fresh empty database.\n");exit(1);}
$pdo->beginTransaction();
try {
    $hash=password_hash($password,PASSWORD_DEFAULT);
    $users=[
      ['Demo Administrator','admin@example.test','DEMO/ADMIN','University-wide','admin'],
      ['Demo ITCS Leader','itcs.leader@example.test','DEMO/LEADER/1','Faculty of Applied Science','club_leader'],
      ['Demo Gavel Leader','gavel.leader@example.test','DEMO/LEADER/2','Faculty of Business Studies','club_leader'],
      ['Demo Student One','student1@example.test','DEMO/STUDENT/01','Faculty of Applied Science','student'],
      ['Demo Student Two','student2@example.test','DEMO/STUDENT/02','Faculty of Technological Studies','student'],
    ];
    $userIds=[];
    foreach($users as [$name,$email,$number,$faculty,$role]) {
        execute('INSERT INTO users(full_name,email,student_number,faculty,role,password_hash) VALUES(?,?,?,?,?,?)',[$name,$email,$number,$faculty,$role,$hash]);
        $userIds[$email]=(int)$pdo->lastInsertId();
    }
    $admin=$userIds['admin@example.test'];$itcs=$userIds['itcs.leader@example.test'];$gavel=$userIds['gavel.leader@example.test'];$student1=$userIds['student1@example.test'];$student2=$userIds['student2@example.test'];
    // Names are public examples inspired by vau.ac.lk/societies-and-clubs/; these records are fictitious demos.
    $clubs=[
      ['Information Technology and Computing Society (ITCS)','Technology','Faculty of Applied Science',$itcs,'A sample technology community focused on web development, programming activities, research discussions and collaborative student learning.'],
      ['IEEE Student Branch','Technology','University-wide',null,'A demonstration student engineering community that explores computing, professional networking and technical events.'],
      ['Gavel Club','Leadership','University-wide',$gavel,'A sample student public-speaking and leadership society that supports confidence, communication and friendly practice.'],
      ['AIESEC','Leadership','University-wide',null,'A demo listing for an international youth-leadership community with cultural exchange and volunteering interests.'],
      ['Mozilla Campus Club','Technology','University-wide',null,'A demonstration community for open source technologies, browser innovation and collaborative software development.'],
      ['Environmental Society','Environment','University-wide',null,'A sample campus community for green initiatives, environmental awareness and responsible student actions.'],
      ['Project Management Club','Professional','Faculty of Business Studies',null,'A demonstration society for project planning, organizational skills, teamwork and student leadership.'],
      ['Leo Club','Community Service','University-wide',null,'A sample service-minded student organization planning volunteering events and local community initiatives.'],
    ];
    $clubIds=[];
    foreach($clubs as [$name,$category,$faculty,$leader,$description]) {
        execute("INSERT INTO clubs(name,slug,description,category,faculty,leader_user_id,status) VALUES(?,?,?,?,?,?,'approved')",[$name,slugify($name),$description,$category,$faculty,$leader]);
        $clubIds[$name]=(int)$pdo->lastInsertId();
    }
    $itcsClub=$clubIds['Information Technology and Computing Society (ITCS)'];$gavelClub=$clubIds['Gavel Club'];$envClub=$clubIds['Environmental Society'];$ieeeClub=$clubIds['IEEE Student Branch'];
    foreach([[$itcsClub,$student1,'approved'],[$gavelClub,$student1,'approved'],[$itcsClub,$student2,'pending'],[$gavelClub,$student2,'pending'],[$envClub,$student2,'approved']] as [$c,$u,$s]) execute('INSERT INTO club_memberships(club_id,user_id,status) VALUES(?,?,?)',[$c,$u,$s]);
    // Proposal category and venue reference records (examples only).
    foreach([['Workshop','Practical hands-on sessions'],['Seminar','Talks and academic seminars'],
        ['Competition','Student competitions'],['Sports','Athletic activities'],
        ['Cultural','Cultural performances'],['Community','Community activities'],
        ['General','Other approved activities']] as [$n,$d])
        execute('INSERT IGNORE INTO event_categories(name,description) VALUES(?,?)',[$n,$d]);
    $categoryMap=[];
    foreach(rows('SELECT id,name FROM event_categories') as $cat) $categoryMap[$cat['name']]=$cat['id'];
    $items=[
      [$itcsClub,$itcs,'Build Your First Web Application','A practical demonstration workshop on PHP, HTML, MySQL and the fundamentals of constructing a student-focused web application.','Computer Laboratory 02',5,10,65,'approved'],
      [$gavelClub,$gavel,'Speak with Confidence Workshop','Join a supportive student workshop on public speaking, speech structure, teamwork and delivering a confident presentation.','Main Auditorium',10,10,100,'approved'],
      [$itcsClub,$itcs,'Open Source Day','Discuss open-source project contributions, version control, documentation and responsible collaboration in software engineering.','Lecture Hall 04',15,9,85,'approved'],
      [$gavelClub,$gavel,'Campus Leadership Meetup','An interactive leadership session exploring student communication, peer support and effective society activity planning.','Seminar Room B',24,10,45,'approved'],
      [$ieeeClub,$admin,'Introduction to Robotics','A sample information session featuring introductory robotics, electronics and engineering problem solving for students.','Tech Hall',21,14,70,'approved'],
      [$envClub,$admin,'Green Campus Awareness Day','An open activity to discuss sustainability, recycling and the value of maintaining healthy campus environments.','Campus Garden Meeting Point',32,8,75,'approved'],
      [$itcsClub,$itcs,'Research Poster Planning Session','A sample proposed workshop on planning, designing and communicating research posters for undergraduate students.','Seminar Hall A',38,10,55,'pending'],
      [$gavelClub,$gavel,'Practice Speech Circle','A demonstration completed club event where members practised preparing short talks and sharing respectful feedback.','Meeting Room 1',-7,10,30,'approved'],
    ];
    $eventIds=[];
    foreach($items as [$clubId,$creator,$title,$desc,$venue,$days,$hour,$capacity,$status]) {
        $date=date('Y-m-d',strtotime(($days>=0?'+':'').$days.' days'));
        $start=$date.sprintf(' %02d:00:00',$hour);$end=$date.sprintf(' %02d:00:00',$hour+2);
        $v=row('SELECT id,capacity FROM venues WHERE name=?',[$venue]);
        if(!$v){execute('INSERT INTO venues(name,location,capacity) VALUES(?,?,?)',[$venue,'University of Vavuniya',$capacity]);$venueId=(int)$pdo->lastInsertId();}
        else{$venueId=(int)$v['id'];}
        $categoryId=$categoryMap[str_contains(strtolower($title),'workshop')?'Workshop':'General'];
        $deadline=date('Y-m-d H:i:s',strtotime($start.' -1 hour'));
        execute('INSERT INTO events(club_id,created_by,title,description,venue,venue_id,category_id,starts_at,ends_at,registration_deadline,capacity,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)',
          [$clubId,$creator,$title,$desc,$venue,$venueId,$categoryId,$start,$end,$deadline,$capacity,$status]);
        $eventIds[$title]=(int)$pdo->lastInsertId();
    }
    execute("INSERT INTO event_registrations(event_id,user_id,status) VALUES(?,?,'registered')",[$eventIds['Build Your First Web Application'],$student1]);
    execute("INSERT INTO event_registrations(event_id,user_id,status) VALUES(?,?,'registered')",[$eventIds['Speak with Confidence Workshop'],$student2]);
    execute("INSERT INTO event_registrations(event_id,user_id,status,attended_at) VALUES(?,?,'registered',DATE_SUB(NOW(),INTERVAL 6 DAY))",[$eventIds['Practice Speech Circle'],$student1]);
    $practiceReg=row('SELECT id FROM event_registrations WHERE event_id=? AND user_id=?',[$eventIds['Practice Speech Circle'],$student1]);
    execute('INSERT INTO attendance(registration_id,is_present,marked_at) VALUES(?,1,DATE_SUB(NOW(),INTERVAL 6 DAY))',[$practiceReg['id']]);
    execute('INSERT INTO event_feedback(event_id,user_id,registration_id,rating,comment) VALUES(?,?,?,?,?)',[$eventIds['Practice Speech Circle'],$student1,$practiceReg['id'],5,'An engaging demonstration event with helpful feedback.']);
    execute("UPDATE events SET status='completed' WHERE id=?",[$eventIds['Practice Speech Circle']]);
    foreach([
      [$itcsClub,$itcs,'Welcome to the tech community','This is a fictional demo announcement. Browse our sample events and join activities.'],
      [$gavelClub,$gavel,'Speaking practice open','New participants are welcome to our sample public-speaking and leadership sessions.'],
    ] as [$clubId,$creator,$title,$body]) execute('INSERT INTO announcements(club_id,posted_by,title,body) VALUES(?,?,?,?)',[$clubId,$creator,$title,$body]);
    execute('INSERT INTO notifications(user_id,title,message,link) VALUES(?,?,?,?)',[$admin,'Demo data installed','Sample accounts, clubs and events are ready.','admin']);
    execute('INSERT INTO notifications(user_id,title,message,link) VALUES(?,?,?,?)',[$student1,'Welcome to Campus Connect','Your sample student account is ready.','dashboard']);
    $pdo->commit();
    echo "\nDemo data installed successfully.\n\n";
    foreach($users as $u) echo str_pad($u[4],14).' '.$u[1]."\n";
    echo "\nShared demo password: [the one you supplied]\n";
    echo "Change/remove sample accounts before any real deployment.\n";
} catch(Throwable $e) {
    if($pdo->inTransaction())$pdo->rollBack();
    fwrite(STDERR,'Seeding failed: '.$e->getMessage()."\n");exit(1);
}
