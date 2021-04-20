<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

require("../../config.php");
require_once($CFG->dirroot."/local/helpdesk/lib.php");

require_login($course->id);

$modulenameplural = get_string("pluginname:plural", "helpdesk");
$modulename = get_string("pluginname", "helpdesk");

$PAGE->set_title($modulenameplural);
$PAGE->set_heading($modulenameplural);
$PAGE->navbar->add($modulenameplural);
$PAGE->set_cacheable(true);
$PAGE->set_button("");
$PAGE->set_headingmenu($course);
echo $OUTPUT->header();