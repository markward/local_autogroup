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

define('SORT_MODULE_DIR', $CFG->dirroot.'/local/autogroup/classes/sort_module/');

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

/**
 * generates an array list of sort modules
 *
 * @return array
 */
function get_sort_module_list(){
    global $CFG;

    $list = array();

    $files = scandir(SORT_MODULE_DIR);

    foreach($files as $file){
        if(strstr($file, '.php')){
            include_once(SORT_MODULE_DIR . $file);

            $classname = str_replace('.php','',$file);
            $fullname = 'local_autogroup\\sort_module\\'.$classname;

            if(class_exists($fullname)){
                $list[$classname] = sanitise_sort_module_name($classname);
            }
        }
    }

    return $list;
}

function sanitise_sort_module_name($name = ''){

    // for when we are passed the full name
    $name = explode('\\',$name);
    $name = array_pop($name);

    $name = str_replace('_', ' ', $name);
    $name = ucfirst($name);
    return $name;
}