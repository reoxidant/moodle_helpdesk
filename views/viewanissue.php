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

$initialviewmode = ($action === 'addcomment') ? 'visiblediv' : 'hiddendiv';
$initialviewmodeforcss = ($action === 'register' || $action === 'unregister') ? 'visiblediv' : 'hiddendiv';

$issue = $DB -> get_record('helpdesk_issue', ['id' => $issueid]);

if (!$issue) {
    redirect('view.php?view=view&screen=tickets');
}
$issue -> reporter = $DB -> get_record('user', ['id' => $issue -> reportedby]);
$issue -> owner = $DB -> get_record('user', ['id' => $issue -> assignedto]);

$history = $DB -> get_records_select(
    'helpdesk_issueownership', ' issueid = ? ', [$issue -> id], 'timeassigned DESC');
$statehistory = $DB -> get_records_select(
    'helpdesk_state_change', ' issueid = ? ', [$issue -> id], 'timechange ASC');
$showhistorylink =
    (!empty($history) || !empty($statehistory)) ?
        '<a id="togglehistorylink" href="javascript:togglehistory()">'
        . get_string(($initialviewmode === 'visiblediv') ? 'hidehistory' : 'showhistory', 'local_helpdesk') .
        '</a>&nbsp;-&nbsp;' : '';

// Start printing.

echo $OUTPUT -> box_start('generalbox', 'bugreport');
?>
    <table style="padding: 5px;" class="helpdesk-issue">
        <script type="text/javascript">
            let showhistory = "<?php print_string('showhistory', 'local_helpdesk')?>";
            let hidehistory = "<?php print_string('hidehistory', 'local_helpdesk')?>";
        </script>
        <?php
        if ($issue -> status < OPEN && helpdesk_can_workon($context, $issue)) {
            // If I can resolve and I have seen, the bug is open
            $oldstatus = $issue -> status;
            $issue -> status = OPEN;
            $DB -> set_field('helpdesk_issue', 'status', OPEN, ['id' => $issueid]);
            // log state change
            $stc = new StdClass;
            $stc -> userid = $USER -> id;
            $stc -> issueid = $issue -> id;
            $stc -> timechange = time();
            $stc -> statusfrom = $oldstatus;
            $stc -> statusto = $issue -> status;
            $DB -> insert_record('helpdesk_state_change', $stc);
        }
        if (helpdesk_can_edit($context, $issue)) {
            echo $renderer -> edit_link($issue);
        }

        echo $renderer -> core_issue($issue);

        //Show Comments

        $showcommentslink = '';
        $addcommentlink = '';
        $commentscount = $DB -> count_records('helpdesk_issuecomment', ['issueid' => $issue -> id]);

        if (has_capability('local/helpdesk:comment', $context)) {
            $addcommentlink = '<a href="addcomment.php?issueid='.$issueid.'">' . get_string('addcomment', 'local_helpdesk') . '</a>';
        }

        ?>
        <tr style="vertical-align: top;">
            <td style="text-align: right" colspan="4">
                <?= $showhistorylink . $addcommentlink?>
            </td>
        </tr>

        <?php
        if ($showhistorylink) {
            echo $renderer -> history($history, $statehistory, $initialviewmode);
        }
        ?>
    </table>
<?= $OUTPUT -> box_end() ?>