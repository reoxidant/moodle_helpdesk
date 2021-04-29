<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
} elseif ($action == 'update_issues') {
    $issue = new StdClass;

    $issue -> id = required_param('issueid', PARAM_INT);
    $issue -> issueid = $issue -> id;
    $issue -> status = required_param('status', PARAM_INT);
    $issue -> assigned = required_param('assigned', PARAM_INT);
    $issue -> summary = required_param('summary', PARAM_TEXT);
    $issue -> description_editor = required_param_array('description_editor', PARAM_CLEANHTML);
    $issue -> description_format = $issue -> description_editor['format'];
    $edit_options = array('maxfiles' => 99);

    $issue -> resolution_editor = required_param_array('resolution_editor', PARAM_CLEANHTML);
    $issue -> resolution_format = $issue -> resolution_editor['format'];

    $issue -> description = file_save_draft_area_files($issue -> description_editor['item_id'], $context -> id, 'mod_tracker', 'issue_description', $issue -> id, $editoroptions, $issue -> description_editor['text']);
    $issue -> resolution = file_save_draft_area_files($issue -> resolution_editor['item_id'], $context -> id, 'mod_tracker', 'issue_resolution', $issue -> id, $editoroptions, $issue -> resolution_editor['text']);

    $issue -> date_reported = required_param('datereported', PARAM_INT);
    $issue -> tracker_id = $tracker -> id;

    $issue -> bywhomid = $USER -> id;
    $issue -> time_assigned = time();
}
