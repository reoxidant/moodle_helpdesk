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

if (!isloggedin() or isguestuser()) {
    require_login();
    die;
}

$pluginname = get_string('pluginname', 'local_helpdesk');

$context = context_system ::instance();
$PAGE -> set_context($context);
$PAGE -> set_pagelayout('standard');
$PAGE -> navbar -> add($pluginname);
$PAGE -> set_title($pluginname);
$PAGE -> set_heading($pluginname);
$PAGE -> set_url($url);

$renderer = $PAGE -> get_renderer('local_helpdesk');

if (($view === 'view') && $action !== '') {
    $result = include($CFG->dirroot.'/local/helpdesk/views/view_controller.php');
}

echo $OUTPUT -> header();

echo $OUTPUT -> box_start('', 'helpdesk-view');
echo $renderer -> tabs($view, $screen);

if ($view === 'view') {
    switch ($screen) {
        case 'browse':
            $resolved = 0;
            include($CFG->dirroot. '/local/helpdesk/views/view_issues.php');
            break;
    }
}

echo $OUTPUT -> box_end();

echo $OUTPUT -> footer();