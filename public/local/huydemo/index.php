<?php

use core\output\html_writer;
use core\output\notification;
use local_huydemo\form\task_form;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/huydemo/classes/form/task_form.php');

global $DB, $USER, $PAGE, $OUTPUT;

$url = new moodle_url('/local/huydemo/index.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Manager Task');
$PAGE->set_heading('Manager Task');

require_login();

$mform = new task_form();
if ($mform->is_cancelled()) {

} else if ($data = $mform->get_data()) {
    $record = new stdClass();
    $record->userid = $USER->id;
    $record->taskname = $data->taskname;
    $record->timecreated = time();

    $DB->insert_record('local_huydemo_tasks', $record);

    redirect(url: $url, message: 'Task added successfully', messagetype: notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

$mform->display();

echo html_writer::tag(tagname: 'hr', contents: '');

echo html_writer::tag(tagname: 'h3', contents: 'List tasks');

$tasks = $DB->get_records('local_huydemo_tasks', ['userid' => $USER->id], 'timecreated DESC');

if ($tasks) {
    echo html_writer::start_tag(tagname: 'ul');
    foreach ($tasks as $task) {
        $date = userdate(date: $task->timecreated);
        echo html_writer::tag(tagname: 'li', contents: "{$task->taskname} (Created at: $date)");
    }

    echo html_writer::end_tag(tagname: 'ul');
} else {
    echo html_writer::tag(tagname: 'p', contents: 'No tasks');
}

echo $OUTPUT->footer();