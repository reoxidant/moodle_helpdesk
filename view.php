<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

require("../../config.php");
require_once($CFG -> dirroot . "/local/helpdesk/lib.php");
require_once($CFG -> dirroot . "/local/helpdesk/locallib.php");

$screen = helpdesk_resolve_screen($cm);
$view = helpdesk_resolve_view();

$url = new moodle_url("/local/helpdesk/view.php", array("id" => $cm -> id, "view" => $view, "screen" => $screen));

if ($view === "view" && (empty($screen) || $screen === "viewanissue" || $screen === "editanissue") && empty($issueid)) {
    redirect(new moodle_url("/local/helpdesk/view.php", array("id" => $cm -> id, "view" => "view", "screen" => "browse")));
}

if ($view === "reportanissue") {
    redirect(new moodle_url("/local/helpdesk/reportissue.php", array("id" => $id)));
}

// Implicit routing.

if ($issueid) {
    $view = "view";
    if (empty($screen)) {
        $screen = "viewanissue";
    }
}

$context = context_system ::instance();
$PAGE -> set_context($context);
$PAGE -> set_title($pluginname);
$PAGE -> set_heading($pluginname);
$PAGE -> set_url($url);

$pluginname = get_string('pluginname', 'local_helpdesk');

$renderer = $PAGE -> get_renderer('helpdesk');

$result = 0;
if ($view == 'view') {
    if ($action != '') {
        $result = include($CFG -> dirroot . '/local/helpdesk/views/view.controller.php');
    }
}

echo $OUTPUT -> header();

echo $OUTPUT -> box_start('', 'helpdesk-view');
echo $renderer -> tabs($view, $screen, null);

echo $OUTPUT -> box_end();

echo $OUTPUT -> footer();