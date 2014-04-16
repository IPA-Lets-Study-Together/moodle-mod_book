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
 * Booktool_validator module local lib functions
 *
 * @package    booktool_validator
 * @copyright  2014 Ivana Skelic, Hrvoje Golcic 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot.'/mod/book/edit_form.php');

/**
 * Returns pregmatch_all result for given search criteria
 *
 * @param 
 * @param array $chapters
 * @return 
 */
function get_pregmatch($query_result, $pattern) {

 	$serialize_query_result = serialize($query_result);
 	preg_match_all($pattern, $serialize_query_result, $pregmatch_result);

 	return $pregmatch_result;
}
/**
 * Counts how many indices are in the given array
 * 
 * @param array $pregmatch_result
 * @return int
 */
function count_indices($pregmatch_result) {

	if (empty($pregmatch_result)) {
		return false;
	}

	return count($pregmatch_result);
}
/**
 * Returns array which contains all occurences of given string
 *
 * @param 
 * @param string $search_term
 * @return 
 */
function term_search($pregmatch_result, $search_term) {

	if (empty($pregmatch_result) && empty($search_term)) {
		return false;
	}

	return array_search($search_term, $pregmatch_result);
}
/**
 * Validate entire book.
 *
 * Checks whole book for image and table validation issues. Sets database field if book is validated.
 *
 * @param  stdClass $book
 * @return bool value whether book content is validated or not
 */
function book_checkvalidation($book) {
 	
 	global $DB;

	//connect to database and get all chapters for this book
	$query = 'SELECT bc.content FROM
		{book_chapters} bc 
		JOIN {book} b ON bc.bookid = b.id
		WHERE bc.bookid = ?';
	$query_result = $DB->get_records_sql($query, array($book->id));

	//set regular expressions for search and run the search

	$image_pattern = '/<img(.*)\/>/'; //regular expression for image tag search
	
	$image_pregmatch = get_pregmatch($query_result, $image_pattern);
	$image_pregmatch_cnt = count_indices($image_pregmatch);
	$image_pregmatch_search = term_search($image_pregmatch, 'alt="');
	$cnt_alt = count_indices($image_pregmatch_search);

	
	$table_pattern = '/<table(.*)\>/'; //regular expression for table tag search

	$table_pregmatch = get_pregmatch($query_result, $table_pattern);
	$table_pregmatch_cnt = count_indices($table_pregmatch);
	$table_pregmatch_search = term_search($table_pregmatch, 'summary="');
	$cnt_summary = count_indices($table_pregmatch_search);

	if (($image_pregmatch_cnt > $cnt_alt) && ($table_pregmatch_cnt > $cnt_summary)) {
		return false;
	} elseif (($image_pregmatch_cnt == $cnt_alt) && ($table_pregmatch_cnt == $cnt_summary)) {
		return true;
	}

 }
/**
 * Validate chapter of a book.
 *
 * Checks chapter of a book for image and table validation issues. Sets database field if book is validated.
 *
 * @param  	stdClass $book
 * @param 	$chapterid
 * @return 	bool value whether book content is validated or not
 */
function chapter_checkvalidation($book, $chapterid) {

 	global $DB;

 	$query = 'SELECT bc.content FROM 
 		{book_chapters} bc
 		JOIN {book} b ON bc.bookid = b.id
 		WHERE bc.bookid = ? AND bc.id = ?';
 	$params = array($book->id, $chapterid);
 	$query_result = $DB->get_records_sql($query, $params);

	//set regular expressions for search and run the search

	$image_pattern = '/<img(.*)\/>/'; //regular expression for image tag search
	
	$image_pregmatch = get_pregmatch($query_result, $image_pattern);
	$image_pregmatch_cnt = count_indices($image_pregmatch);
	$image_pregmatch_search = term_search($image_pregmatch, 'alt="');
	$cnt_alt = count_indices($image_pregmatch_search);

	
	$table_pattern = '/<table(.*)\>/'; //regular expression for table tag search

	$table_pregmatch = get_pregmatch($query_result, $table_pattern);
	$table_pregmatch_cnt = count_indices($table_pregmatch);
	$table_pregmatch_search = term_search($table_pregmatch, 'summary="');
	$cnt_summary = count_indices($table_pregmatch_search);

	if (($image_pregmatch_cnt > $cnt_alt) && ($table_pregmatch_cnt > $cnt_summary)) {
		return false;
	} elseif (($image_pregmatch_cnt == $cnt_alt) && ($table_pregmatch_cnt == $cnt_summary)) {
		return true;
	}
}
/**
 * Get chapter name.
 *
 * Finds and returns chapter name for given parameters.
 *
 * @param  	stdClass $book
 * @param 	$chapterid
 * @return 	chapter name value
 */
function chapter_getname($book, $chapterid) {

	global $DB;

	$query = 'SELECT title FROM {book_chapters} WHERE id = ? AND bookid = ?';
	$params = array($chapterid, $book->id);
	$query_result = $DB->get_record_sql($query, $params);
	
	if (!$query_result) {
		return false;
	}
	return $query_result->title;
}

function number_od_faults($book, $chapterid) {

	global $DB;

	$query = 'SELECT faults FROM {booktoool_validator} WHERE chapterid = ? AND bookid = ?';
 	$query_result = $DB->get_records($query, $chapterid, $book->id);

 	if (!$query_result) {
 		return;
 	}

 	return $query_result;
}

function cnt_faults($book, $chapterid) {

	global $DB;

	// $book_id = $book->id;
	// $chapter_id = $chapterid->id;

	$query = 'SELECT bc.content FROM 
 			{book_chapters} bc 
 			JOIN {book} b ON bc.bookid = b.id
 			WHERE b.id = ? AND bc.id = ?';
 	$params = array($book->id, $chapterid->id);
 	$query_result = $DB->get_records_sql($query, $params);


	$image_pattern = '/<img(.*)\/>/'; //regular expression for image tag search
	
	$image_pregmatch = get_pregmatch($query_result, $image_pattern);
	$image_pregmatch_cnt = count_indices($image_pregmatch);
	$image_pregmatch_search = term_search($image_pregmatch, 'alt="');
	$cnt_alt = count_indices($image_pregmatch_search);

	
	$table_pattern = '/<table(.*)\>/'; //regular expression for table tag search

	$table_pregmatch = get_pregmatch($query_result, $table_pattern);
	$table_pregmatch_cnt = count_indices($table_pregmatch);
	$table_pregmatch_search = term_search($table_pregmatch, 'summary="');
	$cnt_summary = count_indices($table_pregmatch_search);


	return ($image_pregmatch_cnt - $cnt_alt) + ( $table_pregmatch_cnt - $cnt_summary );
}