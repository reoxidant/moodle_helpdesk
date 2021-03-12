<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG -> dirroot . '/lib/uploadlib.php');

function helpdesk_resolve_screen(){
    global $SESSION;

    $context = context_system::instance();

    $screen = optional_param('screen', @$SESSION->tracker_current_screen, PARAM_ALPHA);

    if(empty($screen)){
        if(has_capability('local/helpdesk:develop', $context)){
            $defaultscreen = 'mywork';
        } elseif (has_capability('local/helpdesk:report', $context)) {
            $defaultscreen = 'mytickets';
        } else {
            $defaultscreen = 'browse';
        }
        $screen = $defaultscreen;
    }
}