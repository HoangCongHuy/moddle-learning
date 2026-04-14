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
 * Trait that overrides query_end() to capture SQL queries into query_store.
 *
 * @package    local_debugbar
 */
trait query_capture_trait {

    /**
     * Override query_end to capture query data before delegating to parent.
     *
     * @param mixed $result The db specific result obtained from running a query.
     */
    protected function query_end($result) {
        if (!$this->loggingquery) {
            query_store::add([
                'sql' => $this->last_sql,
                'params' => $this->last_params ?? [],
                'type' => $this->last_type,
                'duration' => $this->query_time(),
                'success' => ($result !== false),
                'error' => ($result === false) ? $this->get_last_error() : '',
            ]);
        }

        parent::query_end($result);
    }

    /**
     * Create a wrapper instance from an existing moodle_database object
     * by copying all properties via Reflection.
     *
     * @param \moodle_database $db The original database instance.
     * @return static
     */
    public static function createFrom(\moodle_database $db): static {
        $wrapper = (new \ReflectionClass(static::class))->newInstanceWithoutConstructor();

        $sourceClass = new \ReflectionClass($db);
        do {
            foreach ($sourceClass->getProperties() as $prop) {
                if ($prop->getDeclaringClass()->getName() !== $sourceClass->getName()) {
                    continue;
                }
                $prop->setAccessible(true);
                try {
                    $prop->setValue($wrapper, $prop->getValue($db));
                } catch (\ReflectionException $e) {
                    // Skip properties that cannot be copied.
                }
            }
        } while ($sourceClass = $sourceClass->getParentClass());

        return $wrapper;
    }
}
