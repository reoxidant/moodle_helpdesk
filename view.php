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

$issueid = optional_param('issueid', '', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

$screen = helpdesk_resolve_screen();
$view = helpdesk_resolve_view();

$url = new moodle_url('/local/helpdesk/view.php', ['view' => $view, 'screen' => $screen]);

// Redirect

if ($view === 'view' && (empty($screen) || $screen === 'viewanissue' || $screen === 'editanissue') && empty($issueid)) {
    redirect(new moodle_url('/local/helpdesk/view.php'), ['view' => 'view', 'screen' => 'browse']);
}

if ($view === 'reportanissue') {
    redirect(new moodle_url('/local/helpdesk/view.php'));
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

if ($action !== '') {
    if (($view === 'view')) {
        $result = include($CFG -> dirroot . '/local/helpdesk/views/viewcontroller.php');
    } elseif ($view === 'resolved') {
        $result = include($CFG -> dirroot . '/local/helpdesk/views/viewcontroller.php');
    }
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
                if (!has_any_capability(['local/helpdesk:seeissues', 'local/helpdesk:resolve', 'local/helpdesk:manage'], $context)) {
                    print_error('errornoaccessissue', 'local_helpdesk');
                } else {
                    include($CFG -> dirroot . '/local/helpdesk/views/viewanissue.php');
                }
        }
    }
} elseif ($view === 'resolved') {
    if ($result !== -1) {
        switch ($screen) {
            case 'tickets':
                $resolved = 1;
                include($CFG -> dirroot . '/local/helpdesk/views/viewassignedtickets.php');
                break;
            case 'browse':
                if (!has_capability('local/helpdesk:viewallissues', $context)) {
                    print_error('errornoaccessallissues', 'local_helpdesk');
                } else {
                    $resolved = 1;
                    include($CFG -> dirroot . '/local/helpdesk/views/viewtickets.php');
                }
                break;
        }
    }
} else {
    try {
        //default view
        $resolved = 0;
        include($CFG -> diroot . '/local/helpdesk/views/viewassignedtickets.php');
    } catch (Throwable $e) {
        print_error('errorfindingaction', 'local_helpdesk', $action);
    }
}

echo $OUTPUT -> box_end();

echo $OUTPUT -> footer();