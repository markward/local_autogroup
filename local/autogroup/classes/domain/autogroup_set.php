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
use local_autogroup\sort_module;
use moodle_database;
use stdClass;

require_once(__DIR__ . "/../../../../group/lib.php" );

/**
 * Class sort
 * @package local_autogroup\domain
 */
class autogroup_set extends domain
{
    /**
     * @param \stdclass $autogroupset
     * @param \moodle_database $db
     * @param bool $lazyload
     * @throws exception\invalid_autogroup_set_argument
     */
    public function __construct (\moodle_database $db, $autogroupset = null)
    {
        //set the sortconfig as empty
        $this->sortconfig = new stdClass();

        //get the id for this course
        if( $this->validate_object($autogroupset) ){
            $this->load_from_object($autogroupset);
        }

        $this->initialise();

        if( $this->exists() ) {
            //load autogroup groups for this autogroup set
            $this->get_autogroups($db);
        }

        $this->roles = $this->retrieve_applicable_roles($db);
    }

    /**
     * @return array
     */
    public function get_membership_counts()
    {
        $result = array();
        foreach($this->groups as $groupid => $group){
            $result[$groupid] = $group->membership_count();
        }
        return $result;
    }

    /**
     * @param \moodle_database $db
     * @return bool
     */
    public function create(\moodle_database $db)
    {
        return $this->save($db);
    }

    /**
     * @param \moodle_database $db
     * @return bool
     */
    public function delete(\moodle_database $db, $cleanupgroups = true)
    {
        if(!$this->exists()){
            return false;
        }

        //this has to be done first to prevent event handler getting in the way
        $db->delete_records('local_autogroup_set', array('id'=>$this->id));
        $db->delete_records('local_autogroup_roles', array('setid'=>$this->id));

        if($cleanupgroups){
            foreach($this->groups as $k => $group){
                $group->remove();
                unset($this->groups[$k]);
            }
        }
        else {
            $this->disassociate_groups();
        }
    }

    /**
     * Used to unlink generated groups from an autogroup set
     */
    public function disassociate_groups()
    {
        foreach($this->groups as $k => $group){
            $group->idnumber = '';
            $group->update();
            unset($this->groups[$k]);
        }
    }

    /**
     * @return array
     */
    public function get_eligible_roles()
    {
        return $this->roles;
    }


    /**
     * This function builds a list of roles to add and a list of roles to
     * remove, before carrying out the action on the database
     *
     * @param array $roles
     */
    public function set_eligible_roles($newroles, moodle_database $db)
    {
        $rolestoremove = $this->roles;
        $rolestoadd = array();

        foreach ($newroles as $role){
            if ($key = array_search($role, $rolestoremove)){
                //we don't want to remove this from the db
                unset($rolestoremove[$key]);
            }
            else {
                //we want to add this to the db
                $newrow = new stdClass();
                $newrow->setid = $this->id;
                $newrow->roleid = $role;
                $rolestoadd[] = $newrow;
            }
        }

        if ( count($rolestoremove) || count($rolestoadd) ) {
            //if there are changes to make do them and return true
            $db->delete_records_list('local_autogroup_roles', 'roleid', $rolestoremove);

            $db->insert_records('local_autogroup_roles', $rolestoadd);

            return true;
        }
        return false;
    }

    /**
     * Returns the options to be displayed on the autgroup_set
     * editing form. These are defined per-module.
     *
     * @return array
     */
    public function get_group_by_options()
    {
        return $this->sortmodule->get_config_options();
    }

    /**
     * @return string
     */
    public function grouping_by()
    {
        return $this->sortmodule->grouping_by();
    }

