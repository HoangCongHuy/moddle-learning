<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    global $USER;

    $settings = new admin_settingpage('local_debugbar', get_string('pluginname', 'local_debugbar'));

    // Test the Debugbar functionality in settings page.
    $manager = \local_debugbar\debugbar_manager::instance();

    // Test logging with different levels.
    $manager->log_message('info', 'Settings page loaded', ['time' => time(), 'user' => $USER->id]);
    $manager->log_message('debug', 'Testing Debugbar in settings.php', [
        'hassiteconfig' => $hassiteconfig,
        'plugin' => 'local_debugbar'
    ]);

    // Test performance measurement.
    $manager->measure('load_settings', 'Load Debugbar Settings', function() use ($settings) {
        // Simulate some work.
        usleep(5000); // 5ms
        return true;
    });

    // Test manual start/stop.
    $manager->start_measure('config_check', 'Check Configuration');
    $alloweduser = get_config('local_debugbar', 'alloweduserid');
    $enabledebugging = get_config('local_debugbar', 'enablewhendebugging');
    $manager->stop_measure('config_check', [
        'alloweduser' => $alloweduser,
        'enabledebugging' => $enabledebugging
    ]);

    // Log configuration status.
    $manager->log_message('info', 'Current configuration', [
        'allowed_user_id' => $alloweduser ?: 123,
        'enable_when_debugging' => $enabledebugging ? 'Yes' : 'No',
        'debugbar_enabled' => $manager->is_enabled() ? 'Yes' : 'No'
    ]);

    // Add a heading for configuration.
    $settings->add(new admin_setting_heading(
        'local_debugbar/configheading',
        get_string('settings', 'core'),
        get_string('configdesc', 'local_debugbar', [
            'enabled' => $manager->is_enabled() ? '✅ ENABLED' : '❌ DISABLED',
            'user' => $USER->id
        ])
    ));

    $settings->add(new admin_setting_configtext(
        'local_debugbar/alloweduserid',
        get_string('alloweduserid', 'local_debugbar'),
        get_string('alloweduserid_desc', 'local_debugbar'),
        123,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_debugbar/enablewhendebugging',
        get_string('enablewhendebugging', 'local_debugbar'),
        get_string('enablewhendebugging_desc', 'local_debugbar'),
        0
    ));

    // Add test section.
    $settings->add(new admin_setting_heading(
        'local_debugbar/testheading',
        'Testing & Debugging',
        'The Debugbar is being tested on this page. Check the Debugbar below to see:<br>' .
        '• <strong>Timeline</strong> tab for performance measurements<br>' .
        '• <strong>Messages</strong> tab for log entries<br>' .
        '• Settings load time, configuration checks, and more<br><br>' .
        '<em>Note: You must add <code>?enable_debugbar=1</code> to the URL to see the Debugbar.</em>'
    ));

    $ADMIN->add('localplugins', $settings);
}
