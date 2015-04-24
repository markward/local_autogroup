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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/outputrenderers.php');

class local_autogroup_renderer extends plugin_renderer_base
{
    const URL_COURSE_SETTINGS = '/local/autogroup/edit.php';
    const URL_COURSE_MANAGE = '/local/autogroup/manage.php';

    public function intro_text($count = 0) {
        $output = '';

        $text = html_writer::tag('p', get_string('autogroupdescription', 'local_autogroup'));

        if (!$count) {
            $text .= html_writer::tag('p', get_string('newsettingsintro', 'local_autogroup'));
        }
        else {
            $text .= html_writer::tag('p', get_string('updatesettingsintro', 'local_autogroup', $count));
        }

        $output .= $this->heading(get_string('pluginname', 'local_autogroup'), 2);
        $output .= $this->box($text);

        return $output;
    }

}