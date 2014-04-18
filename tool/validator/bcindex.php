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

$id        = required_param('id', PARAM_INT);           // Course Module ID
$chapterid = required_param('chapterid', PARAM_INT); // Chapter ID
$pagenum    = optional_param('pagenum', 0, PARAM_INT);
$subchapter = optional_param('subchapter', 0, PARAM_BOOL);

$cm = get_coursemodule_from_id('book', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$book = $DB->get_record('book', array('id'=>$cm->instance), '*', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
/*require_capability('mod/book:read', $context);
require_capability('mod/book:edit', $context);*/

require_capability('booktool/validator:validate', $context);

$PAGE->set_url('/mod/book/tool/validator/bcindex.html', array('id'=>$id, 'chapterid'=>$chapterid));
$PAGE->set_pagelayout('admin'); // TODO: Something. This is a bloody hack!

//check if data exists in the sub-plugin table, create new data if doesn't exist

if ( !$DB->record_exists('booktool_validator', array('bookid'=>$book->id, 'chapterid'=>$chapterid)) ) {

    $record = new stdClass();

    $record->bookid = $book->id;
    $record->chapterid = $chapterid;

    if (!chapter_checkvalidation($book, $chapterid)) {
        
        $record->validated = 0;
        $record->faults = cnt_faults($book, $chapter);
        $DB->insert_record('booktool_validator', $record, false);
    } else {

        $record->validated = 1;
        $record->faults = 0;
        $DB->insert_record('booktool_validator', $record, false);

    } 

} 

$pagenum_query = 'SELECT pagenum FROM {book_chapters} WHERE id = ?';
$pagenum_params = array($chapterid);
$pagenum = $DB->get_records_sql($pagenum_query, $pagenum_params);

$subchapter_query = 'SELECT subchapter FROM {book_chapters} WHERE id = ?';
$subchapter_params = array($chapterid);
$subchapter = $DB->get_records_sql($subchapter_query, $subchapter_params);

$chapter = $DB->get_record('book_chapters', array('id'=>$chapterid, 'bookid'=>$book->id), '*', MUST_EXIST);
$chapter->cmid = $cm->id;

$options = array('noclean'=>true, 'subdirs'=>true, 'maxfiles'=>-1, 'maxbytes'=>0, 'context'=>$context);

$chapter = file_prepare_standard_editor($chapter, 'content', $options, $context, 'mod_book', 'chapter', $chapter->id);

if (($DB->get_field('booktool_validator', 'validated', array('bookid'=>$book->id, 'chapterid'=>$chapterid), MUST_EXIST)) == '0' ) {
    
    $mform = new book_chapter_edit_form(null, array('chapter'=>$chapter, 'options'=>$options));

    //If data submitted, process and store

    if ($mform->is_cancelled()) {
        if (empty($chapter->id)) {
            redirect("view.php?id=$cm->id");
        } else {
            redirect("view.php?id=$cm->id&chapterid=$chapter->id");
        }

    } else if ($data = $mform->get_data()) {
        
        //store the files
        $data->timemodified = time();
        $data = file_postupdate_standard_editor($data, 'content', $options, $context, 'mod_book', 'chapter', $data->id);
        $DB->update_record('book_chapters', $data);
        $DB->set_field('book', 'revision', $book->revision+1, array('id'=>$book->id));

        add_to_log($course->id, 'course', 'update mod', '../mod/book/view.php?id='.$cm->id, 'book '.$book->id);
        $params = array(
            'context' => $context,
            'objectid' => $data->id
        );
        $event = \mod_book\event\chapter_updated::create($params);
        $event->add_record_snapshot('book_chapters', $data);
        $event->trigger();

        book_preload_chapters($book); // fix structure
        redirect("view.php?id=$cm->id&chapterid=$data->id");

        $chapter = $DB->get_record('book_chapters', array('id'=>$chapterid, 'bookid'=>$book->id), '*', MUST_EXIST);

        if (chapter_checkvalidation($book, $chapterid)) {

            $record->bookid = $book->id;
            $record->chapterid = $chapterid;
            $record->validated = 1;
            $record->faults = 0;

            $DB->update_record('booktool_validator', $record);
        }

    }
}

// Otherwise fill and print the form.
$PAGE->set_title($book->name);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($book->name);

$mform->display();

echo $OUTPUT->footer();