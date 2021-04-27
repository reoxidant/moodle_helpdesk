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
$table -> set_attribute('class', 'list_issue');
$table -> set_attribute('width', '100%');

$table -> set_attribute('priority', 'list_priority');
$table -> set_attribute('id', 'list_issue_number');
$table -> set_attribute('summary', 'list_summary');
$table -> set_attribute('datereported', 'time_label');
$table -> set_attribute('reportedby', 'list_reportedby');
$table -> set_attribute('assigned', 'list_assigned');
$table -> set_attribute('watches', 'list_watches');
$table -> set_attribute('status', 'list_status');
$table -> set_attribute('action', 'list_action');

$table -> setup();

$where = $table -> get_sql_where();
$sort = $table -> get_sql_sort();
$table -> pagesize($limit, $num_records);

if ($sort !== null) {
    $sql .= " ORDER BY $sort";
} else {
    $sql .= ' ORDER BY priority ASC';
}

$issue = $DB -> get_records_sql($sql, null, $table -> get_page_start(), $table -> get_page_size());

$max_priority = $DB -> get_field_select('helpdesk_issue', 'MAX(priority)', '');

if (!empty($issues)) {
    foreach ($issues as $issue) {

        $issue_number = "<a href=\"view.php?view=view&amp;issueid={$issue->id}\">{$issue->id}</a>";

        $summary = "<a href=\"view.php?view=view&amp;screen=view_issue&amp;issueid={$issue->id}\">" . format_string($issue -> summary) . '</a>';

        $datereported = date("Y/m/d H:i, $issue->datereported");

        $user = $DB -> get_record('user', array('id' => $issue -> reportedby));

        $reportedby = fullname($user);

        $assigned = '';

        $user = $DB -> get_record('user', array('id' => $issue -> assigned));

        $status_code = $STATUS_CODES[$issue -> status];

        $status = '<div class=\"status_' . $status_code . '\" style="width: 110%; height:105%; text-align: center">' . $status . '</div>';

        $has_resolution = $issue -> status === RESOLVED && !empty($issue -> resolution);

        $solution = ($has_resolution) ?
            "<img src=\"" . $OUTPUT -> pix_url('solution', 'helpdesk') . "\" 
                  height='15' 
                  alt=\"" . get_string('has_resolution', 'local_helpdesk') . "\" 
            />" : '';

        $actions = '';
    }

    if (has_capability('local/helpdesk:manage', $context) || has_capability('local/helpdesk:resolve', $context)) {
        $actions =
            "<a href=\"view.php?view=view&amp;issueid=" . $issue -> id . "&screen=edit_issue\" title = \"" . get_string('update') . "\">
                <img src=\"" . $OUTPUT -> pix_url('t/edit', 'core') . "\" border=\"0\" />
            </a>";
    }
}


echo ' </form > ';