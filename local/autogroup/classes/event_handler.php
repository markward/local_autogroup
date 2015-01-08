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

namespace local_autogroup;

use \core\event;
use \local_autogroup\usecase;

class event_handler
{
    public static function user_enrolment_created(event\user_enrolment_created $event)
    {
        global $DB;

        $courseid = (int) $event->courseid;
        $userid = (int) $event->relateduserid;

        $usecase = new usecase\verify_user_group_membership($userid, $DB, $courseid);
        return $usecase();
    }

    public static function group_member_added(event\group_member_added $event)
    {
        global $DB;

        $courseid = (int) $event->courseid;
        $userid = (int) $event->relateduserid;

        $usecase = new usecase\verify_user_group_membership($userid, $DB, $courseid);
        return $usecase();
    }

    public static function group_member_removed(event\group_member_removed $event)
    {
        global $DB, $PAGE;

        $groupid = (int) $event->objectid;
        $courseid = (int) $event->courseid;
        $userid = (int) $event->relateduserid;

        $usecase1 = new usecase\verify_user_group_membership($userid, $DB, $courseid);
        $usecase1();


        $usecase2 = new usecase\verify_group_population($groupid, $DB, $PAGE);
        $usecase2();
        return true;
    }

    public static function user_updated(event\user_updated $event)
    {
        global $DB;

        $userid = (int) $event->relateduserid;

        $usecase = new usecase\verify_user_group_membership($userid, $DB);
        return $usecase();
    }

    public static function group_change(event\base $event)
    {
        //TODO: ensure this is not executed after verify_group_population deletes a group

        global $DB;

        $courseid = (int) $event->courseid;

        $usecase = new usecase\verify_course_group_membership($courseid, $DB);
        return $usecase();
    }

    public static function role_change(event\base $event)
    {
        global $DB;

        $userid = (int) $event->relateduserid;

        $usecase = new usecase\verify_user_group_membership($userid, $DB);
        return $usecase();
    }

    public static function course_created(event\course_created $event) {
        global $DB;
        $courseid = (int) $event->courseid;

        $usecase = new usecase\add_default_to_course($courseid, $DB);
        return $usecase();
    }

}
