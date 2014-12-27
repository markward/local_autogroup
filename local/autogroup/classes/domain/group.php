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
 * A group object relates to a Moodle group and is generally the end
 * point for most usecases.
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
 * Class group
 * @package local_autogroup\domain
 */
class group extends domain
{
    /**
     * @param $group
     * @param \moodle_database $db
     * @throws exception\invalid_group_argument
     */
    public function __construct ($group, \moodle_database $db)
    {
        if(is_int($group) && $group > 0){
            $this->load_from_database($group,$db);
        }

        else if($this->validate_object($group)){
            $this->load_from_stdclass($group);
        }

        else {
            throw new exception\invalid_group_argument($group);
        }
    }

    /**
     * @param $groupid
     * @param \moodle_database $db
     */
    private function load_from_database($groupid, \moodle_database $db)
    {
        $group = $db->get_record('groups',array('id'=>$groupid));
        $this->load_from_stdclass($group);
    }

    /**
     * @param \stdclass $group
     */
    private function load_from_stdclass(\stdclass $group){
        foreach($this->attributes as $attribute){
            $this->$attribute = $group->$attribute;
        }
    }

    /**
     * @param $group
     * @return bool
     */
    private function validate_object($group)
    {
        return is_object($group)
               && isset($group->id)
               && $group->id > 0
               && strlen($group->name) > 0
               && strstr($group->idnumber,'autogroup|');
    }

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var int
     */
    private $courseid = 0;

    /**
     * @var string
     */
    private $idnumber = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var int
     */
    private $descriptionformat = 1;

    /**
     * @var string
     */
    private $enrolmentkey = '';

    /**
     * @var int
     */
    private $picture = 0;

    /**
     * @var int
     */
    private $hidepicture = 0;
    /**
     * @var int
     */
    private $timecreated = 0;

    /**
     * @var int
     */
    private $timemodified = 0;

    /**
     * List of members for this group
     *
     * @var array
     */
    private $members;

    /**
     * An array of DB level attributes for a group
     * used for handling stdclass object conversion.
     *
     * @var array
     */
    private $attributes = array(
        'id','courseid','idnumber','name', 'description', 'descriptionformat',
        'enrolmentkey','picture','hidepicture','timecreated','timemodified'
    );

}