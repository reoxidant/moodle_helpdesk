<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

defined('MOODLE_INTERNAL') || die();

//for upload file and use some extensions was deprecated
//require_once($CFG -> dirroot . '/lib/uploadlib.php');
if (isset($CFG -> dirroot)) {
    require_once($CFG->dirroot.'/local/helpdesk/mailtemplatelib.php');
}

// Define helpdesk status

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

// Define enabled status
const ENABLED_POSTED = 1;
const ENABLED_OPEN = 2;
const ENABLED_RESOLVING = 4;
const ENABLED_WAITING = 8;
const ENABLED_RESOLVED = 16;
const ENABLED_ABANDONNED = 32;
const ENABLED_TRANSFERED = 64;
const ENABLED_TESTING = 128;
const ENABLED_PUBLISHED = 256;
const ENABLED_VALIDATED = 512;
const ENABLED_ALL = 1023;

/**
 * @throws coding_exception
 * @throws dml_exception
 */
function helpdesk_resolve_screen(&$cm){
    global $SESSION;

    $context = context_system::instance($cm->id);

    $screen = optional_param('screen', @$SESSION->helpdesk_current_screen, PARAM_ALPHA);

    if(empty($screen) && $context !== null){
        if(has_capability('local/helpdesk:develop', $context)){
            $defaultscreen = 'work';
        } elseif (has_capability('local/helpdesk:report', $context)) {
            $defaultscreen = 'helpdesk';
        } else {
            $defaultscreen = 'browse';
        }
        $screen = $defaultscreen;
    }

    $SESSION->helpdesk_current_screen = $screen;
    return $screen;
}

function helpdesk_resolve_view(){
    global $SESSION;

    $view = optional_param("view", @$SESSION->helpdesk_current_view, PARAM_ALPHA);

    if(empty($view)){
        $defaultview = "view";
        $view = $defaultview;
    }

    $SESSION->helpdesk_current_view = $view;
    return $view;
}