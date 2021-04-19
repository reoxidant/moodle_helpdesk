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

// Check optional parameters
$moduleid = optional_param("moduleid", 0, PARAM_INT);
$instanceid = optional_param("instanceid", 0, PARAM_INT);
$issueid = optional_param("issueid", "", PARAM_INT);
$action = optional_param("action", "", PARAM_ALPHA);

if ($moduleid) {
    if (!$cm = get_coursemodule_from_id("helpdesk", $id)) {
        print_error("errorcoursemodid", "helpdesk");
    }
    if (!$course = $DB -> get_record("course", array("id" => $cm -> course))) {
        print_error("errorcoursemisconfigured", "helpdesk");
    }
    if (!$helpdesk = $DB -> get_record("helpdesk", array("id" => $cm -> instance))) {
        print_error("errormoduleincorrect", "helpdesk");
    }
} else {
    if (!$helpdesk = $DB -> get_record("helpdesk", array("id" => $helpdesk -> course))) {
        print_error("errormoduleincorrect", "helpdesk");
    }
    if (!$cm = get_coursemodule_from_instance("helpdesk", $helpdesk -> id)) {
        print_error("errorcoursemodid", "helpdesk");
    }
}

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