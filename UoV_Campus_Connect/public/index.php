<?php
require dirname(__DIR__).'/vendor_autoload.php';
use App\Core\Router;
use App\Controllers\{SiteController,AuthController,ClubController,EventController,LeaderController,AdminController};

header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-store');
foreach ([$_GET, $_POST] as $fields) {
    foreach ($fields as $value) {
        if (!is_string($value) || str_contains($value, "\0") || !preg_match('//u', $value)) {
            http_response_code(400);
            exit('Invalid request. Please return to the page and use the form fields.');
        }
    }
}

$router = new Router();
foreach ([
    'home'=>'home','clubs'=>'clubs','club'=>'club','events'=>'events','event'=>'event','calendar'=>'calendar','about'=>'about',
    'dashboard'=>'dashboard','notifications'=>'notifications','profile'=>'profile'
] as $route=>$method) $router->get($route,[SiteController::class,$method]);
foreach (['login'=>'loginForm','register'=>'registerForm'] as $route=>$method) $router->get($route,[AuthController::class,$method]);
foreach (['login'=>'login','register'=>'register','logout'=>'logout','profile/save'=>'profileSave','notifications/read'=>'markRead'] as $route=>$method) $router->post($route,[AuthController::class,$method]);
foreach (['club/join'=>'join','club/leave'=>'leave'] as $route=>$method) $router->post($route,[ClubController::class,$method]);
foreach (['event/register'=>'register','event/cancel'=>'cancel','event/feedback'=>'feedback'] as $route=>$method) $router->post($route,[EventController::class,$method]);
foreach (['leader'=>'index','leader/members'=>'members','leader/event/form'=>'eventForm','leader/attendees'=>'attendees','leader/club/form'=>'clubForm','leader/reports'=>'reports'] as $route=>$method) $router->get($route,[LeaderController::class,$method]);
foreach (['leader/membership'=>'membership','leader/event/save'=>'eventSave','leader/event/cancel'=>'eventCancel','leader/attendance'=>'attendance','leader/announcement'=>'announcement','leader/announcement/status'=>'announcementStatus','leader/club/save'=>'clubSave','leader/event/complete'=>'eventComplete'] as $route=>$method) $router->post($route,[LeaderController::class,$method]);
foreach (['admin'=>'index','admin/events'=>'events','admin/clubs'=>'clubs','admin/club/form'=>'clubForm','admin/users'=>'users','admin/export'=>'export','admin/records'=>'records','admin/reports'=>'reports'] as $route=>$method) $router->get($route,[AdminController::class,$method]);
foreach (['admin/event/status'=>'eventStatus','admin/club/save'=>'clubSave','admin/club/status'=>'clubStatus','admin/user/role'=>'userRole','admin/user/status'=>'userStatus','admin/category/save'=>'categorySave','admin/venue/save'=>'venueSave'] as $route=>$method) $router->post($route,[AdminController::class,$method]);

try { $router->dispatch(($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD' ? 'GET' : ($_SERVER['REQUEST_METHOD'] ?? 'GET'), (string)($_GET['r'] ?? 'home')); }
catch (\Throwable $e) {
    error_log('[Campus Connect] '.$e);
    http_response_code(500);
    // Minimal fallback that does not attempt another database connection.
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Campus Connect setup</title><body style="font:16px system-ui;margin:12vh auto;max-width:680px;padding:24px;color:#3d2340"><h1>Campus Connect needs attention</h1><p>Check that XAMPP Apache and MySQL are running, the SQL schema has been imported, and the database configuration is correct.</p><p>See <code>README.md</code> and your PHP error log for more information.</p></body></html>';
}
