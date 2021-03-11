<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

require("../../config.php");
require_once($CFG->dirroot."/local/helpdesk/locallib.php");

require_login();

$url = new moodle_url("/local/helpdesk/view.php");

$PAGE->set_url($url);

$pluginname = get_string('pluginname', 'local_helpdesk');
$PAGE->set_title($pluginname);
$PAGE->set_heading($pluginname);

echo $OUTPUT->header();
echo $OUTPUT->footer();