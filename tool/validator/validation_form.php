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
 * Book validation form
 *
 * @package    booktool_validator
 * @copyright  2014 Ivana Skelic, Hrvoje Golcic 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/book/edit_form.php');

class booktool_validator_form extends moodleform {
	function definition() {

		$mform 	= $this->_form;
		$data	= $this->_customdata;

		$mform->addElement('header', 'general', get_string('validatebook', 'booktool_validator'));

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);

		$mform->addElement('hidden', 'chapterid');
		$mform->setType('chapterid', PARAM_INT);



		$this->set_data($data);
	}
}*/

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/book/edit_form.php');

class book_validation_edit_form extends book_chapter_edit_form {

    function definition() {
		
        parent::definition();
    }

    function definition_after_data(){
        $mform = $this->_form;
        $pagenum = $mform->getElement('pagenum');
        if ($pagenum->getValue() == 1) {
            $mform->hardFreeze('subchapter');
        }
    }
}