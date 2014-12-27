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
 * @date       December 2014
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_autogroup\domain;

use local_autogroup\domain;
use local_autogroup\exception;

class course extends domain
{
    public function __construct ($course, \moodle_database $db, $lazyload = false)
    {
        //get the id for this course
        $this->parse_course_id($course);

        //load autogroup groups for this course if applicable
        if(!$lazyload){
            $this->get_autogroups($db);
        }
    }

    /**
     * @param object|int $course
     * @return bool
     * @throws exception\invalid_course_argument
     */
    private function parse_course_id ($course)
    {
        if(is_int($course) && $course > 0){
            $this->id = $course;
            return true;
        }

        if(is_object($course) && isset($course->id) && $course->id > 0){
            $this->id = $course->id;
            return true;
        }

        throw new exception\invalid_course_argument($course);
    }

    private function get_autogroups(\moodle_database $db){
        $sql = "SELECT g.id".PHP_EOL
            ."FROM {groups} g".PHP_EOL
            ."WHERE g.courseid = :courseid".PHP_EOL
            ."AND ".$db->sql_like('g.idnumber', 'autogroup|');
        $param = array('courseid' => $this->id);

        $this->autogroups = $db->get_fieldset_sql($sql,$param);

        foreach($this->autogroups as $k => $groupid){
            try {
                $this->autogroups[$k] = new group($groupid, $db);
            } catch (exception\invalid_group_argument $e){
                unset($this->autogroups[$k]);
            }
        }
    }

    private $autogroups = array();

}