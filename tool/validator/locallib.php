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
 * Returns integer value 1 (true) if given chapter of book has
 * no validation faults or 0 (false) if there are validations faults
 * 
 * @param  	stdClass $book->id
 * @param 	$chapterid
 * @return 	int
 */
function chapter_checkvalidation($bookid, $chapterid) {

 	global $DB;

 	$query = $DB->get_field('book_chapters', 'content', array('id'=>$chapterid, 'bookid'=>$bookid));

 	$content = serialize($query);
	
	//setp alt regular expression and run

	$alt_pat = '/<img(\s*(?!alt)([\w\-])+=([\"])[^\"]+\3)*\s*\/?>/i'; //counts ones that don't have alt tag

	preg_match_all($alt_pat, $content, $img_pregmatch);

	$image_pregmatch_cnt = count($img_pregmatch[0]);

	//set regular expressions for table and run

	$summ_pat = '/<table(\s*(?!summary)([\w\-])+=([\"])[^\"]+\3)*\s*\/?>/i'; //counts ones that don't have summary tag

	preg_match_all($summ_pat, $content, $table_pregmatch);

	$table_pregmatch_cnt = count($table_pregmatch[0]);

	if ($image_pregmatch_cnt == 0 && $table_pregmatch_cnt == 0) {
		return 1; //true, there are no validation faults
	} else {
		return 0; //false, there are validation faults
	}

}
/**
 * Get chapter name.
 *
 * Finds and returns chapter name for given parameters.
 *
 * @param  	$book->id
 * @param 	$chapterid
 * @return 	array
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

function number_od_faults($bookid, $chapterid) {

	global $DB;

	$query = 'SELECT faults FROM {booktoool_validator} WHERE chapterid = ? AND bookid = ?';
 	$query_result = $DB->get_records($query, $chapterid, $book->id);

 	if (!$query_result) {
 		return;
 	}

 	return $query_result;
}

/**
 * Counts number of images that lack alt attribute and number of tables that lack
 * summarry attribute. Returns number of faults.
 *
 * @param  	stdClass $book->id
 * @param 	$chapterid
 * @return 	int
 */
function cnt_faults($bookid, $chapterid) {

	global $DB;

 	$query = $DB->get_field('book_chapters', 'content', array('id'=>$chapterid, 'bookid'=>$bookid));

 	$content = serialize($query);

	//set regular expressions for image and run

	$alt_pat = '/<img(\s*(?!alt)([\w\-])+=([\"])[^\"]+\3)*\s*\/?>/i';;

	preg_match_all($alt_pat, $content, $img_alt_pregmatch);

	//set regular expressions for table and run

	$summ_pat = '/<table(\s*(?!summary)([\w\-])+=([\"])[^\"]+\3)*\s*\/?>/i';

	preg_match_all($summ_pat, $content, $table_summ_pregmatch);

	$nof = (count($img_alt_pregmatch[0]) + count($table_summ_pregmatch[0]));

	return $nof;
}

/**
 * Finds and prints images that lack alt attribute for given arguments
 *
 * @param  	stdClass $book->id
 * @param 	$chapterid
 * @return 	
 */
function print_images($bookid, $chapterid) {

	global $DB;

	$query = $DB->get_field('book_chapters', 'content', array('id'=>$chapterid, 'bookid'=>$bookid));
	$content = serialize($query);

	$alt_pat = '/<img(\s*(?!alt)([\w\-])+=([\"])[^\"]+\3)*\s*\/?>/i';;
	preg_match_all($alt_pat, $content, $img_alt_pregmatch);

	if (count($img_alt_pregmatch[0]) != 0) {
		
		echo get_string('image','booktool_validator');
		echo "<br> <br>";

		foreach($img_alt_pregmatch[0] as $print) {
   		echo $print . "<br>";
		}
	}		
}

/**
 * Echo tables that lack summary attribute
 *
 * Finds and prints tables that lack summary attribute for given arguments
 *
 * @param  	stdClass $book->id
 * @param 	$chapterid
 * @return 	
 */
function print_tables($bookid, $chapterid) {

	global $DB;

	$query = $DB->get_field('book_chapters', 'content', array('id'=>$chapterid, 'bookid'=>$bookid));
	//$content = serialize($query);

	$table_pattern = '/<table(.*?)>.*?<\/table>/s'; //regular expression for table tag search

	preg_match_all($table_pattern, $query, $table_pregmatch);

	foreach($table_pregmatch[0] as $child) {

   		if(strpos($child, "<table ") !== FALSE && strpos($child, "summary=") == FALSE) {
   			echo get_string('table', 'booktool_validator');
   			echo "<br> <br>";
   			echo $child . "<br>";
   		}
	}
}

/**
 * Finds and prints all images and their alt attribute
 *
 * @param  	stdClass $book->id
 * @param 	$chapterid
 * @return 	
 */
function find_images($bookid, $chapterid) {
	global $DB;

	$query = $DB->get_field('book_chapters', 'content', array('id'=>$chapterid, 'bookid'=>$bookid));
	$content = serialize($query);

	$img_pat = '/<img([\w\W]+?)>/i'; //regular expression for image tag search
	preg_match_all($img_pat, $query, $img_pregmatch);

	if (!empty($img_pregmatch[0])) {

		foreach($img_pregmatch[0] as $image) {

			echo $image . "<br>"; //echoes image

			$alt_pat = '/(alt\=\"([a-zA-Z0-9\d\D ]*)\")/';
			preg_match_all($alt_pat, $image, $alt_pregmatch);

			if (empty($alt_pregmatch[0])) {
				echo '<b>' . get_string('image', 'booktool_validator') . '</b>';
			}

			foreach ($alt_pregmatch[2] as $alt) {

				echo $alt; //echoes alt
				echo '<br>' . '<b>' . get_string('words','booktool_validator') . ': </b>' . str_word_count($alt) .'<br>';
		
			}
		}		
	} else {
		echo get_string('no_images','booktool_validator');
	}
}

/**
 * Finds and prints all tables and their summary attribute
 *
 * @param  	stdClass $book->id
 * @param 	$chapterid
 * @return 	
 */
function find_tables($bookid, $chapterid) {
	global $DB;

	$query = $DB->get_field('book_chapters', 'content', array('id'=>$chapterid, 'bookid'=>$bookid));
	//$content = serialize($query);

	$table_pat = '/<table(.*?)>.*?<\/table>/s'; //regular expression for table tag search
	preg_match_all($table_pat, $query, $table_pregmatch);

	if (!empty($table_pregmatch[0])) {

		foreach($table_pregmatch[0] as $table) {

			echo $table . "<br>"; //echoes table

			$summ_pat = '/(summary\=\"([a-zA-Z0-9\d\D ]*)\") /i';
			preg_match_all($summ_pat, $table, $summ_pregmatch);

			if (empty($summ_pregmatch[0])) {
				echo '<b>' . get_string('table', 'booktool_validator') . '</b>';
			}

			foreach ($summ_pregmatch[2] as $summary) {
				echo $summary; //echoes summary
				echo '<br>' . '<b>' . get_string('words','booktool_validator') . ': </b>' . str_word_count($summary) .'<br>'; 
			}
		}		
	} else {
		echo get_string('no_tables','booktool_validator');
	}
}
