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
 * Static storage for captured SQL queries.
 *
 * @package    local_debugbar
 */
final class query_store {

    /** @var int Maximum number of queries to store in detail. */
    private const MAX_QUERIES = 500;

    /** @var array Captured query records. */
    public static array $queries = [];

    /** @var int Total query count (including those beyond MAX_QUERIES). */
    public static int $total_count = 0;

    /**
     * Add a captured query to the store.
     *
     * @param array $query
     */
    public static function add(array $query): void {
        self::$total_count++;
        if (count(self::$queries) < self::MAX_QUERIES) {
            self::$queries[] = $query;
        }
    }
}
