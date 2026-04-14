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

namespace local_debugbar\db;

defined('MOODLE_INTERNAL') || die();

/**
 * Replaces the global $DB with an instrumented wrapper that captures SQL queries.
 *
 * @package    local_debugbar
 */
final class db_swapper {

    /** @var \moodle_database|null Holds the original $DB to prevent garbage collection. */
    private static ?\moodle_database $originalDb = null;

    /** @var bool Whether the swap has already been performed. */
    private static bool $swapped = false;

    /** @var array Map of original driver classes to wrapper classes. */
    private const DRIVER_MAP = [
        'mysqli_native_moodle_database' => debugbar_mysqli_database::class,
        'mariadb_native_moodle_database' => debugbar_mariadb_database::class,
        'pgsql_native_moodle_database' => debugbar_pgsql_database::class,
    ];

    /**
     * Replace the global $DB with an instrumented wrapper.
     *
     * @return bool True if the swap was successful.
     */
    public static function swap(): bool {
        global $DB;

        if (self::$swapped) {
            return true;
        }

        if ($DB === null) {
            return false;
        }

        $class = get_class($DB);
        if (!isset(self::DRIVER_MAP[$class])) {
            return false;
        }

        try {
            $wrapperClass = self::DRIVER_MAP[$class];
            $wrapper = $wrapperClass::createFrom($DB);

            self::$originalDb = $DB;
            $DB = $wrapper;
            self::$swapped = true;

            return true;
        } catch (\Throwable $e) {
            error_log('[local_debugbar] Failed to swap $DB: ' . $e->getMessage());
            return false;
        }
    }
}
