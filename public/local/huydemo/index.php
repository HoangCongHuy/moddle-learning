<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/huydemo/classes/form/task_form.php');

use core\output\html_writer;
use core\output\inplace_editable;
use core\output\notification;
use core\url;
use local_huydemo\form\task_form;

global $DB, $USER, $PAGE, $OUTPUT;

$url = new url('/local/huydemo/index.php');
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

$action = optional_param(parname: 'action', default: '', type: PARAM_ALPHA);
$taskid = optional_param(parname: 'taskid', default: 0, type: PARAM_INT);

if ($action === 'delete' && $taskid > 0) {
    require_sesskey();

    $DB->delete_records('local_huydemo_tasks', ['id' => $taskid, 'userid' => $USER->id]);
    redirect(url: $url, message: 'Task deleted successfully', messagetype: notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
$mform->display();
echo html_writer::tag(tagname: 'hr', contents: '');

$tasks = $DB->get_records('local_huydemo_tasks', ['userid' => $USER->id], 'timecreated DESC');

$templatedata = new stdClass();
$templatedata->hastasks = !empty($tasks);
$templatedata->tasks = [];

if ($templatedata->hastasks) {
    foreach ($tasks as $task) {
        $deleteUrl = new url('/local/huydemo/index.php', [
            'action' => 'delete',
            'taskid' => $task->id,
            'sesskey' => sesskey(),
        ]);

        $editable = new inplace_editable(
            component: 'local_huydemo',
            itemtype: 'taskname',
            itemid: $task->id,
            editable: true,
            displayvalue: $task->taskname,
            value: $task->taskname,
        );

        $taskname_html = $OUTPUT->render($editable);

        $templatedata->tasks[] = [
            'taskname_html' => $taskname_html,
            'date' => userdate($task->timecreated),
            'deleteurl' => $deleteUrl->out(false),
        ];
    }
}

echo $OUTPUT->render_from_template('local_huydemo/task_list', $templatedata);

echo $OUTPUT->footer();