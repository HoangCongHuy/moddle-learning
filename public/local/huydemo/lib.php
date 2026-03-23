<?php

use core\output\inplace_editable;

defined('MOODLE_INTERNAL') || die();

function local_huydemo_inplace_editable(string $itemtype, int $itemid, string $newvalue) {
    global $DB, $USER;

    require_login();

    if ($itemtype === 'taskname') {
        $task = $DB->get_record('local_huydemo_tasks', ['id' => $itemid, 'userid' => $USER->id], '*', MUST_EXIST);
        $task->taskname = clean_param($newvalue, PARAM_TEXT);

        $DB->update_record('local_huydemo_tasks', $task);

        return new inplace_editable(
            component: 'local_huydemo',
            itemtype: 'taskname',
            itemid: $task->id,
            editable: true,
            displayvalue: $task->taskname,
            value: $task->taskname,
        );
    }
}