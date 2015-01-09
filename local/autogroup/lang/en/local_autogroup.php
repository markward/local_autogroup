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

$string['pluginname']  = 'Auto Group';

// Course Settings
$string['coursesettings']  = 'Auto Groups';
$string['coursesettingstitle']  = 'Auto Groups: {$a}';

$string['autogroupdescription'] = '"Autogroups" will automatically assign your users to groups within a course based upon information within their user profile.';
$string['newsettingsintro'] = 'To start grouping your users, simply select a profile field from the "Group by" option below and click "Save Changes".';
$string['updatesettingsintro'] = 'This course is already grouping users by "{$a}". You can either change this to a new field or select "Don\'t group users". Remember, doing this will remove any older auto groups unless you select "no" for "Clean up old groups?"';

$string['groupby'] = 'Group by';
$string['dontgroup'] = "Don't group users";
$string['cleanupold'] = 'Clean up old groups?';

$string['roles'] = 'Eligible Roles';

// Admin Settings
$string['addtonewcourses'] = 'Add to new courses';
$string['defaults'] = 'Default Settings';
$string['defaultroles'] = 'Default Eligible Roles';
$string['enabled'] = 'Enabled';
$string['general'] = 'General Configuration';
$string['strict'] = 'Strict Enforcement';
$string['strict_info'] = 'Monitor additional events such as "group member removed" to ensure that users are always in their correct groups.';

// Capabilities
$string['autogroup:managecourse']  = 'Manage autogroup settings on course';

// Sort profile field options
$string['auth'] = "Authentication Method";
$string['department'] = "Department";
$string['institution'] = "Institution";
$string['lang'] = "Preferred Language";