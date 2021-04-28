<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

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
            i.status
        FROM
            {helpdesk_issue} i
        WHERE
            i.reportedby = {$USER->id} AND
            $resolved_clause
        GROUP BY
            i.id,
            i.summary,
            i.datereported,
            i.reportedby,
            i.status
    ";

$sql_count = "
        SELECT
            COUNT(*)
        FROM
            {helpdesk_issue} i
        WHERE
            i.reportedby = {$USER->id} AND
            $resolved_clause
    ";

$num_records = $DB -> count_records_sql($sql_count);
?>

    <form name="manageform" action="view.php" method="post">
        <input type="hidden" name="action" value="updatelist"/>
        <input type="hidden" name="view" value="resolved"/>

<?php

//Define table object.

$priority = get_string('priority', 'local_helpdesk');
$issue_number = get_string('issue_number', 'local_helpdesk');
$summary = get_string('summary', 'local_helpdesk');
$datereported = get_string('date_reported', 'local_helpdesk');
$assigned = get_string('assigned', 'local_helpdesk');
$status = get_string('status', 'local_helpdesk');
$watches = get_string('watches', 'local_helpdesk');
$action = '';

if (!$resolved && has_capability('local/helpdesk:view_priority', $context)) {
    $table_columns = array('priority', 'id', 'summary', 'datereported', 'assigned', 'status', 'watches', 'action');
    $table_headers = array(
        "<b>$priority</b>",
        '',
        "<b>$summary</b>",
        "<b>$datereported</b>",
        "<b>$assigned</b>",
        "<b>$status</b>",
        "<b>$watches</b>",
        "<b>$action</b>"
    );
} else {
    $table_columns = array('id', 'summary', 'datereported', 'assigned', 'status', 'watches', 'action');
    $table_headers = array(
        '',
        "<b>$summary</b>",
        "<b>$datereported</b>",
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

$table -> sortable(true, 'datereported', SORT_DESC);
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
}

$issues = $DB -> get_records_sql($sql, null, $table -> get_page_start(), $table -> get_page_size());
$max_priority = $DB -> get_field_select('helpdesk_issue', 'MAX(priority)', '');

$FULL_STATUS_KEYS = helpdesk_get_status_keys();

if (!empty($issues)) {
    foreach ($issues as $issue) {

        $issue_number = "<a href=\"view.php?view=view&amp;issueid={$issue->id}\">{$issue->id}</a>";

        $summary = "<a href=\"view.php?view=view&amp;screen=view_issue&amp;issueid={$issue->id}\">" . format_string($issue -> summary) . '</a>';

        $datereported = date("Y/m/d H:i, $issue->datereported");

        $user = $DB -> get_record('user', array('id' => $issue -> assigned));

        if (has_capability('local/helpdesk:manage', $context)) {
           $status = $FULL_STATUS_KEYS[0 + $issue->status];
        }

        $status = '<div class=\"status_' . $status_code . '\" style="width: 110%; height:105%; text-align: center">' . $status . '</div>';

        $has_resolution = $issue -> status === RESOLVED && !empty($issue -> resolution);

        $solution = ($has_resolution) ?
            "<img src=\"" . $OUTPUT -> pix_url('solution', 'helpdesk') . "\" 
                  height='15' 
                  alt=\"" . get_string('has_resolution', 'local_helpdesk') . "\" 
            />" : '';

        $actions = '';

        if(
                has_capability('local/helpdesk:manage', $context)
            ||
                has_capability('local/helpdesk:resolve', $context)
        ){
            $actions = "
            <a href=\"view.php?view=resolved&amp;issueid={$issue->id}&screen=edit_issue\" title=\"".get_string('update')."\" >
                <img src =\"".$OUTPUT->pix_url('t/edit', 'core')."\" border=\"0\" />
            </a>";
        }

        if(
            has_capability('local/helpdesk:manage', $context)
        ){
            $actions = "&nbsp;
            <a href=\"view.php?view=resolved&amp;issueid={$issue->id}&action=delete\" title=\"".get_string('delete')."\" >
                <img src =\"".$OUTPUT->pix_url('t/delete', 'core')."\" border=\"0\" />
            </a>";
        }

        if (!$resolved && has_capability('local/helpdesk:view_priority', $context)) {
            $ticker_priority = ($issue->status < RESOLVED) ? $max_priority - $issue->priority + 1 : '';
            $dataset = array();
        } else {
            $dataset = array();
        }
        $table->add_data($dataset);
    }
    $table->finish_html();

} else {

}

echo '</form>';