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

require_once($CFG->dirroot . '/local/autogroup/lib.php');

/**
 * Class verify_user_group_membership
 * @package local_autogroup\usecase
 */
class verify_course_group_membership extends usecase
{

    /**
     * @param int $courseid
     * @param \moodle_database $db
     */
    public function __construct($courseid, \moodle_database $db )
    {
        $this->course = new domain\course($courseid, $db);
        $this->db = $db;
    }

    /**
     * @return bool
     */
    public function __invoke()
    {
        if(!\local_autogroup\plugin_is_enabled()){
            return false;
        }

        set_time_limit(500);
        return $this->course->verify_all_group_membership($this->db);
    }

    /**
     * @var domain\user
     */
    protected $course;

    /**
     * @var \moodle_database
     */
    private $db;
}