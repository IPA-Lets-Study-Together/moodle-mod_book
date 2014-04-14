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
 * @package    bookverification_verifier
 * @copyright  2014 Ivana Skelic, Hrvoje Golcic 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../../../config.php');

/*$id        = required_param('id', PARAM_INT);           // Course Module ID
$chapterid = optional_param('chapterid', 0, PARAM_INT); // Chapter ID

$cm = get_coursemodule_from_id('book', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$book = $DB->get_record('book', array('id'=>$cm->instance), '*', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/book:read', $context);
require_capability('bookverification/verifier:verify', $context);

// Check all variables.
if ($chapterid) {
    // Single chapter printing - only visible!
    $chapter = $DB->get_record('book_chapters', array('id'=>$chapterid, 'bookid'=>$book->id), '*', MUST_EXIST);
} else {
    // Complete book.
    $chapter = false;
}

$PAGE->set_url('/mod/book/verify.php', array('id'=>$id, 'chapterid'=>$chapterid));

unset($id);
unset($chapterid);

// read chapters
$chapters = book_preload_chapters($book);

$strbooks = get_string('modulenameplural', 'mod_book');
$strbook  = get_string('modulename', 'mod_book');
$strtop   = get_string('top', 'mod_book');

@header('Cache-Control: private, pre-check=0, post-check=0, max-age=0');
@header('Pragma: no-cache');
@header('Expires: ');
@header('Accept-Ranges: none');
@header('Content-type: text/html; charset=utf-8');

if ($chapter) {

    if ($chapter->hidden) {
        require_capability('mod/book:viewhiddenchapters', $context);
    }

    $params = array(
        'context' => $context,
        'objectid' => $chapter->id
    );
    $event = \bookverification_verifier\event\chapter_verified::create($params);
    $event->add_record_snapshot('book_chapters', $chapter);
    $event->trigger();

    // page header
    ?>
    <!DOCTYPE HTML>
    <html>
    <head>
      <title><?php echo format_string($book->name, true, array('context'=>$context)) ?></title>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name="description" content="<?php echo s(format_string($book->name, true, array('context'=>$context))) ?>" />
      <link rel="stylesheet" type="text/css" href="print.css" />
    </head>
    <body>
    <?php
    // Print dialog link.
    $printtext = get_string('printchapter', 'booktool_print');
    $printicon = $OUTPUT->pix_icon('chapter', $printtext, 'booktool_print', array('class' => 'book_print_icon'));
    $printlinkatt = array('onclick' => 'window.print();return false;', 'class' => 'book_no_print');
    echo html_writer::link('#', $printicon.$printtext, $printlinkatt);
    ?>
    <a name="top"></a>
    <?php
    echo $OUTPUT->heading(format_string($book->name, true, array('context'=>$context)), 1);
    ?>
    <div class="chapter">
    <?php


    if (!$book->customtitles) {
        if (!$chapter->subchapter) {
            $currtitle = book_get_chapter_title($chapter->id, $chapters, $book, $context);
            echo $OUTPUT->heading($currtitle);
        } else {
            $currtitle = book_get_chapter_title($chapters[$chapter->id]->parent, $chapters, $book, $context);
            $currsubtitle = book_get_chapter_title($chapter->id, $chapters, $book, $context);
            echo $OUTPUT->heading($currtitle);
            echo $OUTPUT->heading($currsubtitle, 3);
        }
    }

    $chaptertext = file_rewrite_pluginfile_urls($chapter->content, 'pluginfile.php', $context->id, 'mod_book', 'chapter', $chapter->id);
    echo format_text($chaptertext, $chapter->contentformat, array('noclean'=>true, 'context'=>$context));
    echo '</div>';
    echo '</body> </html>';

}*/