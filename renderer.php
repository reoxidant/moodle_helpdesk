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
class local_helpdesk_renderer extends plugin_renderer_base{
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

        $rows[0][] = new tabobject('view', "view.php", get_string('view', 'helpdesk'));

        $rows[0][] = new tabobject('resolved', "view.php", get_string('resolved_plural', 'helpdesk'));

        $rows[0][] = new tabobject('profile', "view.php", get_string('profile', 'helpdesk'));

        $selected = null;
        $activated = null;
        switch ($view) {
            case 'view' :
                if (!preg_match("/tickets|work|browse|search|view_issue|edit_issue/", $screen)) {
                    $screen = 'tickets';
                }
                break;
            case 'resolved' :
                if (!preg_match("/tickets|browse|work/", $screen)) {
                    $screen = 'tickets';
                }
                break;
            case 'profile':
                if (!preg_match("/profile|preferences|watches|queries/", $screen)) {
                    $screen = 'profile';
                }
                break;
            case 'reports':
                if (!preg_match("/status|evolution|print/", $screen)) {
                    $screen = 'status';
                }
                break;
            case 'admin':
                if (!preg_match("/summary|manage_elements|manage_network/", $screen)) {
                    $screen = 'summary';
                }
                break;
            default:
        }
        if (!empty($screen)) {
            $selected = $screen;
            $activated = array($view);
        } else {
            $selected = $view;
        }
        $str .= $OUTPUT->container_start('local-header helpdesk-tabs');
        $str .= print_tabs($rows, $selected, '', $activated, true);
        $str .= $OUTPUT->container_end();

        return $str;
    }
}