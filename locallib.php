<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

defined('MOODLE_INTERNAL') || die();

/**
 *
 */
const OPEN = 1;
/**
 *
 */
const RESOLVING = 2;
/**
 *
 */
const RESOLVED = 3;

global $STATUSCODES;
global $STATUSKEYS;
global $FULLSTATUSKEYS;

$STATUSCODES = array(
    OPEN => 'open',
    RESOLVING => 'resolving',
    RESOLVED => 'resolved'
);

$STATUSKEYS = helpdesk_get_status_keys();
$FULLSTATUSKEYS = helpdesk_get_status_keys();

/**
 * @return array|false|float|int|mixed|string|null
 * @throws coding_exception
 * @throws dml_exception
 */
function helpdesk_resolve_screen()
{
    global $SESSION;

    $screen = optional_param('screen', @$SESSION -> helpdesk_current_screen, PARAM_ALPHA);

    if (empty($screen)) {
        if (has_capability('local/helpdesk:report', context_system ::instance())) {
            $defaultscreen = 'tickets';
        } else {
            $defaultscreen = 'browse';
        }
        $screen = $defaultscreen;
    }

    $SESSION -> helpdesk_current_screen = $screen;
    return $screen;
}

/**
 * @return array|false|float|int|mixed|string|null
 * @throws coding_exception
 */
function helpdesk_resolve_view()
{
    global $SESSION;

    $view = optional_param('view', @$SESSION -> helpdesk_current_view, PARAM_ALPHA);

    if (empty($view)) {
        $defaultview = 'view';
        $view = $defaultview;
    }

    $SESSION -> helpdesk_current_view = $view;
    return $view;
}

/**
 * @param $data
 * @return StdClass|null
 * @throws dml_exception
 * @throws moodle_exception
 */
function helpdesk_submit_issue_form(&$data)
{
    global $DB, $USER;

    $issue = new StdClass();
    $issue -> summary = $data -> summary;
    $issue -> description = $data -> description_editor['text'];
    $issue -> descriptionformat = $data -> description_editor['format'];
    $issue -> datereported = time();
    $issue -> reportedby = $USER -> id;
    $issue -> status = OPEN;
    $issue -> assignedto = 0;
    $issue -> bywhomid = 0;

    $maxpriority = $DB -> get_field_select('helpdesk_issue', 'MAX(priority)', '');
    $issue -> priority = $maxpriority + 1;

    $issue -> id = $DB -> insert_record('helpdesk_issue', $issue);
    if ($issue -> id) {
        $data -> issueid = $issue -> id;
        return $issue;
    }

    print_error('errorrecordissue', 'local_helpdesk');
    return null;
}

/**
 * @return array|mixed
 * @throws coding_exception
 */
function helpdesk_get_status_keys()
{
    static $FULLSTATUSKEYS;

    if (!isset($FULLSTATUSKEYS)) {
        $FULLSTATUSKEYS = array(
            OPEN => get_string('open', 'local_helpdesk'),
            RESOLVING => get_string('resolving', 'local_helpdesk'),
            RESOLVED => get_string('resolved', 'local_helpdesk')
        );
    }

    return $FULLSTATUSKEYS;
}

/**
 * @throws dml_exception
 */
function helpdesk_update_priority_stack()
{
    global $DB;

    $sql = '
        UPDATE
            {helpdesk_issue}
        SET
            priority = 0
        WHERE
            status IN (' . RESOLVED . ')
    ';
    $DB -> execute($sql);

    // fetch prioritized by order
    $issues = $DB -> get_records_select('helpdesk_issue', 'priority != 0', null, 'priority', 'id, priority');
    $i = 1;
    if (!empty($issues)) {
        foreach ($issues as $issue) {
            $issue -> priority = $i;
            $DB -> update_record('helpdesk_issue', $issue);
            $i++;
        }
    }
}

/**
 * @throws coding_exception
 */
function helpdesk_can_workon(&$context, $issue = null): bool
{
    global $USER;

    if ($issue) {
        return $issue -> assignedto === $USER -> id && has_capability('local/helpdesk:resolve', $context);
    }

    return has_capability('local/helpdesk:resolve', $context);
}

/**
 * @param $context
 * @param $issue
 * @return bool
 * @throws coding_exception
 */
function helpdesk_can_edit(&$context, &$issue): bool
{
    return
        has_capability('local/helpdesk:manage', $context) ||
        $USER -> id === $issue -> repotedby ||
        ($issue -> assgnedto === $USER -> id && has_capability('local/helpdesk:resolve', $context));
}

/**
 * @param $context
 * @return array
 * @throws coding_exception
 */
