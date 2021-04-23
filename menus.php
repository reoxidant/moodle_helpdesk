<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

defined('MOODLE_INTERNAL') || die();

$total_issues = 0;
$total_resolved_issue = 0;

/* if ($screen === 'tickets') {
    //$total_issues = define in $DB->count_records_select
} elseif ($screen === 'work') {
    //$total_issues = define in $DB->count_records_select
} else {
    $total_issues = 0;
    $total_resolved_issue = 0;
}*/

// Render Tabs with options for user.

if (has_capability('local/helpdesk:report', $context)) {
    $rows[0][] = new tabobject('report_issue', 'report_issue.php',
        get_string('new_issue', 'local_helpdesk'));
}

$rows[0][] = new tabobject('view', 'view.php?view=view',
    get_string('view', 'local_helpdesk') . ' (' . $total_issues . ' ' .
    get_string('issues', 'local_helpdesk') . ')');

$rows[0][] = new tabobject('resolved', 'view.php?view=resolved',
    get_string('resolved_plural', 'local_helpdesk') . ' (' . $total_resolved_issue . ' ' .
    get_string('issues', 'local_helpdesk') . ')');

$rows[0][] = new tabobject('profile', 'view.php?view=profile',
    get_string('profile', 'local_helpdesk'));

if (has_capability('local/helpdesk:view_reports', $context)) {
    $rows[0][] = new tabobject('reports', 'view.php?view=reports',
        get_string('reports', 'local_helpdesk'));
}

if (has_capability('local/helpdesk:configure', $context)) {
    $rows[0][] = new tabobject('admin', 'view.php?view=admin',
        get_string('administration', 'local_helpdesk'));
}

// Render Subtabs

$selected = null;
$activated = null;
switch ($view) {
    case 'view' :
        if (!preg_match('/tickets|work|browse|search|view_issue|edit_issue/', $screen)) $screen = 'tickets';
        if (has_capability('local/helpdesk:report', $context)) {
            $rows[1][] = new tabobject('tickets', 'view.php?view=view&amp;screen=tickets', get_string('tickets', 'local_helpdesk'));
        }
        if (has_assigned_issues()){
            $rows[1][] = new tabobject('work', 'view.php?view=view&amp;screen=work', get_string('work', 'local_helpdesk'));
        }
        if(has_capability('local/helpdesk:view_issues', $context)){
            $rows[1][] = new tabobject('browse', 'view.php?view=view&amp;screen=browse', get_string('browse', 'local_helpdesk'));
        }
        $rows[1][] = new tabobject('search', 'view.php?view=view&amp;screen=search', get_string('search', 'local_helpdesk'));
        break;
    case 'resolved' :
        if (!preg_match('/tickets|browse|work/', $screen)) $screen = 'tickets';
        break;
    case 'profile':
        if (!preg_match('/profile|preferences|watches|queries/', $screen)) $screen = 'profile';
        break;
    case 'reports':
        if (!preg_match('/status|evolution|print/', $screen)) $screen = 'status';
        break;
    case 'admin':
        if (!preg_match('/summary|manage_elements|manage_network/', $screen)) $screen = 'summary';
        break;
    default:
}
if (!empty($screen)) {
    $selected = $screen;
    $activated = [$view];
} else $selected = $view;

echo $OUTPUT -> container_start('local-header helpdesk-tabs');
print_tabs($rows, $selected, '', $activated);
echo '<br/>';
echo $OUTPUT->container_end();