<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

defined('MOODLE_INTERNAL') || die();

/**
 *
 */
const OPEN = 1;
/**
 *
 */
const RESOLVING = 2;
/**
 *
 */
const RESOLVED = 3;

global $STATUSCODES;
global $STATUSKEYS;
global $FULLSTATUSKEYS;

$STATUSCODES = array(
    OPEN => 'open',
    RESOLVING => 'resolving',
    RESOLVED => 'resolved'
);

$STATUSKEYS = helpdesk_get_status_keys();
$FULLSTATUSKEYS = helpdesk_get_status_keys();

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
            if (has_capability('local/helpdesk:report', $context)) {
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

/**
 * @param false $resolved
 * @return int
 */
function helpdesk_has_assigned_issues(bool $resolved = false): int
{
    $select = '
        issueid = ? AND
        assignedto = ?
    ';

    if ($resolved) {
        $select .= '
            AND
            status IN (' . RESOLVED . ')
        ';
    } else {
        $select .= '
            AND
            status NOT IN (' . RESOLVED . ')
        ';
    }

    $search = [];

    return 0;
//    return $DB->count_records_select('tracker_issue', $select, $search);
}

/**
 * @param $data
 * @return StdClass|null
 * @throws dml_exception
 * @throws moodle_exception
 */
function helpdesk_submit_issue_form(&$data)
{
    global $DB, $USER;

    $issue = new StdClass();
    $issue -> summary = $data -> summary;
    $issue -> description = $data -> description_editor['text'];
    $issue -> descriptionformat = $data -> description_editor['format'];
    $issue -> datereported = time();
    $issue -> reportedby = $USER -> id;
    $issue -> status = OPEN;
    $issue -> assignedto = 0;
    $issue -> bywhomid = 0;

    $maxpriority = $DB -> get_field_select('helpdesk_issue', 'MAX(priority)', '');
    $issue -> priority = $maxpriority + 1;

    if ($issue -> id = $DB -> insert_record('helpdesk_issue', $issue)) {
        $data -> issueid = $issue -> id;
        return $issue;
    }

    print_error('errorrecordissue', 'local_helpdesk');
    return null;
}

/**
 * @return array|mixed
 * @throws coding_exception
 */
function helpdesk_get_status_keys()
{
    static $FULLSTATUSKEYS;

    if (!isset($FULLSTATUSKEYS)) {
        $FULLSTATUSKEYS = array(
            OPEN => get_string('open', 'local_helpdesk'),
            RESOLVING => get_string('resolving', 'local_helpdesk'),
            RESOLVED => get_string('resolved', 'local_helpdesk')
        );
    }

    return $FULLSTATUSKEYS;
}

/**
 * @throws dml_exception
 */
function helpdesk_update_priority_stack()
{
    global $DB;

    $sql = '
        UPDATE
            {helpdesk_issue}
        SET
            priority = 0
        WHERE
            status IN (' . RESOLVED . ')
    ';
    $DB -> execute($sql);

    // fetch prioritized by order
    $issues = $DB -> get_records_select('helpdesk_issue', 'priority != 0', null, 'priority', 'id, priority');
    $i = 1;
    if (!empty($issues)) {
        foreach ($issues as $issue) {
            $issue -> priority = $i;
            $DB -> update_record('helpdesk_issue', $issue);
            $i++;
        }
    }
}

/**
 * @throws coding_exception
 */
function helpdesk_can_workon(&$context, $issue = null): bool
{
    global $USER;

    if ($issue) {
        if ($issue -> assignedto === $USER -> id && has_capability('local/helpdesk:resolve', $context)) {
            return true;
        }
    } else if (has_capability('local/helpdesk:resolve', $context)) {
        return true;
    }

    return false;
}

/**
 * @param $context
 * @param $issue
 * @return bool
 * @throws coding_exception
 */
function helpdesk_can_edit(&$context, &$issue): bool
{
    if (has_capability('local/helpdesk:manage', $context)) {
        return true;
    }

    if ($issue -> repotedby === $USER -> id) {
        return true;
    }

    if ($issue -> assgnedto === $USER -> id && has_capability('local/helpdesk:resolve', $context)) {
        return true;
    }

    return false;
}