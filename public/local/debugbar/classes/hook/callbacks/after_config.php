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

namespace local_debugbar\hook\callbacks;

/**
 * Hook callback for after_config.
 *
 * @package    local_debugbar
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class after_config {

    /**
     * Hook callback executed immediately after config.php and setup.php are loaded.
     * This ensures Debugbar is available early in the request lifecycle.
     *
     * @param \core\hook\after_config $hook
     */
    public static function callback(\core\hook\after_config $hook): void {
        // Do not instrument AJAX or web-service requests — the wrapped $DB
        // outlives the real mysqli connection and causes "mysqli object is
        // already closed" errors that corrupt JSON responses.
        if ((defined('AJAX_SCRIPT') && AJAX_SCRIPT) || (defined('WS_SERVER') && WS_SERVER)) {
            return;
        }

        $result = \local_debugbar\debugbar_manager::init_early();
        if ($result) {
            \local_debugbar\db\db_swapper::swap();
        }
    }
}

