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
 * Library of functions for the booktool_validator module.
 *
 * @package    booktool_validator
 * @copyright  2014 Ivana Skelic, Hrvoje Golcic 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $node The node to add module settings to
 */
 function booktool_validator_extend_settings_navigation(settings_navigation $settings, navigation_node $node) {
 	global $PAGE, $DB;

 	$params = $PAGE->url->params();
 	if (empty($params['id']) or empty($params['chapterid'])) {
 		return;
 	}

 /*	$query = 'SELECT btv.validated 
 		FROM {booktool_validator} btv
 		JOIN {book_chapters} bc ON btv.chapterid = bc.id
 		JOIN {book} b ON bc.bookid = b.id
 		WHERE b.id = ? AND bc.id';

 	$query_result = $DB->get_record($query, array($params['id'], $params['chapterid']));*/

 	$validated = $DB->get_field('booktool_validator', 'validated', array('bookid'=>$params['id'], 'chapterid'=>$params['chapterid']), IGNORE_MISSING);

	if (has_capability('booktool/validator:validate', $PAGE->cm->context)) {
 		if ($validated == 1) {

 			$node->add(get_string('validatebook', 'booktool_validator'), null, navigation_node::TYPE_SETTING, null, null, 
	 			new pix_icon('validator_gray', '', 'booktool_validator', array('class'=>'icon')));
	 		$node->add(get_string('validatechapter', 'booktool_validator'), null, navigation_node::TYPE_SETTING, null, null, 
	 			new pix_icon('validator_gray', '', 'booktool_validator', array('class'=>'icon')));

 		} else {

 			$url1 = new moodle_url('/mod/book/tool/validator/bindex.php', array('id'=>$params['id']));
	 		$url2 = new moodle_url('/mod/book/tool/validator/bcindex.php', array('id'=>$params['id'], 'chapterid'=>$params['chapterid']));
	 		//$action = new action_link($url1, get_string('verifybook', 'booktool_validator'), new popup_action('click', $url1));
	 		$node->add(get_string('validatebook', 'booktool_validator'), $url1, navigation_node::TYPE_SETTING, null, null, 
	 			new pix_icon('validator', '', 'booktool_validator', array('class'=>'icon')));
	 		//$action = new action_link($url2, get_string('verifychapter', 'booktool_validator'), new popup_action('click', $url2));
	 		$node->add(get_string('validatechapter', 'booktool_validator'), $url2, navigation_node::TYPE_SETTING, null, null, 
	 			new pix_icon('validator', '', 'booktool_validator', array('class'=>'icon')));
 		}
	}
 		
}