<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

require_once $CFG -> libdir . '/formslib.php';

/**
 * Class HelpDeskIssueForm
 */
class reportissue_form extends moodleform
{

    /**
     * @var
     */
    public $options;
    /**
     * @var
     */
    public $context;

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function definition()
    {
        global $CFG;

        $issueid = 0;
        $this -> context = context_system ::instance();

        $maxfiles = 99;
        $maxbytes = $CFG -> maxbytes;

        $this -> options = array(
            'trusttext' => true,
            'subdirs' => false,
            'maxfiles' => $maxfiles,
            'maxbytes' => $maxbytes,
            'context' => $this -> context
        );

        $mform = $this -> _form;
        $mform -> addElement('hidden', 'id', $issueid);
        $mform -> setType('id', PARAM_INT);

        $mform -> addElement('text', 'summary', get_string('summary', 'local_helpdesk'), array('size' => 80));
        $mform -> setType('summary', PARAM_TEXT);
        $mform -> addRule('summary', null, 'required', null, 'client');

        $mform -> addElement('editor', 'description_editor', get_string('description'), $this -> options);

        $this -> add_action_buttons();
    }

    /**
     * @param $data
     * @param null $files
     * @return void
     */
    public function validation($data, $files = null)
    {
    }

    /**
     * @param array|stdClass $default_values
     * @throws coding_exception
     */
    public function set_data($default_values)
    {
        $default_values -> description_editor['text'] = $default_values -> description;
        $default_values -> description_editor['format'] = $default_values -> descriptionformat;
        $default_values = file_prepare_standard_editor(
            $default_values,
            'description',
            $this -> options,
            $this -> context,
            'local_helpdesk',
            'issuedescription',
            $default_values -> issueid
        );

        parent ::set_data($default_values);
    }
}