<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
} elseif ($action === 'updateanissues') {
    $issue = new StdClass;

    $issue -> id = required_param('issueid', PARAM_INT);
    $issue -> issueid = $issue -> id;
    $issue -> status = required_param('status', PARAM_INT);
    $issue -> assignedto = required_param('assignedto', PARAM_INT);
    $issue -> summary = required_param('summary', PARAM_TEXT);
    $issue -> description_editor = required_param_array('description_editor', PARAM_CLEANHTML);
    $issue -> description_format = $issue -> description_editor['format'];
    $editoptions = array('maxfiles' => 99);

    $issue -> resolution_editor = required_param_array('resolution_editor', PARAM_CLEANHTML);
    $issue -> resolution_format = $issue -> resolution_editor['format'];

    $issue -> description = file_save_draft_area_files($issue -> description_editor['itemid'], $context -> id, 'local_helpdesk', 'issuedescription', $issue -> id, $editoroptions, $issue -> description_editor['text']);
    $issue -> resolution = file_save_draft_area_files($issue -> resolution_editor['itemid'], $context -> id, 'local_helpdesk', 'issueresolution', $issue -> id, $editoroptions, $issue -> resolution_editor['text']);

    $issue -> datereported = required_param('datereported', PARAM_INT);

    $issue -> bywhomid = $USER -> id;
    $issue -> timeassigned = time();
}
