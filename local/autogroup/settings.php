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
 * A course object relates to a Moodle course and acts as a container
 * for multiple groups. Initialising a course object will automatically
 * load each autogroup group for that course into memory.
 *
 * @package    local
 * @subpackage autogroup
 * @author     Mark Ward (me@moodlemark.com)
 * @date       January 2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/lib.php');

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_autogroup',
        get_string('pluginname', 'local_autogroup')
    );

    // general settings
    $settings->add(
        new admin_setting_heading(
            'local_autogroup/general',
            get_string('general', 'local_autogroup'),
            ''
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'local_autogroup/enabled',
            get_string('enabled', 'local_autogroup'),
            '',
            true
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'local_autogroup/strict',
            get_string('strict', 'local_autogroup'),
            get_string('strict_info', 'local_autogroup'),
            false
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'local_autogroup/addtonewcourses',
            get_string('addtonewcourses', 'local_autogroup'),
            '',
            false
        )
    );

    // default settings
    $settings->add(
        new admin_setting_heading(
            'local_autogroup/defaults',
            get_string('defaults', 'local_autogroup'),
            ''
        )
    );
    //TODO: This will eventually need reworking to allow for properly dynamic sort modules
    $choices = array(
        'auth' => get_string('auth', 'local_autogroup'),
        'department' => get_string('department', 'local_autogroup'),
        'institution' => get_string('institution', 'local_autogroup'),
        'lang' => get_string('lang', 'local_autogroup')
    );
    $settings->add(
        new admin_setting_configselect(
            'local_autogroup/filter',
            get_string('groupby', 'local_autogroup'),
            '',
            'department',
            $choices
        )
    );

    // default roles
    $settings->add(
        new admin_setting_heading(
            'local_autogroup/roleconfig',
            get_string('defaultroles', 'local_autogroup'),
            ''
        )
    );

    if ($roles = \get_all_roles()) {
        $roles = \role_fix_names($roles, null, ROLENAME_ORIGINAL);
        $assignableroles = \get_roles_for_contextlevels(CONTEXT_COURSE);
        foreach ($roles as $role) {
            //default should be true for students
            $default = ($role->id == 5);

            $settings->add(
                new admin_setting_configcheckbox(
                    'local_autogroup/eligiblerole_'.$role->id,
                    $role->localname,
                    '',
                    $default
                )
            );
        }
    }

    $ADMIN->add('localplugins', $settings);
}