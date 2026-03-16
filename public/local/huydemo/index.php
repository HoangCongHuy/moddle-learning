<?php

require_once(__DIR__ . '/../../config.php');

$url = new moodle_url('/local/huydemo/index.php');
$PAGE->set_url($url);

$context = context_system::instance();
$PAGE->set_context($context);

$title = get_string('pagetitle', 'local_huydemo');
$PAGE->set_title($title);
$PAGE->set_heading($title);

require_login();

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('pluginname', 'local_huydemo'));
echo html_writer::tag('p', get_string('greeting', 'local_huydemo'));

echo $OUTPUT->footer();