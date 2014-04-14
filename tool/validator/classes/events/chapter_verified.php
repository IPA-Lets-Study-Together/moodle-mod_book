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
 * booktool_verifier chapter verified event
 *
 * @package    booktool_verifier
 * @copyright  2014 Ivana Skelic, Hrvoje Golcic 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace booktool_verifier\event;
defined('MOODLE_INTERNAL') || die();

/**
 * booktool_verifier chapter verified event class
 *
 * @package    booktool_verifier
 * @copyright  2014 Ivana Skelic, Hrvoje Golcic 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class chapter_verified extends \core\event\base {
	/**
     * Returns description of what happened.
     *
     * @return string
     */
	public function get_description() {
		return "The user $this->userid has verified the chapter $this->objectid of the book module $this->context->instanceid."
	}

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
    	return array($this->courseid, 'book', 'verify chapter', 'tool/verifier/index.php?id=' . $this->context->instanceid . '&chapterid=' . $this->objectid, $this->objectid, $this->context->instanceid);
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
    	return get_string('event_chapter_verified', 'booktool_verifier');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
    	return new \moodle_url('/mod/book/tool/verifier/index.php', array('id' => $this->context->instanceid));
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
    	$this->data['crud'] = 'r';
    	$this->data['level'] = self::LEVEL_PARTICIPATING;
    	$this->data['objecttable'] = 'book';
    }
    
}