function helpdesk_getresolvers($context): array
{
    $allnames = get_all_user_name_fields(true, 'u');
    return get_users_by_capability($context, 'local/helpdesk:resolve', 'u.id' . $allnames, 'lastname', '', '', '', '', false);
}

function helpdesk_print_direct_editor($attributes, $values, &$options): string
{
    global $CFG, $PAGE;

    require_once($CFG -> dirroot . '/repository/lib.php');

    $ctx = $options['context'];

    $id = $attributes['id'];
    $elname = $attributes['name'];

    $subdirs = @$options['subdirs'];
    $maxbytes = @$options['maxbytes'];
    $areamaxbytes = @$options['areamaxbytes'];
    $maxfiles = @$options['maxfiles'];
    $changeformat = @$options['changeformat'];

    $text = $values['text'];
    $format = $values['format'];
    $draftitemid = $values['itemid'];

    if (!isloggedin() || isguestuser()) {
        $maxfiles = 0;
    }

    $str = '<div>';

    $editor = editors_get_preferred_editor($format);
    $strformats = format_text_menu();
    $formats = $editor -> get_supported_formats();
    foreach ($formats as $fid) {
        $formats[$fid] = $strformats;
    }

    // get filepicker info

    if ($maxfiles != 0) {
        if (empty($draftitemid)) {
            // no existing area info provided - let's use fresh new draft are
            require_once("$CFG->libdir/filelib.php");
            $draftitemid = file_get_unused_draft_itemid();
            echo "Generating fresh filearea $draftitemid";
        }

        $args = new stdClass();
        // need these three to filter repositories list
        $args -> accepted_types = ['web_image'];
        $args -> return_types = @$options['return_types'];
        $args -> context = $ctx;
        $args -> env = 'filepicker';

        // advimage plugin
        $image_options = initialise_filepicker((array)$args);
        $image_options -> context = $ctx;
        $image_options -> client_id = uniqid();
        $image_options -> maxbytes = @$options['maxbytes'];
        $image_options -> areamaxbytes = @$options['areamaxbytes'];
        $image_options -> env = 'editor';
        $image_options -> itemid = $draftitemid;

        //moodlemedia plugin
        $args -> accepted_types = ['video', 'audio'];
        $media_options = initialise_filepicker((array)$args);
        $media_options -> context = $ctx;
        $media_options -> client_id = uniqid();
        $media_options -> maxbytes = @$options['maxbytes'];
        $media_options -> areamaxbytes = @$options['areamaxbytes'];
        $media_options -> env = 'editor';
        $media_options -> itemid = $draftitemid;

        //advlink plugin
        $args -> accepted_types = '*';
        $link_options = initialise_filepicker((array)$args);
        $link_options -> context = $ctx;
        $link_options -> client_id = uniqid();
        $link_options -> maxbytes = @$options['maxbytes'];
        $link_options -> areamaxbytes = @$options['areamaxbytes'];
        $link_options -> env = 'editor';
        $link_options -> itemid = $draftitemid;

        $fpoptions['image'] = $image_options;
        $fpoptions['media'] = $media_options;
        $fpoptions['link'] = $link_options;
    }

    //If editor is required tinymce, then set required_tinymce option to initalize tinymce valodation.
    if (($editor instanceof tinymce_texteditor) && !empty($attributes['onchange'])) {
        $options['required'] = true;
    }

    $editor -> use_editor($id, $options, $fpoptions);

    $rows = empty($attributes['rows']) ? 15 : $attributes['rows'];
    $cols = empty($attributes['cols']) ? 80 : $attributes['cols'];

    //Apply editor validation if required field
    $editorrules = '';
    if (!empty($attributes['onblur']) && !empty($attributes['onchange'])) {
        $editorrules = ' onblur="' . htmlspecialchars($attributes['onblur']) . '" onchange="' . htmlspecialchars($attributes['onchange']) . '"';
    }
    $str .= '<div><textarea id="' . $id . '" name="' . $elname . '[text]" rows="' . $rows . '" cols="' . $cols . '"' . $editorrules . '>';
    $str .= s($text);
    $str .= '</textarea></div>';

    $str .= '<div>';
    if(count($formats) > 1) {
        $str .= html_writer::label(get_string('format'), 'menu'. $elname. 'format', false, ['class' => 'accesshide']);

        $str .= html_writer::select($formats, $elname.'[format]', $format, false, ['id' => 'menu', $elname.'format']);
    } else {
        $keys = array_keys($formats);
//        $str .= html_writer::empty_tag('input', 'name' => $lname)
    }
    $str .= '</div>';

    return '';
}