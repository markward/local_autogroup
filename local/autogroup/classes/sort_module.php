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

/**
 * An object which can return the valid groups for a given user
 * on a course
 * @package local_autogroup
 */
abstract class sort_module {

    /**
     * @param \stdclass $user
     * @param int $courseid
     * @param string $config
     */
    public abstract function __construct(\stdclass $user, $courseid, $config);

    /**
     * Child classes will probably override this method.
     * @return string
     */
    public function __toString() {
        return get_class($this);
    }

    /**
     * @return array $result
     */
    public abstract function eligible_groups();

    /**
     * @param string $attribute
     * @return array|null
     */
    public function __get($attribute)
    {
        if($attribute = 'groups'){
            return $this->eligible_groups();
        }
        return null;
    }

    /**
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function __set($attribute,$value)
    {
        return false;
    }

    /**
     * @var
     */
    protected $user;

    /**
     * @var
     */
    protected $courseid;

    /**
     * @var array
     */
    protected $config = array();

}