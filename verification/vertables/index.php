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
 * Book verification
 *
 * @package    bookverification_vertables
 * @copyright  2014 Ivana Skelic, Hrvoje Golcic 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../../../config.php');
//require_once(dirname(__FILE__).'/locallib.php');

$id 		= required_param('id', PARAM_INT);				// Course Module ID
$chapterid	= optional_param('chapterid', 0, PARAM_INT);	// Chapter ID

// =========================================================================
// security checks START - teachers and students view
// =========================================================================

$cm = get_coursemodule_from_id('book', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$book = $DB->get_record('book', array('id'=>$cm->instance), '*', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/book:read', $context);
require_capability('bookverification/vertables:verify_table', $context);

//Check all variables
if ($chapterid) {
	$chapter = $DB->get_record('book_chapters', array('id'=>$chapterid, 'bookid'=>$book->id), '*', MUST_EXIST);
} else {
	$chapter = false;
}

$PAGE->set_url('/mod/book/verify.php', array('id'=>$id, 'chapterid'=>$chapterid));

unset($id);
unset($chapterid);

// Security checks END

// read chapters



