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
 * Instrumented PostgreSQL database wrapper for query capture.
 *
 * @package    local_debugbar
 */
class debugbar_pgsql_database extends \pgsql_native_moodle_database {
    use query_capture_trait;
}
