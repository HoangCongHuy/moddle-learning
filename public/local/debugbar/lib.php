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

/**
 * Global helper function to log messages to Debugbar.
 *
 * @param string $level The log level (info, debug, warning, error)
 * @param mixed $message The message to log
 * @param array $context Optional context data
 */
function debugbar_log(string $level, $message, array $context = []): void {
    \local_debugbar\debugbar_manager::instance()->log_message($level, $message, $context);
}

/**
 * Global helper function to start a measure in Debugbar timeline.
 *
 * @param string $name Unique measure identifier
 * @param string|null $label Display label (defaults to $name)
 * @param string|null $collector Collector name
 */
function debugbar_start(string $name, ?string $label = null, ?string $collector = null): void {
    \local_debugbar\debugbar_manager::instance()->start_measure($name, $label, $collector);
}

/**
 * Global helper function to stop a measure in Debugbar timeline.
 *
 * @param string $name The measure identifier
 * @param array $params Optional additional parameters
 */
function debugbar_stop(string $name, array $params = []): void {
    \local_debugbar\debugbar_manager::instance()->stop_measure($name, $params);
}

/**
 * Global helper function to wrap a callable in a Debugbar measure.
 *
 * @param string $name Unique measure identifier
 * @param string $label Display label
 * @param callable $callable The function to measure
 * @param string|null $collector Collector name
 * @return mixed The return value of the callable
 */
function debugbar_measure(string $name, string $label, callable $callable, ?string $collector = null) {
    return \local_debugbar\debugbar_manager::instance()->measure($name, $label, $callable, $collector);
}

