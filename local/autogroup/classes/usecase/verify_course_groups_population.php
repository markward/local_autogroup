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

namespace local_autogroup\usecase;

use local_autogroup\usecase;
use local_autogroup\domain;

class verify_course_groups_population extends usecase
{
    public function __construct($courseid, \moodle_database $db){
        $this->course = new domain\course($courseid, $db);
    }

    public function __invoke(){
        $groupmemberships = $this->course->get_membership_counts();

        foreach($groupmemberships as $groupid => $groupmembership){
            if($groupmembership == 0){
                $this->course->remove_group($groupid);
            }
        }
    }

    private $course;
}