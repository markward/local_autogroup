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

include_once(__DIR__ . '/locallib.php');

/**
 * Generates the course settings navigation
 *
 * @param settings_navigation $settingsnav
 * @param context $context
 * @return bool
 * @throws coding_exception
 */
function local_autogroup_extends_settings_navigation(settings_navigation $settingsnav, context $context){
    if(!local_autogroup\plugin_is_enabled()){
        return false;
    }

    global $PAGE, $SITE;

    $course = $PAGE->course;

    if($course->id != $SITE->id && ($course->groupmode || !$course->groupmodeforce)) {

        if(has_capability('local/autogroup:managecourse', $context)) {
            if($groupnode = $settingsnav->find('groups', navigation_node::TYPE_SETTING)) {
                $url = new moodle_url('/local/autogroup/edit.php', array('courseid' => $course->id));

                $linknode = $groupnode->add(
                    get_string('coursesettings', 'local_autogroup'),
                    $url,
                    navigation_node::TYPE_SETTING,
                    null,
                    'groups',
                    new pix_icon('i/group', '')
                );


                if (strstr($PAGE->url, 'local/autogroup/')) {
                    $linknode->make_active();
                }
            }
        }

    }
}