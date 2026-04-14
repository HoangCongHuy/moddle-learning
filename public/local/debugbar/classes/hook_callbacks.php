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

/**
 * Hook callbacks for Debugbar plugin.
 *
 * @package    local_debugbar
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    /**
     * Hook callback executed before standard head HTML generation.
     *
     * This allows us to inject CSS or JavaScript into the head section.
     *
     * @param \core\hook\output\before_standard_head_html_generation $hook
     */
    public static function before_standard_head_html_generation(
        \core\hook\output\before_standard_head_html_generation $hook
    ): void {
        $manager = debugbar_manager::instance();

        // Log that we're in the head generation phase
        if ($manager->is_enabled()) {
            $manager->log_message('debug', 'Head HTML generation hook triggered');
        }

        // Inject any CSS needed for the debugbar
        $manager->inject_head_content($hook);
    }

    /**
     * Hook callback executed before footer HTML generation.
     *
     * This is where we inject the Debugbar UI into the page.
     *
     * @param \core\hook\output\before_footer_html_generation $hook
     */
    public static function before_footer_html_generation(
        \core\hook\output\before_footer_html_generation $hook
    ): void {
        $manager = debugbar_manager::instance();

        // Only inject if enabled
        if ($manager->is_enabled() === true) {
            $manager->log_message('debug', 'Footer HTML generation hook triggered');

            // Inject the debugbar HTML
            $manager->inject_footer_content($hook);
        }
    }
}

