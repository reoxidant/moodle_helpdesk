<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

defined('MOODLE_INTERNAL') || die();

global $OUTPUT, $DB, $USER;

$str = '';
$context = context_system ::instance();


if ($screen === 'tickets') {
    $select = 'status <> ' . RESOLVED . ' AND reportedby = ? ';
    $totalissues = $DB -> count_records_select('helpdesk_issue', $select, [$USER -> id]);

    $select = 'status = ' . RESOLVED . ' AND reportedby = ? ';
    $totalresolvedissues = $DB -> count_records_select('helpdesk_issue', $select, [$USER -> id]);
} elseif ($screen === 'work') {
    $select = 'status <> ' . RESOLVED . ' AND assignedto = ? ';
    $totalissues = $DB -> count_records_select('helpdesk_issue', $select, [$USER -> id]);

    $select = 'status = ' . RESOLVED . ' AND assignedto = ? ';
    $totalresolvedissues = $DB -> count_records_select('helpdesk_issue', $select, [$USER -> id]);
} else {
    $select = 'status <> ' . RESOLVED;
    $totalissues = $DB -> count_records_select('helpdesk_issue', $select);

    $select = 'status = ' . RESOLVED;
    $totalresolvedissues = $DB -> count_records_select('helpdesk_issue', $select);
}

// Render Tabs with options for user.

if ($context === null) {
    die('context is null');
}

if (has_capability('local/helpdesk:report', $context)) {
    $rows[0][] = new tabobject('reportanissue', 'reportissue.php', get_string('newissue', 'local_helpdesk'));
}

$rows[0][] = new tabobject('view', 'view.php?view=view',
    get_string('view', 'local_helpdesk') . ' (' . $totalissues . ' ' .
    get_string('issues', 'local_helpdesk') . ')'
);

$rows[0][] = new tabobject('resolved', 'view.php?view=resolved',
    get_string('resolvedplural', 'local_helpdesk') . ' (' . $totalresolvedissue . ' ' .
    get_string('issues', 'local_helpdesk') . ')'
);

$rows[0][] = new tabobject('profile', 'view.php?view=profile', get_string('profile', 'local_helpdesk'));

if (has_capability('local/helpdesk:configure', $context)) {
    $rows[0][] = new tabobject('admin', 'view.php?view=admin', get_string('administration', 'local_helpdesk'));
}

// Render Subtabs

$selected = null;
$activated = null;
switch ($view) {
    case 'view' :
        if (!preg_match('/tickets|work|browse|search|viewanissue|editanissue/', $screen)) $screen = 'tickets';
        if (has_capability('local/helpdesk:report', $context)) {
            $rows[1][] = new tabobject('tickets', 'view.php?view=view&amp;screen=tickets', get_string('tickets', 'local_helpdesk'));
        }
        if (helpdesk_has_assigned_issues()) {
            $rows[1][] = new tabobject('work', 'view.php?view=view&amp;screen=work', get_string('work', 'local_helpdesk'));
        }
        if (has_capability('local/helpdesk:viewallissues', $context)) {
            $rows[1][] = new tabobject('browse', 'view.php?view=view&amp;screen=browse', get_string('browse', 'local_helpdesk'));
        }
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
        if (!preg_match('/summary|manageelements|managenetwork/', $screen)) $screen = 'summary';
        break;
    default:
}
if (!empty($screen)) {
    $selected = $screen;
    $activated = [$view];
} else {
    $selected = $view;
}

echo $OUTPUT -> container_start('local-header helpdesk-tabs');
print_tabs($rows, $selected, '', $activated);
echo '<br/>';
echo $OUTPUT -> container_end();