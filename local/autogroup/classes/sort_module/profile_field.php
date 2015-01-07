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

namespace local_autogroup\sort_module;

use local_autogroup\sort_module;
use local_autogroup\exception;

/**
 * Class course
 * @package local_autogroup\domain
 */
class profile_field extends sort_module
{
    /**
     * @param \stdclass $user
     * @param int $courseid
     * @param string $config
     */
    public function __construct(\stdclass $user, $courseid, $config)
    {
        $config = json_decode($config);
        $this->field = $config->field;
        $this->user = $user;
    }

    /**
     * @return array $result
     */
    public function eligible_groups()
    {
        $field = $this->field;
        if (isset($this->user->$field) && !empty($this->user->$field)){
            return array($this->user->$field);
        }
        else {
            return array();
        }
    }

    /**
     * @return string
     */
    public function grouping_by(){
        return (string) $this->field;
    }

    /**
     * @param \stdclass $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @param mixed $courseid
     */
    public function setCourseid($courseid)
    {
        $this->courseid = $courseid;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @var \stdclass object
     */
    protected $user;

    /**
     * @var string
     */
    private $field = '';

}