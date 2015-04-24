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
 * These strings are used throughout the user interface and can
 * be overridden by the user through the language customisation
 * tool.
 *
 * @package    local
 * @subpackage autogroup
 * @author     Mark Ward (me@moodlemark.com)
 * @date       December 2014
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname']  = 'Auto Group';

// Course Settings
$string['actions'] = 'Actions';

$string['coursesettings']  = 'Auto Groups';
$string['coursesettingstitle']  = 'Auto Groups: {$a}';

$string['autogroupdescription'] = '"Auto Groups" will automatically assign your users to groups within a course based upon information within their user profile.';
$string['newsettingsintro'] = 'To start grouping your users, simply select a profile field from the "Group by" option below and click "Save Changes".';
$string['updatesettingsintro'] = 'This course is already grouping users with {$a} rule set(s). You can either edit or remove these existing rule sets, or add a new one to the course. ';

$string['dontgroup'] = "Don't group users";
$string['cleanupold'] = 'Clean up old groups?';

$string['set_type'] = 'Group set type';
$string['set_groups'] = 'Number of groups';
$string['set_roles'] = 'Eligible Roles';
$string['set_groupby'] = 'Group by';

$string['confirmdelete'] = 'Are you sure you wish to remove this auto group set?';

$string['create'] = 'Create new group set:';

// Admin Settings
$string['addtonewcourses'] = 'Add to new courses';
$string['addtorestoredcourses'] = 'Add to restored courses';
$string['defaults'] = 'Default Settings';
$string['defaultroles'] = 'Default Eligible Roles';
$string['enabled'] = 'Enabled';
$string['general'] = 'General Configuration';
$string['strict'] = 'Strict Enforcement';
$string['strict_info'] = 'Monitor additional events such as "group member removed" to ensure that users are always in their correct groups. Enabling this option has a performance impact.';

// Capabilities
$string['autogroup:managecourse']  = 'Manage autogroup settings on course';

// Sort profile field options
$string['auth'] = "Authentication Method";
$string['department'] = "Department";
$string['institution'] = "Institution";
$string['lang'] = "Preferred Language";