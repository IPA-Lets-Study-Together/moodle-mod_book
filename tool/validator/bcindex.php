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
require_once($CFG->dirroot.'/mod/book/locallib.php');
require_once($CFG->libdir.'/chromephp/ChromePhp.php');

$cmid           = required_param('cmid', PARAM_INT);            // Course Module ID
$chapterid      = optional_param('chapterid', 0, PARAM_INT);    // Chapter ID
$pagenum        = optional_param('pagenum', 0, PARAM_INT);
$subchapter     = optional_param('subchapter', 0, PARAM_BOOL);

$cm = get_coursemodule_from_id('book', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$book = $DB->get_record('book', array('id'=>$cm->instance), '*', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/book:edit', $context);

/*require_capability('mod/book:read', $context);
require_capability('booktool/validator:validate', $context);*/

//set data
$PAGE->set_url('/mod/book/tool/validator/bcindex.html', array('cmid'=>$cmid, 'chapterid'=>$chapterid));
$PAGE->set_pagelayout('admin'); // TODO: Something. This is a bloody hack!

//check if data exists in the sub-plugin table, create new data if doesn't exist

if ( !$DB->record_exists('booktool_validator', array('bookid'=>$book->id, 'chapterid'=>$chapterid)) && $chapterid != 0 ) {

    $record = new stdClass();

    $record->bookid = $book->id;
    $record->chapterid = $chapterid;

    if (chapter_checkvalidation($book->id, $chapterid) == 0) { //if false
        
        $record->validated = 0;
        $record->faults = cnt_faults($book->id, $chapterid);
        $DB->insert_record('booktool_validator', $record, false);

    } else {

        $record->validated = 1;
        $record->faults = 0;
        $DB->insert_record('booktool_validator', $record, false);
    } 

} 

$chapter = new stdClass();

if ($chapterid) {
    $chapter = $DB->get_record('book_chapters', array('id'=>$chapterid, 'bookid'=>$book->id), '*', MUST_EXIST);
}

$chapter->cmid = $cm->id;

$options = array('noclean'=>true, 'subdirs'=>true, 'maxfiles'=>-1, 'maxbytes'=>0, 'context'=>$context);
$chapter = file_prepare_standard_editor($chapter, 'content', $options, $context, 'mod_book', 'chapter', $chapterid);

$mform = new book_chapter_edit_form(null, array('chapter'=>$chapter, 'options'=>$options));

//If data submitted, process and store

if ($mform->is_cancelled()) {
    if (empty($chapterid)) {
        redirect($CFG->wwwroot . "/mod/book/view.php?id=$cm->id");
    } else {
        redirect($CFG->wwwroot . "/mod/book/view.php?id=$cm->id&chapterid=$chapter->id");
    }

} else if ($data = $mform->get_data()) {

    //var_dump($data);
        
    //store the files
    $data->timemodified = time();
    $data = file_postupdate_standard_editor($data, 'content', $options, $context, 'mod_book', 'chapter', $data->id);
    $DB->update_record('book_chapters', $data);
    $DB->set_field('book', 'revision', $book->revision+1, array('id'=>$book->id));

    //check again
    if ((chapter_checkvalidation($book->id, $data->id) == 1) 
        && ($DB->get_field('booktool_validator', 'validated', array('bookid'=>$book->id, 'chapterid'=>$data->id), MUST_EXIST) == 0)) {

        $validator_id = $DB->get_field('booktool_validator', 'id', array('bookid'=>$book->id, 'chapterid'=>$data->id));
        $DB->set_field('booktool_validator', 'validated', 1, array('id'=>$validator_id));
        $DB->set_field('booktool_validator', 'faults', 0, array('id'=>$validator_id));

    }

    add_to_log($course->id, 'course', 'update mod', '../mod/book/view.php?id='.$cm->id, 'book '.$book->id);
    $params = array(
        'context' => $context,
        'objectid' => $data->id
    );
    $event = \mod_book\event\chapter_updated::create($params);
    $event->add_record_snapshot('book_chapters', $data);
    $event->trigger();  

    book_preload_chapters($book); // fix structure
    redirect($CFG->wwwroot . "/mod/book/view.php?id=$cm->id&chapterid=$data->id");
}

// Otherwise fill and print the form.

$PAGE->set_title($book->name);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($book->name);

if (($DB->get_field('booktool_validator', 'validated', array('bookid'=>$book->id, 'chapterid'=>$chapterid), MUST_EXIST)) == '0' ) {

        echo get_string('event_chapter_notvalidated', 'booktool_validator');
        echo get_string('nof', 'booktool_validator') . $DB->get_field('booktool_validator', 'faults', array('bookid'=>$book->id, 'chapterid'=>$chapterid));
        echo "<br>";

        print_images($book->id, $chapterid);
        echo "<br>";
        print_tables($book->id, $chapterid);
        $mform->display();
} else {
    echo get_string('event_chapter_validated', 'booktool_validator');
}

echo $OUTPUT->footer();
