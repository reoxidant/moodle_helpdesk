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

$initialviewmode = ($action === 'addcomment') ? 'visible' : 'hidden';
$initialviewmodeforcss = ($action === 'register' || $action === 'unregister') ? 'visible' : 'hidden';

$issue = $DB->get_record('helpdesk_issue', ['id' => $issueid]);

if (!$issue) {
    redirect('view.php?view=view&screen=tickets');
}

$issue->reported = $DB->get_record('user', ['id' => $issue->reportedby]);
$issue->owner = $DB->get_record('user', ['id' => $issue->assignedto]);