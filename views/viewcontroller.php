<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

if ($action === 'updatelist') {
    $keys = array_keys($_POST);

// 0 = "action"
// 1 = "view"
// 2 = "assignedto2"
// 3 = "status2"
// 4 = "assignedto1"
// 5 = "status1"
// 6 = "go_btn"

// 0 = "id"
// 1 = "what"
// 2 = "view"
// 3 = "screen"
// 4 = "status22"
// 5 = "schanged22"
// 6 = "go_btn"

    $statuskeys = preg_grep('/status./' , $keys);              // filter out only the status
    $assignedtokeys = preg_grep('/assignedto./' , $keys);
    $newassignedtokeys = preg_grep('/assignedtoi./', $keys);
    foreach ($statuskeys as $akey) {
        $issueid = str_replace('status', '', $akey);
        $haschanged = optional_param('schanged' . $issueid, 0, PARAM_INT);
        if ($haschanged) {
            $issue = new StdClass;
            $issue -> id = $issueid;
            $issue -> status = required_param($akey, PARAM_INT);
            $oldstatus = $DB -> get_field('helpdesk_issue', 'status', ['id' => $issue -> id]);
            $DB -> update_record('helpdesk_issue', $issue);
            // MARK: check status changing and send notifications
            if ($oldstatus !== $issue -> status) {
                $stc = new StdClass;
                $stc -> usedid = $USER -> id;
                $stc -> issueid = $issue -> id;
                $stc -> timechange = time();
                $stc -> statusform = $oldstatus;
                $stc -> statusto = $issue -> status;
                $DB -> insert_record('helpdesk_state_change', $stc);
            }
        }
    }

    //always add a record for history

    foreach($assignedtokeys as $akey) {
        $issueid = str_replace('assignedto', '', $akey);
        // new ownership is triggered only when a change occured
        $haschanged = optional_param('changed'.$issueid, 0, PARAM_INT);
        if ($haschanged) {
            // save old assignement in history
            console_log($haschanged);
        }
    }
}
