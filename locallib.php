<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

defined('MOODLE_INTERNAL') || die();

const RESOLVED = 4;
const ABANDONNED = 5;
const VALIDATED = 9;

/**
 * @return array|false|float|int|mixed|string|null
 * @throws coding_exception
 * @throws dml_exception
 */
function helpdesk_resolve_screen(){
    global $SESSION;

    $screen = optional_param('screen', @$SESSION->helpdesk_current_screen, PARAM_ALPHA);

    $context = context_system::instance();

    if(empty($screen)){
        if ($context !== null) {
            if(has_capability('local/helpdesk:develop', $context)){
                $defaultscreen = 'work';
            } elseif (has_capability('local/helpdesk:report', $context)) {
                $defaultscreen = 'tickets';
            }
        } else {
            $defaultscreen = 'browse';
        }
        $screen = $defaultscreen;
    }

    $SESSION->helpdesk_current_screen = $screen;
    return $screen;
}

/**
 * @return array|false|float|int|mixed|string|null
 * @throws coding_exception
 */
function helpdesk_resolve_view(){
    global $SESSION;

    $view = optional_param('view', @$SESSION->helpdesk_current_view, PARAM_ALPHA);

    if(empty($view)){
        $defaultview = 'view';
        $view = $defaultview;
    }

    $SESSION->helpdesk_current_view = $view;
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
            status IN ('.RESOLVED.','.ABANDONNED.','.VALIDATED.')
        ';
    } else {
        $select .= '
            AND
            status NOT IN ('.RESOLVED.','.ABANDONNED.','.VALIDATED.')
        ';
    }

    $search = [];

    return 0;
//    return $DB->count_records_select('tracker_issue', $select, $search);
}