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
 * Class domain
 * @package local_autogroup
 */
abstract class domain {
    /**
     * Child classes will probably override this method.
     * @return string
     */
    public function __toString() {
        return get_class($this) . " " . $this->get_id();
    }

    /**
     * @param $attribute
     * @return int|null
     */
    public function __get($attribute)
    {
        if(!\in_array($attribute, $this->attributes)) {
            return null;
        }

        if($attribute == 'id'){
            return $this->get_id();
        }
        else {
            return $this->$attribute;
        }
    }

    /**
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function __set($attribute,$value)
    {
        if(!in_array($attribute, $this->attributes)) {
            return false;
        }

        if($attribute == 'id'){
            $this->set_id($value);
        }
        else {
            $this->$attribute = $value;
        }

        //timemodified will always reflect the last change
        $this->timemodified = time();
        return true;
    }

    /**
     * @return int
     */
    private function get_id() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    private function set_id($id) {
        $this->id = (int) $id;
    }

    /**
     * @var array
     */
    protected $attributes = array('id');

    /**
     * @type int
     */
    private $id = 0;

    /**
     * @var int
     */
    private $timemodified = 0;
}