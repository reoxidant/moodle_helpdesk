<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

if (!defined("MOODLE_INTERNAL")) {
    die("Direct access to this script is forbidden.");    // It must be included from view.php in mod/tracker
}

require_once($CFG->libdir.'/tablelib.php');

?>

<form name="manageform" action="view.php" method="post">
    <input type="hidden" name="action" value="updatelist" />
    <input type="hidden" name="view" value="view" />
    <input type="hidden" name="screen" value="browse" />
<?php

$table_columns = array('id', 'summary', 'date_reported', 'reported', 'assigned', 'status', 'watches', 'transfered', 'action');

$priority = get_string("priority", "helpdesk");
$issue_number = get_string("issue_number", "helpdesk");
$summary = get_string("summary", "helpdesk");
$date_reported = get_string("date_reported", "helpdesk");
$reported = get_string('reported', "helpdesk");
$assigned = get_string('assigned', "helpdesk");
$status = get_string('status', "helpdesk");
$watches = get_string('watches', "helpdesk");

echo '</form>';