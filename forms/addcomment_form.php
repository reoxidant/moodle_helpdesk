<?php

require_once($CFG -> libdir . '/formslib.php');

/**
 * Class addcomment_form
 */
class addcomment_form extends moodleform
{
    public $editoroptions;
    public $context;

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function definition()
    {
        $mform = $this -> _form;

        $this -> context = context_system ::instance();
        $maxfiles = 99;
        $maxbytes = $CFG -> maxbytes;
        $this -> editoroptions = ['trusttext' => true, 'subdirs' => false, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes, 'context' => $this -> context];

        $mform -> addElement('hidden', 'issueid', $this -> _customdata['issueid']);
        $mform -> setType('issueid', PARAM_INT);

        $mform -> addElement('editor', 'comment_editor', get_string('comment', 'local_helpdesk'), $this -> editoroptions);

        $this -> add_action_buttons();
    }

    /**
     * @param array $data
     * @param array $files
     * @return void
     */
    public function validation($data, $files = [])
    {

    }

    /**
     * @param array|stdClass $default_values
     * @throws coding_exception
     */
    public function set_data($default_values)
    {
        $default_values -> comment_editor['text'] = $default_values -> comment;
        $default_values -> comment_editor['format'] = $default_values -> commentformat;
        $default_values = file_prepare_standard_editor(
            $default_values,
            'comment',
            $this -> editoroptions,
            $this -> context,
            'local_helpdesk',
            'issuecomment',
            $default_values -> id
        );

        parent ::set_data($default_values);
    }
}