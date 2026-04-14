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

$string['pluginname'] = 'Debugbar';
$string['configdesc'] = 'Debugbar status: <strong>{$a->enabled}</strong> (Your user ID: {$a->user})';
$string['alloweduserid'] = 'Allowed user ID';
$string['alloweduserid_desc'] = 'Only the user with this Moodle user ID can ever see or activate the Debugbar.';
$string['enablewhendebugging'] = 'Allow activation when debugging is on';
$string['enablewhendebugging_desc'] = 'If enabled, the Debugbar becomes available when Moodle debugging is active (in addition to the user-ID requirement).';
