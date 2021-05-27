<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class local_helpdesk_renderer
 */
class local_helpdesk_renderer extends plugin_renderer_base
{
    /**
     * @param String $view
     * @param String $screen
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    final public function tabs(string $view, string $screen): string
    {
        global $OUTPUT, $DB, $USER;

        $str = '';
        $context = context_system ::instance();

        if ($screen === 'tickets') {
            $select = 'status <> ' . RESOLVED . ' AND reportedby = ? ';
            $totalissues = $DB -> count_records_select('helpdesk_issue', $select, [$USER -> id]);

            $select = 'status = ' . RESOLVED . ' AND reportedby = ? ';
            $totalresolvedissues = $DB -> count_records_select('helpdesk_issue', $select, [$USER -> id]);
        } elseif ($screen === 'work') {
            $select = 'status <> ' . RESOLVED . ' AND assignedto = ? ';
            $totalissues = $DB -> count_records_select('helpdesk_issue', $select, [$USER -> id]);

            $select = 'status = ' . RESOLVED . ' AND assignedto = ? ';
            $totalresolvedissues = $DB -> count_records_select('helpdesk_issue', $select, [$USER -> id]);
        } else {
            $select = 'status <> ' . RESOLVED;
            $totalissues = $DB -> count_records_select('helpdesk_issue', $select);

            $select = 'status = ' . RESOLVED;
            $totalresolvedissues = $DB -> count_records_select('helpdesk_issue', $select);
        }

        // Render Tabs with options for user.

        if ($context === null) {
            die('context is null');
        }

        if (has_capability('local/helpdesk:report', $context)) {
            $rows[0][] = new tabobject('reportanissue', 'reportissue.php', get_string('newissue', 'local_helpdesk'));
        }

        $rows[0][] = new tabobject('view', 'view.php?view=view', get_string('view', 'local_helpdesk') .
            ' (' . $totalissues . ' ' . get_string('issues', 'local_helpdesk') . ')'
        );

        $rows[0][] = new tabobject('resolved', 'view.php?view=resolved', get_string('resolvedplural', 'local_helpdesk') .
            ' (' . $totalresolvedissues . ' ' . get_string('issues', 'local_helpdesk') . ')'
        );

        $rows[0][] = new tabobject('profile', 'view.php?view=profile', get_string('profile', 'local_helpdesk'));

        if (has_capability('local/helpdesk:configure', $context)) {
            $rows[0][] = new tabobject('admin', 'view.php?view=admin', get_string('administration', 'local_helpdesk'));
        }

        // Render Subtabs menu

        $activated = null;
        switch ($view) {
            case 'view' :
                if (!preg_match('/tickets|work|browse|search|viewanissue|editanissue/', $screen)) {
                    $screen = 'tickets';
                }
                if (has_capability('local/helpdesk:report', $context)) {
                    $rows[1][] = new tabobject('tickets', 'view.php?view=view&amp;screen=tickets', get_string('tickets', 'local_helpdesk'));
                }
                if (helpdesk_has_assigned_issues()) {
                    $rows[1][] = new tabobject('work', 'view.php?view=view&amp;screen=work', get_string('work', 'local_helpdesk'));
                }
                if (has_capability('local/helpdesk:viewallissues', $context)) {
                    $rows[1][] = new tabobject('browse', 'view.php?view=view&amp;screen=browse', get_string('browse', 'local_helpdesk'));
                }
                break;
            case 'resolved' :
                if (!preg_match('/tickets|browse|work/', $screen)) {
                    $screen = 'tickets';
                }
                break;
            case 'profile':
                if (!preg_match('/profile|preferences|watches|queries/', $screen)) {
                    $screen = 'profile';
                }
                break;
            case 'reports':
                if (!preg_match('/status|evolution|print/', $screen)) {
                    $screen = 'status';
                }
                break;
            case 'admin':
                if (!preg_match('/summary|manageelements|managenetwork/', $screen)) {
                    $screen = 'summary';
                }
                break;
            default:
        }
        if (!empty($screen)) {
            $selected = $screen;
            $activated = [$view];
        } else {
            $selected = $view;
        }
        $str .= $OUTPUT -> container_start('local-header helpdesk-tabs');
        $str .= print_tabs($rows, $selected, '', $activated, true);
        $str .= $OUTPUT -> container_end();

        return $str;
    }

    /**
     * @param stdClass $issue
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    final public function edit_link(stdClass $issue): string
    {
        $params = ['view' => 'view', 'screen' => 'editanissue', 'issueid' => $issue -> id];

        $issueurl = new moodle_url('/local/helpdesk/view.php', $params);

        $str = '<tr>';
        $str .= '<td colspan="4"  style="text-align: right">';
        $str .= '<form method="post" action="' . $issueurl -> __toString() . '">';
        $str .= '<input type="submit" name="go_btn" value="' . get_string('turneditingon', 'local_helpdesk') . '">';
        $str .= '</form>';
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    /**
     * @param stdClass $issue
     * @return string
     * @throws coding_exception
     */
    final public function core_issue(stdClass $issue): string
    {
        $str = '<tr style="vertical-align:top">
                    <td colspan="4" style="text-align:left" class="helpdesk-issue-summary">' . format_string($issue -> summary) . '</td>
                </tr>';

        $str .= '<tr style="vertical-align:top">
                    <td style="text-align:right; width=25%" class="helpdesk-issue-param">
                        <b>' . get_string('issuenumber', 'local_helpdesk') . ':</b><br/>
                    </td>
                    <td style="text-align:right; width=25%" class="helpdesk-issue-param">
                       <b>' . get_string('status', 'local_helpdesk') . ':</b>
                    </td>
                    <td style="width: 25%" class="status_' . $STATUSKEYS[$issue -> status] . '">
                       <b>' . $STATUSKEYS[$issue -> status] . '</b>
                    </td>
                 </tr>';

        return $str;
    }

}