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

require(dirname(__FILE__).'/../../../../config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/validation_form.php');
require_once(dirname(__FILE__).'/chapter_validation_form.php');

$id = required_param('id', PARAM_INT);           // Course Module ID

$cm = get_coursemodule_from_id('book', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$book = $DB->get_record('book', array('id'=>$cm->instance), '*', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/book:read', $context);
require_capability('booktool/validator:validate', $context);

$PAGE->set_url('/mod/book/tool/validator/bindex.html', array('id'=>$id));

//check if data exists in the sub-plugin table, create new data if doesn't exist

//Fill and print the form
$pagetitle = $book->name . ": " . 

$PAGE->set_title($book->name);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($book->name);

if ( !($DB->record_exists('booktool_validator', array('bookid'=>$book->id))) ) {
    
    $chapterids = $DB->get_records_sql('SELECT id FROM {book_chapters} WHERE bookid = ?', array($book->id));

    foreach ($chapterids as $chapter) {

        $record = new stdClass();

        $record->bookid = $book->id;
        $record->chapterid = $chapter->id;

        if (!chapter_checkvalidation($book, $chapter)) {

            $record->validated = 0;
            $record->faults = cnt_faults($book, $chapter);
            $DB->insert_record('booktool_validator', $record, false);

            $link = new action_link();
            $link->url = new moodle_url('/mod/book/tool/validator/bcindex.php', 
            array('id' => $book->id, 'chapterid' => $chapter->id));
            $link->text = get_string('val_chapter', 'booktool_validator'); // Required
            echo $OUTPUT->link($link);

        } else {

            $record->validated = 1;
            $record->faults = 0;
            $DB->insert_record('booktool_validator', $record, false);

        }

                
    }

} 

echo $OUTPUT->footer();
