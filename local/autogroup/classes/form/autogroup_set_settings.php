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

namespace local_autogroup\form;

use local_autogroup\domain;
use local_autogroup\form;
use \html_writer;

/**
 * Class course_settings
 * @package local_autogroup\form
 */
class autogroup_set_settings extends form {
    /**
     *
     */
    public function definition() {
        $this->autogroup_set = $this->get_submitted_data();

        $this->add_text_intro();
        $this->add_group_by_options();

        $this->add_action_buttons();
    }

    /**
     *
     */
    public function extract_data() {
        $data = array();
        $this->set_data($data);
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        return parent::validation($data, $files);
    }

    /**
     * @return object
     */
    public function get_data() {
        return parent::get_data();
    }

    private function add_group_by_options(){
        $mform = & $this->_form;

        $options = array('dontgroup'=>get_string('dontgroup', 'local_autogroup'));

        $options = $options + $this->_customdata->get_group_by_options();

        $mform->addElement('select', 'groupby', get_string('groupby','local_autogroup'), $options);
        $mform->setDefault('groupby', $this->_customdata->grouping_by());

        if($this->_customdata->exists()) {
            //offer to preserve existing groups
            $mform->addElement('selectyesno', 'cleanupold', get_string('cleanupold','local_autogroup'));
            $mform->setDefault('cleanupold', 1);
        }
    }

    /**
     * @throws \coding_exception
     */
    private function add_text_intro(){
        $mform = & $this->_form;

        $mform->addElement('header', 'intro', get_string('pluginname', 'local_autogroup'));
        $mform->setExpanded('intro');
        $mform->addElement('html', html_writer::tag('p',get_string('autogroupdescription', 'local_autogroup')));

        if(!$this->_customdata->exists()){
            //this hasn't been configured yet
            $mform->addElement('html', get_string('newsettingsintro', 'local_autogroup'));
        }
        else {
            $groupedby = get_string($this->_customdata->grouping_by(), 'local_autogroup');
            //this already has a configuration
            $mform->addElement('html', get_string('updatesettingsintro', 'local_autogroup', $groupedby));
        }

        $mform->addElement('html', html_writer::empty_tag('hr'));
    }

    /**
     * @type domain\autogroup_set
     */
    protected $_customdata;
}