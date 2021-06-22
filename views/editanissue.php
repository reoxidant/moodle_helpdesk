<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * ${PLUGINNAME} file description here.
 *
 * @package    ${PLUGINNAME}
 * @copyright  2021 SysBind Ltd. <service@sysbind.co.il>
 * @auther     vshapovalov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// It must be included from view.php in local/helpdesk

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

global $OUTPUT;

$OUTPUT->box_start('generalbox', 'bugreport');
$issue = $DB -> get_record('helpdesk_issue', ['id' => $issueid]);
$issue -> reporter = $DB -> get_record('helpdesk_issue', ['id' => $issue -> reportedby]);

$editoroptions = ['trusttext' => true, 'subdirs' => true, 'maxfiles' => 99, 'maxbytes' => $CFG -> maxbytes, 'context' => $context];
$issue = file_prepare_standard_editor($issue, 'description', $editoroptions, $context, 'local_helpdesk', $issue -> id);
$issue = file_prepare_standard_editor($issue, 'resolution', $editoroptions, $context, 'local_helpdesk', $issue -> id);

?>

    <div style="text-align: center;">
        <form action="local/helpdesk/view.php" name="editissue" method="post">
            <input type="hidden" name="issueid" value="<?php p($issueid) ?>"/>
            <input type="hidden" name="view" value="view"/>
            <input type="hidden" name="screen" value="viewanissue"/>
            <input type="hidden" name="action" value="updateanissue"/>
            <table style="padding: 5px;" class="helpdesk-issue-editor">
                <?php
                // Opens the issue if I have capability to resolve
                if (helpdesk_can_edit($context, $issue)) {
                    if ($issue -> status < OPEN) {
                        $issue -> status = OPEN;
                        $DB -> set_field('helpdesk_issue', 'status', OPEN, ['id' => $issueid]);
                    }

                    ?>
                    <tr>
                        <td colspan="4" style="text-align: right">
                            <form method="POST"
                                  action="local/helpdesk/view.php?view=view&screen=viewanissue&issueid=<?= $issue -> id ?>">
                                <input type="submit" name="go_btn"
                                       value="<?php print_string('turneditingoff', 'local_helpdesk') ?>">
                            </form>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td style="text-align: right; width=25%" class="helpdesk-issue-param"><b><?php print_string('issuenumber', 'local_helpdesk')?>:</b></td>
                    <td style="width:25%">
                        <?= $issueid ?>
                    </td>
                </tr>
            </table>
        </form>

        <?php
        $OUTPUT->box_end();
        ?>
    </div>
