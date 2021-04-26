<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from view.php in mod/tracker
}

global $CFG, $DB;

require_once($CFG -> libdir . '/tablelib.php');

$limit = 20;
$page = optional_param('page', 1, PARAM_INT);

if ($page <= 0) {
    $page = 1;
}

if ($resolved) {
    $resolved_clause = ' AND
       (status = ' . RESOLVED . ' OR
       status = ' . ABANDONNED . ')
    ';
} else {
    $resolved_clause = ' AND
        status <> ' . RESOLVED . ' AND
        status <> ' . ABANDONNED . '
    ';
}

$sql = "
        SELECT
            i.id,
            i.summary,
            i.datereported,
            i.reportedby,
            i.status,
            u.firstname firstname,
            u.lastname lastname
        FROM
            {helpdesk_issue} i
        LEFT JOIN
            {user} u
        ON
            i.reportedby = u.id
        WHERE
            i.reportedby = u.id AND
            $resolved_clause
        GROUP BY
            i.id,
            i.summary,
            i.datereported,
            i.reportedby,
            i.status,
            u.firstname,
            u.lastname
    ";

$sql_count = "
        SELECT
            COUNT(*)
        FROM
            {helpdesk_issue} i,
            {user} u
        WHERE
            i.reportedby = u.id AND
            $resolved_clause
    ";

$num_records = $DB -> count_records_sql($sql_count)
?>

    <form name="manageform" action="view.php" method="post">
        <input type="hidden" name="action" value="updatelist"/>
        <input type="hidden" name="view" value="view"/>
        <input type="hidden" name="screen" value="browse"/>
<?php

//Define table object.

$priority = get_string('priority', 'tracker');
$issue_number = get_string('issue_number', 'local_helpdesk');
$summary = get_string('summary', 'local_helpdesk');
$datereported = get_string('date_reported', 'local_helpdesk');
$reportedby = get_string('reported', 'local_helpdesk');
$assigned = get_string('assigned', 'local_helpdesk');
$status = get_string('status', 'local_helpdesk');
$watches = get_string('watches', 'local_helpdesk');
$action = '';

if ($resolved) {
    $table_columns = array('id', 'summary', 'datereported', 'reportedby', 'assigned', 'status', 'watches', 'action');
    $table_headers = array(
        "<b>$issue_number</b>",
        "<b>$summary</b>",
        "<b>$datereported</b>",
        "<b>$reportedby</b>",
        "<b>$assigned</b>",
        "<b>$status</b>",
        "<b>$watches</b>",
        "<b>$action</b>"
    );
} else {
    $table_columns = array('priority', 'id', 'summary', 'datereported', 'reportedby', 'assigned', 'status', 'watches', 'action');
    $table_headers = array(
        "<b>$priority</b>",
        "<b>$issue_number</b>",
        "<b>$summary</b>",
        "<b>$datereported</b>",
        "<b>$reportedby</b>",
        "<b>$assigned</b>",
        "<b>$status</b>",
        "<b>$watches</b>",
        "<b>$action</b>"
    );
}

$table = new flexible_table('local-helpdesk-issue-list');
$table -> define_columns($table_columns);
$table -> define_headers($table_headers);

$table -> define_baseurl(new moodle_url('/local/helpdesk/view.php', array('view' => $view, 'screen' => $screen)));

$table -> sortable(true, 'priority', SORT_ASC);
$table -> collapsible(true);
$table -> initialbars(true);

$table -> set_attribute('cellspacing', '0');
$table -> set_attribute('id', 'issues');
$table -> set_attribute('class', 'issue_list');
$table -> set_attribute('width', '100%');


echo '</form>';