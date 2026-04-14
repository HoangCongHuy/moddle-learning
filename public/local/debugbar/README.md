Moodle Debugbar

Debugbar is a lightweight local developer toolbar that surfaces request data, log entries, and profiling metrics while building Moodle features.

Warning: I built this plugin entirely during hobby hours for personal learning, so if you have better ideas for configuration hooks for bootstrapping, please open an issue or send a merge request.

Note: The repository keeps the `vendor` directory committed because some companies, and developers cannot run Composer; in a full Composer-based workflow you can remove that folder and install dependencies via composer during deployment instead.

Requirements
------------
- PHP 7.4 or later (matches `composer.json` platform requirement).
- `php-debugbar/php-debugbar ^1.0` (installed automatically via Composer inside this plugin).

Installation
------------
1. Copy or `git clone` this folder into `moodle/local/debugbar` on your development site.
2. From the plugin directory run `composer install --no-dev` to pull `php-debugbar/php-debugbar` and autoload files.
3. Execute `php admin/cli/upgrade.php` from the Moodle root so the plugin registers itself.
4. Log in as an administrator and confirm `Site administration -> Plugins -> Local plugins -> Debug Bar` shows up.

Features
--------
- Integrates the upstream `php-debugbar` package to display runtime information in Moodle pages.
- Provides admin settings to limit access by user ID and to require developer debugging mode.
- Logs structured messages, automatic measures, and manual profiling spans for each request.
- Ships with a live demo inside `settings.php` so you can inspect how logging and measurement APIs behave.

Configuration
-------------
- Open the Debug Bar settings page and add `?enable_debugbar=1` to the URL so the toolbar appears.
- Set `Allowed user ID` to your testing account to control who sees the bar.
- Turn on `Enable when debugging` only when `$CFG->debugdeveloper` is enabled so the output stays focused on learning sessions.

Usage
-----
1. Browse any page with the query parameter `?enable_debugbar=1` and make sure you use the allowed user account.
2. Watch the Messages tab for log entries and the Timeline tab for performance bars while you click through Moodle.
3. Disable the plugin or remove the query parameter when you are finished to keep your site fast.

Examples from `settings.php`
----------------------------
Check `settings.php` to see how every feature is implemented:

```
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
```

This snippet shows logging, automatic measures, manual measures, and configuration checks that you can copy into your own code.

Warning
-------
Use this plugin only on development or hobby Moodle instances because it logs detailed request data and adds overhead.
