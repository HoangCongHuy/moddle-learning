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

namespace local_debugbar\datacollector;

defined('MOODLE_INTERNAL') || die();

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use local_debugbar\db\query_store;

/**
 * Collects SQL queries executed via Moodle's database layer.
 *
 * @package    local_debugbar
 */
class MoodleSqlCollector extends DataCollector implements Renderable, AssetProvider {

    /**
     * @return string
     */
    public function getName() {
        return 'queries';
    }

    /**
     * @return array
     */
    public function collect() {
        $statements = [];
        $totalduration = 0.0;
        $failed = 0;

        foreach (query_store::$queries as $query) {
            $duration = $query['duration'] ?? 0;
            $totalduration += $duration;
            $success = $query['success'] ?? true;

            if (!$success) {
                $failed++;
            }

            $sql = $query['sql'] ?? '';
            $params = $query['params'] ?? [];
            $statements[] = [
                'sql' => $this->renderSqlWithParams($sql, $params),
                'params' => (object)$params,
                'duration' => $duration,
                'duration_str' => $this->getDataFormatter()->formatDuration($duration),
                'is_success' => $success,
                'error_code' => 0,
                'error_message' => $query['error'] ?? '',
            ];
        }

        return [
            'nb_statements' => query_store::$total_count,
            'nb_failed_statements' => $failed,
            'accumulated_duration' => $totalduration,
            'accumulated_duration_str' => $this->getDataFormatter()->formatDuration($totalduration),
            'statements' => $statements,
        ];
    }

    /**
     * Replace placeholders in SQL with actual parameter values.
     *
     * @param string $sql
     * @param array $params
     * @return string
     */
    private function renderSqlWithParams(string $sql, array $params): string {
        if (empty($params)) {
            return $sql;
        }

        if (strpos($sql, '?') !== false) {
            foreach ($params as $value) {
                $sql = preg_replace('/\?/', $this->quoteParam($value), $sql, 1);
            }

            return $sql;
        }

        // Moodle uses named params like :param0, :param1.
        // Sort by key length descending to avoid :param1 replacing part of :param10.
        $keys = array_keys($params);
        usort($keys, function ($a, $b) {
            return strlen((string)$b) - strlen((string)$a);
        });

        foreach ($keys as $key) {
            $value = $params[$key];
            $placeholder = ':' . $key;
            $sql = str_replace($placeholder, $this->quoteParam($value), $sql);
        }

        return $sql;
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function quoteParam($value): string {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        return "'" . addslashes((string)$value) . "'";
    }

    /**
     * @return array
     */
    public function getWidgets() {
        return [
            'database' => [
                'icon' => 'database',
                'widget' => 'PhpDebugBar.Widgets.SQLQueriesWidget',
                'map' => 'queries',
                'default' => '[]',
            ],
            'database:badge' => [
                'map' => 'queries.nb_statements',
                'default' => 0,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getAssets() {
        return [
            'css' => 'widgets/sqlqueries/widget.css',
            'js' => 'widgets/sqlqueries/widget.js',
        ];
    }
}
