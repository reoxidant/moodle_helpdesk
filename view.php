<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

require('../../config.php');
require_once($CFG -> dirroot . '/local/helpdesk/lib.php');
require_once($CFG -> dirroot . '/local/helpdesk/locallib.php');

$PAGE -> requires -> js('/local/helpdesk/js/helpdeskview.js');

$issueid = optional_param('issueid', '', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

$screen = helpdesk_resolve_screen();
$view = helpdesk_resolve_view();

$url = new moodle_url('/local/helpdesk/view.php', compact('view', 'screen'));

// Redirect

if ($view === 'view' && (empty($screen) || $screen === 'viewanissue' || $screen === 'editanissue') && empty($issueid)) {
    redirect(new moodle_url('/local/helpdesk/view.php', array('view' => 'view', 'screen' => 'browse')));
}

if ($view === 'reportanissue') {
    redirect(new moodle_url('/local/helpdesk/reportissue.php'));
}

if ($issueid) {
    $view = 'view';
    if (empty($screen)) {
        $screen = 'viewanissue';
    }
}

if (!isloggedin() || isguestuser()) {
    require_login();
    die;
}

$context = context_system ::instance();

$pluginname = get_string('pluginname', 'local_helpdesk');

$PAGE -> set_context($context);
$PAGE -> set_title($pluginname);
$PAGE -> set_heading($pluginname);
$PAGE -> set_url($url);
$PAGE -> set_pagelayout('standard');
$PAGE -> navbar -> add($pluginname);

$renderer = $PAGE -> get_renderer('local_helpdesk');

$result = 0;
if ($action !== '' && ($view === 'view' || $view === 'resolved')) {
    include($CFG -> dirroot . '/local/helpdesk/views/viewcontroller.php');
    $result = 1;
}

echo $OUTPUT -> header();

echo $OUTPUT -> box_start('', 'helpdesk-view');
echo $renderer -> tabs($view, $screen);

// MARK: Tickets screen views

if ($view === 'view') {
    if ($result !== -1) {
        switch ($screen) {
            case 'tickets':
                $resolved = 0;
                include($CFG -> dirroot . '/local/helpdesk/views/viewassignedtickets.php');
                break;
            case 'browse':
                $resolved = 0;
                include($CFG -> dirroot . '/local/helpdesk/views/viewtickets.php');
                break;
            case 'viewanissue':
                if (has_any_capability(['local/helpdesk:seeissues', 'local/helpdesk:resolve', 'local/helpdesk:manage'], $context)) {
                    include($CFG -> dirroot . '/local/helpdesk/views/viewanissue.php');
                } else {
                    print_error('errornoaccessissue', 'local_helpdesk');
                }
                break;
            case 'editanissue':
                if (has_capability('/local/helpdesk/views/', $context)) {
                    include($CFG -> dirroot . '/local/helpdesk/views/editanissue.php');
                } else {
                    print_error('errornoaccessissue', 'local_helpdesk');
                }
                break;
            default:
                throw new Exception('Unexpected value');
        }
    }
} elseif ($view === 'resolved') {
    // TODO: Create 2 tabs MyResolve issue list and other
    if ($result !== -1) {
        switch ($screen) {
            case 'browse':
                if (has_capability('local/helpdesk:viewallissues', $context)) {
                    $resolved = 1;
                    include($CFG -> dirroot . '/local/helpdesk/views/viewtickets.php');
                } else {
                    print_error('errornoaccessallissues', 'local_helpdesk');
                }
                break;
            case 'tickets':
                $resolved = 1;
                include($CFG -> dirroot . '/local/helpdesk/views/viewassignedtickets.php');
                break;
            default:
                throw new Exception('Unexpected value');
        }
    }
} else {
    print_error('errorfindingaction', 'local_helpdesk', $action);
}

echo $OUTPUT -> box_end();

echo $OUTPUT -> footer();