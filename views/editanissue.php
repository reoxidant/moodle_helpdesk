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

$OUTPUT -> box_start('generalbox', 'bugreport');
$issue = $DB -> get_record('helpdesk_issue', ['id' => $issueid]);
$issue -> reporter = $DB -> get_record('user', ['id' => $issue -> reportedby]);

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
                <td style="text-align: right; width=25%" class="helpdesk-issue-param">
                    <b><?php print_string('issuenumber', 'local_helpdesk') ?>:</b></td>
                <td style="width:25%"><?= $issueid ?></td>
                <td style="text-align: right; width=22%" class="helpdesk-issue-param">
                    <b>Выбор категории:</b>
                </td>
                <td style="width:28%"></td>
            </tr>
            <tr>
                <td style="text-align: right; width: 25%" class="helpdesk-issue-param">
                    <b><?php print_string('reportedby', 'local_helpdesk'); ?>:</b>
                    <br/>
                </td>
                <td style="width: 25%">
                    <?= fullname($issue -> reporter) ?>
                </td>
                <td style="text-align: right; width: 22%" class="helpdesk-issue-param">
                    <b><?php print_string('datereported', 'local_helpdesk') ?>:</b>
                </td>
                <td style="width: 28%">
                    <?= userdate($issue -> datereported) ?>
                    <input type="hidden" name="datereported" value="<?php p($issue -> datereported) ?>">
                </td>
            </tr>
            <tr>
                <td style="text-align: right; width: 25%" class="helpdesk-issue-param">
                    <b><?php print_string('assignedto', 'local_helpdesk') ?>:</b><br/>
                </td>
                <td style="width: 25%;">
                    <?php
                    $resolvers = helpdesk_getresolvers($context);
                    if ($resolvers) {
                        foreach ($resolvers as $resolver) {
                            $resolversmenu[$resolver -> id] = fullname($resolver);
                        }
                        echo html_writer ::select($resolversmenu, 'assignedto', @$issue -> assignedto);
                    } else {
                        print_string('noresolvers', 'helpdesk_local');
                        echo '<input type="hidden" name="assignedto" value="0" />';
                    }
                    ?>
                </td>
                <td style="text-align: right; width: 22%" class="helpdesk-issue-param">
                    <b><?php print_string('status', 'helpdesk_local') ?>:</b>
                </td>
                <td style="width: 28%;" class="<?= 'status_' . $STATUSCODES[$issue -> status] ?>">
                    <?= html_writer ::select(helpdesk_get_status_keys(), 'status', $issue -> status) ?>
                </td>
            </tr>
            <tr>
                <td style="text-align: right; width: 25%" class="helpdesk-issue-param">
                    <b><?php print_string('summary', 'helpdesk_local'); ?></b>
                </td>
                <td colspan="3" style="vertical-align:center; text-align:left; width: 75%;">
                    <label><input type="text" name="summary" size="70" value="<?= $issue -> summary ?>"></label>
                </td>
            </tr>
            <tr style="vertical-align: top;">
                <td style="text-align: right" height="25%" class="helpdesk-issue-param">
                    <b><?php print_string('description') ?>:</b>
                </td>
                <td style="text-align: left; width: 75%" colspan="3">
                    <?php
                    $attributes = ['id' => 'id_description', 'name' => 'description_editor'];
                    $values = [
                        'text' => $issue -> description_editor['text'],
                        'format' => $issue -> description_editor['format'],
                        'itemid' => $issue -> description_editor['itemid']
                    ];
                    $options = [
                        'maxfiles' => 99,
                        'maxbytes' => $CFG -> maxbytes,
                        'context' => $context
                    ];
                    echo helpdesk_print_direct_editor($attributes, $values, $options);
                    ?>
                </td>
            </tr>
        </table>
    </form>

    <?php
    $OUTPUT -> box_end();
    ?>
</div>
