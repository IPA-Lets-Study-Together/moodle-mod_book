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
 * bookverification_verifier book verified event
 *
 * @package    bookverification_verifier
 * @copyright  2014 Ivana Skelic, Hrvoje Golcic 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace bookverification_verifier\event;
defined('MOODLE_INTERNAL') || die;

/**
 * bookverification_verifier book verified event class
 *
 * @package    bookverification_verifier
 * @copyright  2014 Ivana Skelic, Hrvoje Golcic 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class book_verified extends \core\event\base {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
    	return "The user $this->userid has verified the book $this->objectid.";
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
    	return array($this->courseid, 'book', 'verify book', 'verification/verifier/index.php?id=' . $this->context->instanceid, $this->objectid, $this->context->instanceid);
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
    	return get_string('event_book_verified', 'bookverification_verifier');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
    	return new \moodle_url('mod/book/verification/verifier/index.php', array('id' => $this->context->instanceid));
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
    	$this->data['crud'] = 'r';
    	$this->data['level'] = self::LEVEL_PARTICIPATING;
    	$this->data['objacttable'] = 'book';
    }
    
}