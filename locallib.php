<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

defined('MOODLE_INTERNAL') || die();

const POSTED = 0;
const OPEN = 1;
const RESOLVING = 2;
const WAITING = 3;
const RESOLVED = 4;
const ABANDONNED = 5;
const TRANSFERED = 6;
const TESTING = 7;
const PUBLISHED = 8;
const VALIDATED = 9;

global $STATUS_CODES;

$STATUS_CODES = array(
    POSTED => 'posted',
    OPEN => 'open',
    RESOLVING => 'resolving',
    WAITING => 'waiting',
    RESOLVED => 'resolved',
    ABANDONNED => 'abandonned',
    TRANSFERED => 'transfered',
    TESTING => 'testing',
    PUBLISHED => 'published',
    VALIDATED => 'validated'
);

/**
 * @return array|false|float|int|mixed|string|null
 * @throws coding_exception
 * @throws dml_exception
 */
function helpdesk_resolve_screen()
{
    global $SESSION;

    $screen = optional_param('screen', @$SESSION -> helpdesk_current_screen, PARAM_ALPHA);

    $context = context_system ::instance();

    if (empty($screen)) {
        if ($context !== null) {
            if (has_capability('local/helpdesk:develop', $context)) {
                $defaultscreen = 'work';
            } elseif (has_capability('local/helpdesk:report', $context)) {
                $defaultscreen = 'tickets';
            }
        } else {
            $defaultscreen = 'browse';
        }
        $screen = $defaultscreen;
    }

    $SESSION -> helpdesk_current_screen = $screen;
    return $screen;
}

/**
 * @return array|false|float|int|mixed|string|null
 * @throws coding_exception
 */
function helpdesk_resolve_view()
{
    global $SESSION;

    $view = optional_param('view', @$SESSION -> helpdesk_current_view, PARAM_ALPHA);

    if (empty($view)) {
        $defaultview = 'view';
        $view = $defaultview;
    }

    $SESSION -> helpdesk_current_view = $view;
    return $view;
}

function has_assigned_issues($resolved = false): int
{
    global $DB, $USER;

    $select = '
        issueid = ? AND
        assignedto = ?
    ';

    if ($resolved) {
        $select .= '
            AND
            status IN (' . RESOLVED . ',' . ABANDONNED . ',' . VALIDATED . ')
        ';
    } else {
        $select .= '
            AND
            status NOT IN (' . RESOLVED . ',' . ABANDONNED . ',' . VALIDATED . ')
        ';
    }

    $search = [];

    return 0;
//    return $DB->count_records_select('tracker_issue', $select, $search);
}

function helpdesk_submit_issue_form(&$data): StdClass
{
    global $CFG, $DB, $USER;

    $issue = new StdClass();
    $issue -> datereported = time();
    $issue -> summary = $data -> summary;
    $issue -> description = $data -> description_editor['text'];
    $issue -> descriptionformat = $data -> description_editor['format'];
    $issue -> status = POSTED;
    $issue -> reportedby = $USER -> id;

    if ($issue -> id = $DB -> insert_record('helpdesk_issue', $issue)) {
        $data -> issueid = $issue -> id;
        return $issue;
    }

    print_error('error_record_issue', 'local_helpdesk');
}