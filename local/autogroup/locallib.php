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

/**
 * autogroup local plugin
 *
 * This plugin automatically assigns users to a group within any course
 * upon which they may be enrolled and which has auto-grouping
 * configured.
 *
 * @package    local
 * @subpackage autogroup
 * @author     Mark Ward (me@moodlemark.com)
 * @date       December 2014
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * This file contains functions which are specific to local_autogroup
 * and do not follow any standard moodle convensions.
 *
 * All functions here exist within the local_autogroup namespace which
 * prevents compatability issues with other plugins.
 */

namespace local_autogroup;

/**
 * Checks the plugin config and returns the current status for
 * the "enabled" option
 *
 * @return bool
 * @throws \Exception
 * @throws \dml_exception
 */
function plugin_is_enabled(){
    $config = get_config('local_autogroup');
    return isset($config->enabled) && $config->enabled;
}