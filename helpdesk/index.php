<?php
require_once('../../config.php');

if (!isloggedin() OR isguestuser()) {
	require_login();
	die;
}

// Globals.
global $PAGE, $USER, $OUTPUT, $CFG;

// Fetch context.
$context = context_system::instance();
// Set page context.
$PAGE->set_context($context);

// Set page layout.
$PAGE->set_pagelayout('standard');

$userID = $USER->id;
$helpdeskConfig = get_config('local_helpdesk');

$PAGE->set_title($helpdeskConfig->helpdeskheadertext);
$PAGE->set_heading($helpdeskConfig->helpdeskheadertext);
$PAGE->navbar->add($helpdeskConfig->helpdeskheadertext, new moodle_url('/local/helpdesk/tickets'));
$PAGE->set_url('/local/helpdesk/index.php'); // necessarily

$PAGE->requires->css( new moodle_url($CFG->wwwroot . '/local/helpdesk/css/app.css'), true);


// получаем имя текущей роли пользователя
$isAdmin = 0;
$roleNameArr = array();
$allRoles = get_all_roles();
$objUserRole = get_user_roles_with_special($context, $userID);
foreach($allRoles as $lastRole) {
	foreach($objUserRole as $roleID) {
		if($lastRole->id == $roleID->roleid)
			$roleNameArr[] = $lastRole->id;
		}
	}

$libraryConfig = get_config('local_helpdesk');
$roleButtonArr = explode(',',$libraryConfig->role);
			
$intersect = array_intersect($roleButtonArr,$roleNameArr);
if( !empty($intersect) || is_siteadmin($USER->id) )
	$isAdmin = 1;
	

$result = $DB->get_record_sql('SELECT * FROM hed_users WHERE name = ?', array($USER->username));

if (empty($result)) {

  $datetime = date('Y-m-d H:i:s', time());

  $options = [
    'cost' => 10,
  ];
  $password = password_hash($USER->username, PASSWORD_BCRYPT, $options);

  $param = array(
        "name" => $USER->username,
        "email" => $USER->username.'_'.$USER->email,
        "password" => substr($password,7), // $2y$10$
        "remember_token" => '',
        "created_at" => $datetime,
        "updated_at" => $datetime,
		"ticketit_admin" => ($isAdmin)?'t':'f',
  );

  $sql = 'INSERT INTO hed_users ('. implode( ',', array_keys( $param ) ) .') VALUES (\''. implode( '\',\'', $param ) .'\')';

  $DB->execute($sql);

  $result = $DB->get_record_sql('SELECT MAX(id) as id FROM hed_users');

  $MOODLE_ID = $result->id;

} else {

  if (($result->ticketit_admin == 't') && ($isAdmin == 0)) {
  
    $DB->execute("UPDATE hed_users SET ticketit_admin = 'f' WHERE name = '$USER->username'");
  
  } else if (($result->ticketit_admin == 'f') && ($isAdmin == 1)) {
  
    $DB->execute("UPDATE hed_users SET ticketit_admin = 't' WHERE name = '$USER->username'");
  
  }

  $MOODLE_ID = $result->id;

}

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/

require __DIR__.'/../ticketit/bootstrap/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../ticketit/bootstrap/app.php';


/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/


$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
//$request->session()->flush();
$response->cookie('moodle_id', $MOODLE_ID, 600);
$response->send();

$kernel->terminate($request, $response);

//echo $OUTPUT->footer();
