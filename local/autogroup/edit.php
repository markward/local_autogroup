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
 * @date       January 2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_autogroup;

require_once(dirname(__FILE__) . '/pageinit.php');

use \local_autogroup\domain;
use \local_autogroup\form;
use \local_autogroup\usecase;
use \local_autogroup_renderer;
use \moodle_url;
use \context_course;
use \stdClass;

$courseid = required_param('courseid', PARAM_INT);
$context = context_course::instance($courseid);

require_capability('local/autogroup:managecourse', $context);

global $PAGE, $DB, $SITE;

if($courseid == $SITE->id || !plugin_is_enabled()){
    //do not allow editing for front page.
    die();
}

$course = $DB->get_record('course', array('id' => $courseid));

//for now each course has a single autogroup set.
$autogroup_set = $DB->get_record('local_autogroup_set', array('courseid'=>$courseid));
$autogroup_set = new domain\autogroup_set($DB, $autogroup_set);
//since it may be a new one we need to tell it what course this is for
$autogroup_set->set_course($courseid);

$heading = \get_string('coursesettingstitle', 'local_autogroup', $course->shortname);

global $PAGE;

$PAGE->set_context($context);
$PAGE->set_url(local_autogroup_renderer::URL_COURSE_SETTINGS, array('courseid'=>$courseid));
$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->set_pagelayout('incourse');
$PAGE->set_course($course);

$output = $PAGE->get_renderer('local_autogroup');

$returnurl = new moodle_url(local_autogroup_renderer::URL_COURSE_SETTINGS, array('courseid'=>$courseid));
$aborturl = new moodle_url('/course/view.php', array('id' => $courseid));

$form = new form\autogroup_set_settings($returnurl, $autogroup_set);

if ($form->is_cancelled()) {
    redirect($aborturl);
}
if ($data = $form->get_data()) {
    //TODO: This will eventually need reworking to allow for properly dynamic sort modules

    $options = new stdClass();
    $options->field = $data->groupby;

    $updategroupmembership = false;

    // a short-hand if statement to handle the possibility the form didn't include the cleanupold option
    $cleanupold = isset($data->cleanupold) ? (bool) $data->cleanupold : true;

    if($options->field === 'dontgroup'){
        // user has selected "dont group"
        $autogroup_set->delete($DB, $cleanupold);

        $autogroup_set = new domain\autogroup_set($DB);
        $autogroup_set->set_course($courseid);

    }
    else if($options->field != $autogroup_set->grouping_by()){
        // user has selected another option
        $autogroup_set->set_options($options);
        $autogroup_set->save($DB, $cleanupold);

        $updategroupmembership = true;
    }

    //check for role settings
    if ($autogroup_set->exists() && $roles = \get_all_roles()) {
        $roles = \role_fix_names($roles, null, ROLENAME_ORIGINAL);
        $newroles = array();
        foreach ($roles as $role){
            $attributename = 'role_'.$role->id;
            if (isset($data->$attributename)){
                $newroles[] = $role->id;
            }
        }

        if($autogroup_set->set_eligible_roles($newroles, $DB)){
            $autogroup_set->save($DB, $cleanupold);

            $updategroupmembership = true;
        }
    }

    if ($updategroupmembership){
        $usecase = new usecase\verify_course_group_membership($courseid, $DB);
        $usecase();
    }

    $form = new form\autogroup_set_settings($returnurl, $autogroup_set);
}

echo $output->header();

$form->display();

echo $output->footer();