    /**
     * Save or create this autogroup set to the database
     *
     * @param moodle_database $db
     */
    public function save(moodle_database $db, $cleanupold = true)
    {
        $this->update_timestamps();

        $data = $this->as_object();
        $data->sortconfig = json_encode($data->sortconfig);
        if($this->exists()){
            $db->update_record('local_autogroup_set', $data);
        }
        else{
            $this->id = $db->insert_record('local_autogroup_set', $data);
            $this->roles = $this->retrieve_applicable_roles($db);
        }

        //if the user wants to preserve old groups we will need to detatch them now
        if(!$cleanupold){
            $this->disassociate_groups();
        }
    }

    /**
     * @param int $courseid
     */
    public function set_course($courseid)
    {
        if(is_numeric($courseid) && (int) $courseid > 0){
            $this->courseid = $courseid;
        }
    }

    /**
     * @param stdClass $options
     */
    public function set_options(stdClass $config){
        if($this->sortmodule->config_is_valid($config)){
            $this->sortconfig = $config;

            //reinit since the old sortmodule may be out of date
            $this->initialise();
        }
    }

    /**
     * @param \stdclass $user
     * @param \moodle_database $db
     * @param \context_course $context
     * @return bool
     */
    public function verify_user_group_membership(\stdclass $user, \moodle_database $db, \context_course $context)
    {
        $eligiblegroups = array();

        //we only want to check with the sorting module if this user has the correct role assignment
        if($this->user_is_eligible_in_context($user->id, $db, $context)) {
            //an array of strings from the sort module
            $eligiblegroups = $this->sortmodule->eligible_groups_for_user($user);
        }

        //an array of groupids which will be populated as we ensure membership
        $validgroups = array();

        foreach ($eligiblegroups as $eligiblegroup){
            if($group = $this->get_or_create_group_by_idnumber($eligiblegroup, $db)) {
                $validgroups[] = $group->id;
                $group->ensure_user_is_member($user->id);
            }
        }

        //now run through other groups and ensure user is not a member
        foreach($this->groups as $key => $group){
            if(!in_array($group->id,$validgroups)){
                $group->ensure_user_is_not_member($user->id);
            }
        }

        return true;
    }

    /**
     * @param \moodle_database $db
     */
    private function get_autogroups(\moodle_database $db)
    {
        $sql = "SELECT g.*".PHP_EOL
            ."FROM {groups} g".PHP_EOL
            ."WHERE g.courseid = :courseid".PHP_EOL
            ."AND ".$db->sql_like('g.idnumber', ':autogrouptag');
        $param = array(
            'courseid' => $this->courseid,
            'autogrouptag' => $this->generate_group_idnumber('%')
        );

        $this->groups = $db->get_records_sql($sql,$param);

        foreach($this->groups as $k => $group){
            try {
                $this->groups[$k] = new domain\group($group, $db);
            } catch (exception\invalid_group_argument $e){
                unset($this->groups[$k]);
            }
        }
    }

    /**
     * @param string $groupname
     * @param \moodle_database $db
     * @return bool|domain/group
     */
    private function get_or_create_group_by_idnumber($groupname, \moodle_database $db)
    {
        $idnumber = $this->generate_group_idnumber($groupname);
        $result = null;

        //firstly run through existing groups and check for matches
        foreach($this->groups as $group){
            if($group->idnumber == $idnumber){
                return $group;
            }
        }

        //if we don't find a match, create a new group.
        $data = new \stdclass();
        $data->id = 0;
        $data->name = $groupname;
        $data->idnumber = $idnumber;
        $data->courseid = $this->courseid;
        $data->description = '';
        $data->descriptionformat = 0;
        $data->enrolmentkey = null;
        $data->picture = 0;
        $data->hidepicture = 0;

        try {
            $newgroup = new domain\group($data, $db);
            $newgroup->create();
            $this->groups[$newgroup->id] = $newgroup;
        } catch (exception\invalid_group_argument $e){
            return false;
        }

        return $this->groups[$newgroup->id];
    }

    /**
     *
     */
    private function initialise(){
        $this->sortmodule = new $this->sortmodulename($this->sortconfig, $this->courseid);
    }

