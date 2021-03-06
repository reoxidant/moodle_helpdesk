<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

require('../../config.php');
require_once($CFG -> dirroot . '/local/helpdesk/lib.php');
require_once($CFG -> dirroot . '/local/helpdesk/locallib.php');
require_once($CFG -> dirroot . '/local/helpdesk/forms/reportissue_form.php');

$screen = helpdesk_resolve_screen();
$view = helpdesk_resolve_view();

$context = context_system ::instance();

require_login();
require_capability('local/helpdesk:report', $context);

$pluginname = get_string('pluginname', 'local_helpdesk');

$url = new moodle_url('/local/helpdesk/reportissue.php');

$context = context_system ::instance();
$PAGE -> set_url($url);
$PAGE -> set_context($context);
$PAGE -> set_pagelayout('standard');
$PAGE -> navbar -> add($pluginname);
$PAGE -> set_title($pluginname);
$PAGE -> set_heading($pluginname);

$form = new reportissue_form($url);

$data = $form -> get_data();

if ($data && !$form -> is_cancelled()) {

    $issue = helpdesk_submit_issue_form($data);

    if (!$issue) {
        print_error('errorcannotsubmitticket', 'local_helpdesk');
    }

    $data = file_postupdate_standard_editor(
        $data,
        'description',
        $form -> options,
        $context,
        'local_helpdesk',
        'issuedescription',
        $data -> issueid
    );

    $DB -> set_field('helpdesk_issue', 'description', $data -> description, array('id' => $issue -> id));

    $stc = new StdClass;
    $stc -> userid = $USER -> id;
    $stc -> issueid = $issue -> id;
    $stc -> timechange = time();
    $stc -> statusfrom = OPEN;
    $stc -> statusto = OPEN;
    echo $OUTPUT -> header();
    echo $OUTPUT -> box_start('generalbox', 'helpdesk-acknowledge');
    echo get_string('thanksdefault', 'local_helpdesk');
    echo $OUTPUT -> box_end();
    echo $OUTPUT -> continue_button(new moodle_url('/local/helpdesk/view.php', array('view' => 'view', 'screen' => 'browse')));
    echo $OUTPUT -> footer();
}

echo $OUTPUT -> header();

$view = 'reportanissue';
include_once($CFG -> dirroot . '/local/helpdesk/menus.php');

$form -> display();

echo $OUTPUT -> footer();