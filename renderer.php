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
     * @param $view
     * @param $screen
     * @return string
     * @throws coding_exception
     */
    public function tabs($view, $screen): string
    {
        global $OUTPUT;

        $str = '';
        $context = context_system ::instance();

        $totalissues = 0;
        $totalresolvedissue = 0;

        /*      if ($screen === 'tickets') {
                    //$totalissues = define in $DB->count_records_select
                } elseif ($screen === 'work') {
                    //$totalissues = define in $DB->count_records_select
                } else {
                    $totalissues = 0;
                    $totalresolved_issue = 0;
                }*/

        // Render Tabs with options for user.

        if ($context === null) {
            die('context is null');
        }

        if (has_capability('local/helpdesk:report', $context)) {
            $rows[0][] = new tabobject('reportanissue', 'reportissue.php', get_string('newissue', 'local_helpdesk'));
        }

        $rows[0][] = new tabobject('view', 'view.php?view=view', get_string('view', 'local_helpdesk') .
            ' (' . $totalissues . ' ' . get_string('issues', 'local_helpdesk') . ')');

        $rows[0][] = new tabobject('resolved', 'view.php?view=resolved',
            get_string('resolvedplural', 'local_helpdesk') .
            ' (' . $totalresolvedissue . ' ' . get_string('issues', 'local_helpdesk') . ')');

        $rows[0][] = new tabobject('profile', 'view.php?view=profile', get_string('profile', 'local_helpdesk'));

        if (has_capability('local/helpdesk:configure', $context)) {
            $rows[0][] = new tabobject('admin', 'view.php?view=admin', get_string('administration', 'local_helpdesk'));
        }

        // Render Subtabs

//        $selected = null;
        $activated = null;
        switch ($view) {
            case 'view' :
                if (!preg_match('/tickets|work|browse|search|viewanissue|editanissue/', $screen)) $screen = 'tickets';
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
                if (!preg_match('/tickets|browse|work/', $screen)) $screen = 'tickets';
                break;
            case 'profile':
                if (!preg_match('/profile|preferences|watches|queries/', $screen)) $screen = 'profile';
                break;
            case 'reports':
                if (!preg_match('/status|evolution|print/', $screen)) $screen = 'status';
                break;
            case 'admin':
                if (!preg_match('/summary|manageelements|managenetwork/', $screen)) $screen = 'summary';
                break;
            default:
        }
        if (!empty($screen)) {
            $selected = $screen;
            $activated = [$view];
        } else $selected = $view;
        $str .= $OUTPUT -> container_start('local-header helpdesk-tabs');
        $str .= print_tabs($rows, $selected, '', $activated, true);
        $str .= $OUTPUT -> container_end();

        return $str;
    }
}