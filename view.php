<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

require("../../config.php");
require_once($CFG -> dirroot . "/local/helpdesk/locallib.php");

$screen = "mywork";
$view = "view";

$url = new moodle_url("/local/helpdesk/view.php", array('view'=> $view, 'screen' => $screen));

require_login();

$context = context_system ::instance();
$PAGE -> set_context($context);
$PAGE -> set_title($pluginname);
$PAGE -> set_heading($pluginname);
$PAGE -> set_url($url);

$pluginname = get_string('pluginname', 'local_helpdesk');

$renderer = $PAGE->get_renderer('helpdesk');

$result = 0;
if($view == 'view'){
    if($action != ''){
        $result = include($CFG->dirroot.'/local/helpdesk/views/view.controller.php');
    }
}

echo $OUTPUT -> header();

echo $OUTPUT -> box_start('','helpdesk-view');
echo $renderer->tabs($view, $screen, null);

echo $OUTPUT -> box_end();

echo $OUTPUT -> footer();