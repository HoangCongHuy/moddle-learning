<?php

namespace local_huydemo\form;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

class task_form extends \moodleform {
    public function definition()
    {
        $mform = $this->_form;

        $mform->addElement('text', 'taskname', 'Name Job', ['maxlength' => 256, 'size' => 50]);
        $mform->setType('taskname', PARAM_TEXT);
        $mform->addRule(
            element: 'taskname',
            message: 'Please enter name job',
            type: 'required',
            validation: 'client',
        );

        $this->add_action_buttons(cancel: false, submitlabel: 'Add Task');
    }
}
