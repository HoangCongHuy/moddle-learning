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

namespace local_debugbar;

defined('MOODLE_INTERNAL') || die();

use DebugBar\JavascriptRenderer;
use DebugBar\StandardDebugBar;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\TimeDataCollector;
use local_debugbar\datacollector\MoodleSqlCollector;

/**
 * Central manager for the local Debugbar plugin.
 *
 * @package    local_debugbar
 */
final class debugbar_manager {
    /** @var debugbar_manager|null */
    private static ?debugbar_manager $instance = null;

    /** @var bool */
    private bool $eligible = false;

    /** @var bool */
    private bool $bootstrapped = false;

    /** @var StandardDebugBar|null */
    private ?StandardDebugBar $debugbar = null;

    /** @var JavascriptRenderer|null */
    private ?JavascriptRenderer $renderer = null;

    /** @var TimeDataCollector|null */
    private ?TimeDataCollector $timecollector = null;

    /** @var bool */
    private bool $requestmeasurestarted = false;

    /** @var MessagesCollector|null */
    private ?MessagesCollector $messagescollector = null;

    private const REQUEST_MEASURE_NAME = 'moodle_request';
    private const REQUEST_MEASURE_LABEL = 'Moodle request';

    /**
     * Singleton accessor.
     */
    public static function instance(): debugbar_manager {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Early initialization method to bootstrap Debugbar immediately after Moodle core setup.
     * This is called by the after_config hook in lib.php.
     *
     * @return bool True if successfully initialized, false otherwise
     */
    public static function init_early(): bool {
        $manager = self::instance();

        if ($manager->ensure_bootstrapped() !== true) {
            return false;
        }

        $manager->log_message('info', 'Debugbar initialized early in request lifecycle');
        return true;
    }

    /**
     * Constructor is private to enforce the singleton.
     */
    private function __construct() {
        $this->evaluate_conditions();
    }

    /**
     * Outputs Debugbar head assets when permitted.
     */
    public function output_head(): void {
        echo $this->get_head_html();
    }

    /**
     * Returns Debugbar head output or an empty string if unavailable.
     */
    public function get_head_html(): string {
        if ($this->ensure_bootstrapped() !== true) {
            return '';
        }

        if ($this->renderer === null) {
            return '';
        }

        return $this->renderer->renderHead();
    }

    /**
     * Outputs Debugbar footer payload when permitted.
     */
    public function output_footer(): void {
        echo $this->get_footer_html();
    }

    /**
     * Returns Debugbar footer output or an empty string if unavailable.
     */
    public function get_footer_html(): string {
        if ($this->ensure_bootstrapped() !== true) {
            return '';
        }

        if ($this->renderer === null) {
            return '';
        }

        return $this->renderer->render();
    }

    /**
     * Start a custom measure that will show up in the Timeline tab.
     */
    public function start_measure(string $name, ?string $label = null, ?string $collector = null): void {
        if ($this->timecollector === null) {
            return;
        }

        try {
            $this->timecollector->startMeasure($name, $label, $collector);
        } catch (\Throwable $throwable) {
            $this->log_debug('failed to start measure: ' . $throwable->getMessage());
        }
    }

    /**
     * Stop a custom measure and optionally attach additional data.
     */
    public function stop_measure(string $name, array $params = []): void {
        if ($this->timecollector === null) {
            return;
        }

        try {
            $this->timecollector->stopMeasure($name, $params);
        } catch (\Throwable $throwable) {
            $this->log_debug('failed to stop measure: ' . $throwable->getMessage());
        }
    }

    /**
     * Wraps a callable in a timeline measure.
     *
     * @return mixed
     */
    public function measure(string $name, string $label, callable $callable, ?string $collector = null): mixed {
        $this->start_measure($name, $label, $collector);
        try {
            return $callable();
        } finally {
            $this->stop_measure($name);
        }
    }

    /**
     * Pushes a log entry into the Debugbar Messages collector.
     */
    public function log_message(string $level, mixed $message, array $context = []): void {
        if ($this->messagescollector === null) {
            return;
        }

        try {
            if (empty($context) === false) {
                $message .= ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
            }

            $this->messagescollector->log($level, $message, $context);
        } catch (\Throwable $throwable) {
            $this->log_debug('failed to add message: ' . $throwable->getMessage());
        }
    }

    /**
     * Check if the debugbar is enabled for the current user/request.
     *
     * @return bool
     */
    public function is_enabled(): bool {
        if ($this->eligible !== true) {
            return false;
        }

        return $this->ensure_bootstrapped() === true;
    }

    /**
     * Inject head content via hook.
     *
     * @param \core\hook\output\before_standard_head_html_generation $hook
     */
    public function inject_head_content(\core\hook\output\before_standard_head_html_generation $hook): void {
        if ($this->is_enabled() !== true) {
            return;
        }

        // Do not inject debugbar assets into AJAX or web service responses.
        if ((defined('AJAX_SCRIPT') && AJAX_SCRIPT) || (defined('WS_SERVER') && WS_SERVER)) {
            return;
        }

        $headhtml = $this->get_head_html();
        if (empty($headhtml) === true) {
            return;
        }

        $hook->add_html($headhtml);
    }

    /**
     * Inject footer content via hook.
     *
     * @param \core\hook\output\before_footer_html_generation $hook
     */
    public function inject_footer_content(\core\hook\output\before_footer_html_generation $hook): void {
        if ($this->is_enabled() !== true) {
            return;
        }

        // Do not inject debugbar HTML into AJAX or web service responses — it corrupts JSON.
        if ((defined('AJAX_SCRIPT') && AJAX_SCRIPT) || (defined('WS_SERVER') && WS_SERVER)) {
            return;
        }
        
        $footerhtml = $this->get_footer_html();
        if (empty($footerhtml) === true) {
            return;
        }

        $hook->add_html($footerhtml);
    }

    /**
     * Checks the activation requirements.
     */
    private function evaluate_conditions(): void {
        global $CFG, $USER, $SESSION;

        if (isloggedin() !== true || isguestuser() === true || empty($USER->id) === true) {
            $this->log_debug('user is not logged in or is guest');
            return;
        }

        $alloweduserid = (int)get_config('local_debugbar', 'alloweduserid');
        if ($alloweduserid <= 0) {
            $alloweduserid = 2;
        }

        if ((int)$USER->id !== $alloweduserid) {
            $this->log_debug("user {$USER->id} is not allowed (expected {$alloweduserid})");
            return;
        }

        $param = optional_param('enable_debugbar', null, PARAM_RAW_TRIMMED);
        if ($param !== null) {
            if (in_array($param, ['1', 'true', 'on'], true) === true) {
                $SESSION->debugbar_enabled = true;
            } else {
                unset($SESSION->debugbar_enabled);
            }
        }

        $enabled = empty($SESSION->debugbar_enabled) === false;

        $allowdebugging = (int)get_config('local_debugbar', 'enablewhendebugging');
        if ($enabled === false && $allowdebugging !== 0 && debugging() === true) {
            $enabled = true;
        }

        $this->eligible = $enabled;
        $this->log_debug('eligibility evaluated: ' . ($enabled ? 'enabled' : 'disabled'));
    }

    /**
     * Bootstraps the Debugbar once the user is eligible.
     */
    private function ensure_bootstrapped(): bool {
        global $CFG;

        if ($this->eligible !== true) {
            return false;
        }

        if ($this->bootstrapped === true) {
            return $this->renderer !== null;
        }

        $autoload = $CFG->dirroot . '/local/debugbar/vendor/autoload.php';
        if (is_readable($autoload) === false) {
            // Keep the plugin silent when vendor dependencies are missing.
            $this->eligible = false;
            return false;
        }

        require_once($autoload);

        try {
            $this->debugbar = new StandardDebugBar();
            $resourcesdir = $CFG->dirroot . '/local/debugbar/vendor/php-debugbar/php-debugbar/src/DebugBar/Resources';
            $resourcesurl = "$CFG->wwwroot/local/debugbar/vendor/php-debugbar/php-debugbar/src/DebugBar/Resources";
            $this->renderer = $this->debugbar->getJavascriptRenderer($resourcesurl, $resourcesdir);
            $this->recover_collectors();
        } catch (\Throwable $throwable) {
            // Do not leak anything to the end user, just disable silently.
            $this->eligible = false;
            $this->debugbar = null;
            $this->renderer = null;
            return false;
        }

        $this->bootstrapped = true;
        return true;
    }

    /**
     * Retrieves all collectors we care about after bootstrapping.
     */
    private function recover_collectors(): void {
        $this->recover_time_collector();
        $this->recover_messages_collector();
        $this->register_sql_collector();
    }

    /**
     * Retrieves the time collector and starts a request-level measure.
     */
    private function recover_time_collector(): void {
        if ($this->debugbar === null) {
            return;
        }

        try {
            $collector = $this->debugbar->getCollector('time');
        } catch (\Throwable $throwable) {
            return;
        }

        if (($collector instanceof TimeDataCollector) !== true) {
            return;
        }

        $this->timecollector = $collector;
        $collector->showMemoryUsage();
        $this->start_request_measure();
    }

    /**
     * Retrieves the messages collector for logging.
     */
    private function recover_messages_collector(): void {
        if ($this->debugbar === null) {
            return;
        }

        try {
            $collector = $this->debugbar->getCollector('messages');
        } catch (\Throwable $throwable) {
            return;
        }

        if (($collector instanceof MessagesCollector) !== true) {
            return;
        }

        $this->messagescollector = $collector;
    }

    /**
     * Registers the SQL query collector.
     */
    private function register_sql_collector(): void {
        if ($this->debugbar === null) {
            return;
        }

        try {
            $collector = new MoodleSqlCollector();
            $this->debugbar->addCollector($collector);
        } catch (\Throwable $throwable) {
            $this->log_debug('failed to add SQL collector: ' . $throwable->getMessage());
        }
    }

    /**
     * Starts the request measure exactly once.
     */
    private function start_request_measure(): void {
        if ($this->requestmeasurestarted === true) {
            return;
        }

        if ($this->timecollector === null) {
            return;
        }

        $this->timecollector->startMeasure(
            self::REQUEST_MEASURE_NAME,
            self::REQUEST_MEASURE_LABEL,
        );
        $this->requestmeasurestarted = true;
    }

    /**
     * Logs a message for operator visibility.
     */
    private function log_debug(string $message): void {
        error_log('[local_debugbar] ' . $message);
    }
}
