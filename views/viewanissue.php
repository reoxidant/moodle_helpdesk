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

// History of issue

//'<a id="togglehistorylink"
//    href="javascript:togglehistory()">'.
//    get_string(($initialviewmode == 'visibledev') ? 'hide)
//    '</a>&nbsp;-&nbsp;' : '';

$history = $DB -> get_records_select(
    'helpdesk_issueownership', ' issueid = ? ', [$issue -> id], 'timeassigned DESC');
$statehistory = $DB -> get_records_select(
    'helpdesk_state_change', ' issueid = ? ', [$issue -> id], 'timechange ASC');
$showhistorylink =
    (!empty($history) || !empty($statehistory)) ?
        '<a id="togglehistorylink" href="javascript:togglehistory()">'
        . get_string(($initialviewmode === 'visiblediv') ? 'hidehistory' : 'showhistory', 'local_helpdesk') .
        '</a>' : '';

// Start printing.

echo $OUTPUT -> box_start('generalbox', 'bugreport');
?>
    <table style="padding: 5px;" class="helpdesk-issue">
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
        ?>
        <tr style="vertical-align: top;">
            <td style="text-align: right" colspan="4">
                <?php echo $showhistorylink . $showdependancieslink . $showcommentslink . $addcommentlink . $transferlink . $distribute; ?>
            </td>
        </tr>
    </table>
<?= $OUTPUT -> box_end() ?>