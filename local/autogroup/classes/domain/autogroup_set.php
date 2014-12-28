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

/**
 * Class sort
 * @package local_autogroup\domain
 */
class autogroup_set extends domain
{
    /**
     * @param stdclass $settings
     * @param \moodle_database $db
     * @param bool $lazyload
     * @throws exception\invalid_course_argument
     */
    public function __construct ($settings, \moodle_database $db)
    {
        //get the id for this course
        $this->parse_settings($settings);

        //load autogroup groups for this autogroup set
        $this->get_autogroups($db);
    }

    /**
     * @return array
     */
    public function get_membership_counts(){
        $result = array();
        foreach($this->groups as $groupid => $group){
            $result[$groupid] = $group->membership_count();
        }
        return $result;
    }

    /**
     * @param \stdclass $settings
     * @return bool
     * @throws exception\invalid_autogroup_set_argument
     */
    private function parse_settings ($settings)
    {
        if(is_object($settings)){

            foreach($this->attributes as $attribute){
                if (isset($settings->$attribute)){
                    $this->$attribute = $settings->$attribute;
                }
                else {
                    throw new exception\invalid_autogroup_set_argument($settings);
                }
            }

        } else {
            throw new exception\invalid_autogroup_set_argument($settings);
        }
    }

    /**
     * @param \moodle_database $db
     */
    private function get_autogroups(\moodle_database $db){
        $sql = "SELECT g.id".PHP_EOL
            ."FROM {groups} g".PHP_EOL
            ."WHERE g.courseid = :courseid".PHP_EOL
            ."AND ".$db->sql_like('g.idnumber', ':autogrouptag');
        $param = array(
            'courseid' => $this->courseid,
            'autogrouptag' => 'autogroup|' . $this->id . '|%'
        );

        $this->groups = $db->get_fieldset_sql($sql,$param);

        foreach($this->groups as $k => $groupid){
            try {
                $this->groups[$k] = new domain\group($groupid, $db);
            } catch (exception\invalid_group_argument $e){
                unset($this->groups[$k]);
            }
        }
    }
    /**
     * An array of DB level attributes for an autogroup set
     * used for handling stdclass object conversion.
     *
     * @var array
     */
    protected $attributes = array(
        'id','courseid','sortmodule','sortconfig', 'timecreated','timemodified'
    );

    /**
     * @var int
     */
    protected $courseid = 0;

    /**
     * @var string
     */
    protected $sortmodule = 'profile_field';

    /**
     * @var string
     */
    protected $sortconfig = '';

    /**
     * @var int
     */
    protected $timecreated = 0;

    /**
     * @var int
     */
    protected $timemodified = 0;

    /**
     * @var array
     */
    private $groups = array();

}