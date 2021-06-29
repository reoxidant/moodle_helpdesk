<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

if ($action === 'updateanissue') {

    //action the editanissue form

    $issue = new StdClass;

    $issue -> id = required_param('issueid', PARAM_INT);
    $issue -> issueid = $issue -> id;
    $issue -> status = required_param('status', PARAM_INT);
    $issue -> assignedto = required_param('assignedto', PARAM_INT);
    $issue -> summary = required_param('summary', PARAM_TEXT);
    $issue -> description_editor = required_param_array('description_editor', PARAM_CLEANHTML);
    $issue -> descriptionformat = $issue -> description_editor['format'];
    $editoroptions = ['maxfiles' => 99, 'maxbytes' => $CFG -> maxbytes, 'context' => $context];

    $issue->resolution_editor = required_param_array('resolution_editor', PARAM_CLEANHTML);
} elseif ($action === 'updatelist') {
    $keys = array_keys($_POST);
    $statuskeys = preg_grep('/status./', $keys);              // filter out only the status
    $assignedtokeys = preg_grep('/assignedto./', $keys);
    $newassignedtokeys = preg_grep('/assignedtoi./', $keys);
    foreach ($statuskeys as $akey) {
        $issueid = str_replace('status', '', $akey);
        $haschanged = optional_param('schanged' . $issueid, 0, PARAM_INT);
        $status = required_param($akey, PARAM_INT);

        //Direct on new tab only resolve tickets
        if ($status !== 3) {
            $view = 'view';
        }

        if ($haschanged) {
            $issue = new StdClass;
            $issue -> id = $issueid;
            $issue -> status = $status;
            $oldstatus = $DB -> get_field('helpdesk_issue', 'status', ['id' => $issue -> id]);
            $DB -> update_record('helpdesk_issue', $issue);
            // MARK: check status changing and send notifications
            if ($oldstatus !== $issue -> status) {
                $stc = new StdClass;
                $stc -> userid = $USER -> id;
                $stc -> issueid = $issue -> id;
                $stc -> timechange = time();
                $stc -> statusform = $oldstatus;
                $stc -> statusto = $issue -> status;
                $DB -> insert_record('helpdesk_state_change', $stc);
            }
        }
    }

    // always add a record for history
    foreach($assignedtokeys as $akey) {
        $issueid = str_replace('assignedto', '', $akey);
        // new ownershop is triggered only when a change occured
        $haschanged = optional_param('changed'.$issueid, 0, PARAM_INT);
        if($haschanged) {
            //save old assignment in history
            $oldassign = $DB->get_record('helpdesk_issue', ['id' => $issueid]);
            if($oldassign->assignedto != 0) {
                $ownership = new StdClass;
                $ownership -> issueid = $issueid;
            }
        }
    }

    helpdesk_update_priority_stack();
}
