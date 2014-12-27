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
 * A user object relates to a real Moodle user; it acts as a container
 * for multiple courses which in turn contain multiple groups.
 * Initialising a course object will automatically load each autogroup
 * group which could be relevant for a user into memory.
 *
 * A user is also a group member; a membership register is also maintained
 * by this class.
 *
 * @package    local
 * @subpackage autogroup
 * @author     Mark Ward (me@moodlemark.com)
 * @date       December 2014
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_autogroup\domain;

use local_autogroup\domain;
use local_autogroup\exception;

/**
 * Class user
 * @package local_autogroup\domain
 */
class user extends domain
{

    public function __construct ($user, \moodle_database $db, $lazyload = false)
    {
        //get the id for this user
        $this->parse_user_id($user);

        //register which autogroup groups this user is a member of currently
        $this->get_group_membership($db);

        //if applicable, load courses this user is on and their autogroup groups
        if(!$lazyload){
            $this->get_courses($db);
        }

        return true;
    }

    private function get_courses(\moodle_database $db)
    {
        $sql = "SELECT e.courseid".PHP_EOL
            ."FROM {enrol} e".PHP_EOL
            ."LEFT JOIN {user_enrolments} ue".PHP_EOL
            ."ON ue.enrol = e.id".PHP_EOL
            ."WHERE ue.userid = :userid";
        $param = array('userid' => $this->get_id());

        $this->courses = $db->get_fieldset_sql($sql,$param);
    }

    private function get_group_membership(\moodle_database $db)
    {
        $sql = "SELECT g.id, g.courseid".PHP_EOL
              ."FROM {groups} g".PHP_EOL
              ."LEFT JOIN {group_members} gm".PHP_EOL
              ."ON gm.groupid = g.id".PHP_EOL
              ."WHERE gm.userid = :userid".PHP_EOL
              ."AND ".$db->sql_like('g.idnumber', 'autogroup|%');
        $param = array('userid' => $this->get_id());

        $this->membership = $db->get_records_sql_menu($sql,$param);
    }

    private function parse_user_id ($user)
    {
        if(is_int($user) && $user > 0){
            $this->set_id($user);
            return true;
        }

        if(is_object($user) && isset($user->id) && $user->id > 0){
            $this->set_id($user->id);
            return true;
        }

        throw new exception\invalid_user_argument($user);
    }

    private $membership = array();
    private $courses = array();
}