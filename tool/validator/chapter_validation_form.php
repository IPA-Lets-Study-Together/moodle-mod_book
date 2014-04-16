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
 * Book validation
 *
 * @package    booktool_validator
 * @copyright  2014 Ivana Skelic, Hrvoje Golcic 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once(dirname(__FILE__).'/locallib.php');

class chapter_validation_form extends moodleform {

	function definition() {
		global $CFG;

		$book = $this->_customdata['book'];
		$chapter = $this->_customdata['chapter'];

		$mform = $this->_form;

		$chapter_name = chapter_getname($book, $chapter->id);

		$mform->addElement('static', 'event_chapter_notvalidated', $chapter_name, get_string('event_chapter_notvalidated', 'booktool_validator'));

		$buttonarray=array();
		$buttonarray[] =& $mform->createElement('submit', 'validate', get_string('validate', 'booktool_validator'));
		$buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'));
		
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);


	}
}