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
    $resolvedclause = ' AND
       (status = ' . RESOLVED . ')
    ';
} else {
    $resolvedclause = ' AND
        status <> ' . RESOLVED . '
    ';
}

$sql = '
        SELECT
            i.id,
            i.summary,
            i.datereported,
            i.reportedby,
            i.assignedto,
            i.status,
            i.priority,
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
            ' . $resolvedclause . '        
        GROUP BY
            i.id,
            i.summary,
            i.datereported,
            i.reportedby,
            i.assignedto,
            i.status,
            i.priority,
            u.firstname,
            u.lastname
    ';

$sqlcount = '
        SELECT
            COUNT(*)
        FROM
            {helpdesk_issue} i,
            {user} u
        WHERE
            i.reportedby = u.id AND
            ' . $resolvedclause . '
    ';

$numrecords = $DB->count_records_sql($sqlcount)
?>

    <form name="manageform" action="../view.php" method="post">
        <input type="hidden" name="action" value="updatelist"/>
        <input type="hidden" name="view" value="view"/>
        <input type="hidden" name="screen" value="browse"/>
<?php

//Define table object.

$priority = get_string('priority', 'tracker');
$issuenumber = get_string('issuenumber', 'local_helpdesk');
$summary = get_string('summary', 'local_helpdesk');
$datereported = get_string('datereported', 'local_helpdesk');
$reportedby = get_string('reportedby', 'local_helpdesk');
$assignedto = get_string('assignedto', 'local_helpdesk');
$status = get_string('status', 'local_helpdesk');
$action = '';

if ($resolved) {
    $tablecolumns = array('id', 'summary', 'datereported', 'reportedby', 'assignedto', 'status', 'action');
    $tableheaders = array(
        "<b>$issuenumber</b>",
        "<b>$summary</b>",
        "<b>$datereported</b>",
        "<b>$reportedby</b>",
        "<b>$assignedto</b>",
        "<b>$status</b>",
        "<b>$action</b>"
    );
} else {
    $tablecolumns = array('priority', 'id', 'summary', 'datereported', 'reportedby', 'assignedto', 'status', 'action');
    $tableheaders = array(
        "<b>$priority</b>",
        "<b>$issuenumber</b>",
        "<b>$summary</b>",
        "<b>$datereported</b>",
        "<b>$reportedby</b>",
        "<b>$assignedto</b>",
        "<b>$status</b>",
        "<b>$action</b>"
    );
}

$table = new flexible_table('local-helpdesk-issuelist');
$table -> define_columns($tablecolumns);
$table -> define_headers($tableheaders);

$table -> define_baseurl(new moodle_url('/local/helpdesk/view.php', array('view' => $view, 'screen' => $screen)));

$table -> sortable(true, 'priority', SORT_ASC);
$table -> collapsible(true);
$table -> initialbars(true);

$table -> set_attribute('cellspacing', '0');
$table -> set_attribute('id', 'issues');
$table -> set_attribute('class', 'issuelist');
$table -> set_attribute('width', '100%');

$table -> set_attribute('priority', 'list_priority');
$table -> set_attribute('id', 'list_issue_number');
$table -> set_attribute('summary', 'list_summary');
$table -> set_attribute('datereported', 'timelabel');
$table -> set_attribute('reportedby', 'list_reportedby');
$table -> set_attribute('assignedto', 'list_assignedto');
$table -> set_attribute('status', 'list_status');
$table -> set_attribute('action', 'list_action');

$table -> setup();

$where = $table -> get_sql_where();
$sort = $table -> get_sql_sort();
$table -> pagesize($limit, $numrecords);

if ($sort !== null) {
    $sql .= " ORDER BY $sort";
} else {
    $sql .= ' ORDER BY priority ASC';
}

$issues = $DB -> get_records_sql($sql, null, $table -> get_page_start(), $table -> get_page_size());

$maxpriority = $DB -> get_field_select('helpdesk_issue', 'MAX(priority)', '');

if (!empty($issues)) {
    foreach ($issues as $issue) {

        $issuenumber = "<a href=\"view.php?
                           view=view&amp;
                           issueid={$issue->id}\">{$issue->id}</a>";

        $summary = "<a href=\"view.php?
                       view=view&amp;
                       screen=viewanissue&amp;
                       issueid={$issue->id}\">" . format_string($issue->summary) . '</a>';

        $datereported = date('Y/m/d H:i', $issue->datereported);

        $user = $DB->get_record('user', array('id' => $issue->reportedby));

        $reportedby = fullname($user);

        $assignedto = '';

        $user = $DB->get_record('user', array('id' => $issue->assignedto));

        if (has_capability('local/helpdesk:manage', $context)) {
            $status = $FULLSTATUSKEYS[0 + $issue->status] . '<br/>' .
                html_writer::select(
                    $STATUSKEYS,
                    "status{$issue->id}",
                    0,
                    ['' => 'choose'],
                    ['onchange' => "document.forms['manageform'].schanged{$issue->id}.value = 1;"]
                ) .
                "<input type=\"hidden\" name=\"schanged{$issue->id}\" value=\"0\" />";
        }

        $status =
            '<div class=status_' . $STATUSCODES[$issue->status] . ' 
                  style="width: 110%; height:105%; text-align: center">' . $status .
            '</div>';

        $hasresolution = $issue->status === RESOLVED && !empty($issue->resolution);

        $solution = ($hasresolution) ?
            "<img src=\"" . $OUTPUT->image_url('solution', 'helpdesk') . "\" 
                  height='15' 
                  alt=\"" . get_string('hasresolution', 'local_helpdesk') . "\" />" : '';

        $actions = '';
    }
}


echo ' </form > ';