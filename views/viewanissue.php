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

//$initialviewmode = ($action === 'addcomment') ? 'visible' : 'hidden';
//$initialviewmodeforcss = ($action === 'register' || $action === 'unregister') ? 'visible' : 'hidden';

$issue = $DB -> get_record('helpdesk_issue', ['id' => $issueid]);

if (!$issue) {
    redirect('view.php?view=view&screen=tickets');
}
$issue -> reporter = $DB -> get_record('user', ['id' => $issue -> reportedby]);
$issue -> owner = $DB -> get_record('user', ['id' => $issue -> assignedto]);

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
    </table>
<?= $OUTPUT -> box_end() ?>