    /**
     * @param \stdclass $autogroupset
     */
    private function load_from_object(\stdclass $autogroupset)
    {
        $this->id = (int) $autogroupset->id;

        $this->courseid = (int) $autogroupset->courseid;

        if(isset($autogroupset->sortmodule)) {
            $sortmodulename = 'local_autogroup\\sort_module\\' . $autogroupset->sortmodule;
            if (class_exists($sortmodulename)){
                $this->sortmodulename = $sortmodulename;
                $this->sortmoduleshortname = $autogroupset->sortmodule;
            }
        }

        if(isset($autogroupset->sortconfig)) {
            $sortconfig = json_decode($autogroupset->sortconfig);
            if(json_last_error() == JSON_ERROR_NONE) {
                $this->sortconfig = $sortconfig;
            }
        }

        if(isset($autogroupset->timecreated)){
            $this->timecreated = $autogroupset->timecreated;
        }
        if(isset($autogroupset->timemodified)){
            $this->timemodified = $autogroupset->timemodified;
        }
    }

    /**
     * @param \moodle_database $db
     * @return array  role ids which should be added to the group
     */
    private function retrieve_applicable_roles(\moodle_database $db)
    {
        $roles = $db->get_records_menu('local_autogroup_roles', array('setid'=>$this->id), 'id', 'id, roleid');

        if (empty($roles) && !$this->exists()){
            $roles = $this->retrieve_default_roles();
        }

        return $roles;
    }

    /**
     * @return array  default eligible roleids
     */
    private function retrieve_default_roles()
    {
        $config = \get_config('local_autogroup');
        if ($roles = \get_all_roles()) {
            $roles = \role_fix_names($roles, null, ROLENAME_ORIGINAL);
            $newroles = array();
            foreach ($roles as $role) {
                $attributename = 'eligiblerole_' . $role->id;
                if (isset($config->$attributename) && $config->$attributename) {
                    $newroles[] = $role->id;
                }
            }
            return $newroles;
        }
        return false;
    }

    /**
     * @return \stdclass $autogroupset
     */
    private function as_object()
    {
        $autogroupset = new \stdclass();
        foreach($this->attributes as $attribute){
            $autogroupset->$attribute = $this->$attribute;
        }

        // this is a special case because we dont want
        // to export the sort module, just the name of the module
        $autogroupset->sortmodule = $this->sortmoduleshortname;

        return $autogroupset;
    }

    /**
     * @param \stdclass $autogroupset
     * @return bool
     */
    private function validate_object($autogroupset)
    {
        return is_object($autogroupset)
        && isset($autogroupset->id)
        && $autogroupset->id >= 0
        && isset($autogroupset->courseid)
        && $autogroupset->courseid > 0;
    }

    /**
     * @param string $groupname
     * @return string
     */
    private function generate_group_idnumber($groupname)
    {
        //generate the idnumber for this group
        $idnumber = implode('|',
            array(
                'autogroup',
                $this->id,
                $groupname
            )
        );
        return $idnumber;
    }

    /**
     * Whether or not the user is eligible to be grouped
     * by this autogroup set
     *
     * @param int $userid
     * @param \moodle_database $db
     * @param \context_course $context
     * @return bool
     */
    private function user_is_eligible_in_context($userid, \moodle_database $db, \context_course $context)
    {
        $roleassignments = \get_user_roles($context,$userid);

        foreach($roleassignments as $role){
            if(in_array($role->roleid, $this->roles)){
                return true;
            }
        }
        return false;
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
     * @var sort_module
     */
    protected $sortmodule;

    /**
     * @var string
     */
    protected $sortmodulename = 'local_autogroup\\sort_module\\profile_field';

    /**
     * @var string
     */
    protected $sortmoduleshortname = 'profile_field';

    /**
     * @var stdClass
     */
    protected $sortconfig;

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

    /**
     * @var array
     */
    private $roles = array();

}