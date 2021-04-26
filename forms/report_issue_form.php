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
class HelpDeskIssueForm extends moodleform
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
        $maxbytes = $CFG->maxbytes;

        $this -> options = array(
            'trusttext' => true,
            'subdirs' => false,
            'maxfiles' => $maxfiles,
            'maxbytes'  => $maxbytes,
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
     * @return array|void
     */
    public function validation($data, $files = null)
    {
    }

    /**
     *
     */
    public function set_data($defaults)
    {
        $defaults -> description_editor['text'] = $defaults -> description;
        $defaults -> description_editor['format'] = $defaults -> descriptionformat;
        $defaults = file_prepare_standard_editor(
            $defaults,
            'description',
            $this -> options,
            $this -> context,
            'local_helpdesk',
            'issuedescription',
            $defaults -> issueid
        );

        parent ::set_data($defaults);
    }
}