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
 * Block learning session plugin's external functions file.
 *
 * @package   block_learning_session
 * @copyright 2026 onwards Pierre Duverneix - Fondation UNIT (http://unit.eu)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_learning_session\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");

use external_api;
use external_function_parameters;
use external_value;

/**
 * External web service for requesting report file.
 */
class export_data extends external_api {
    /**
     * Describes the parameters for submit_create_group_form webservice.
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'The course ID'),
            'sessioncode' => new external_value(PARAM_TEXT, 'The session code'),
            'userid' => new external_value(PARAM_INT, 'The user ID'),
        ]);
    }

    /**
     * Submit the data request to the adhoc task.
     *
     * @param int $courseid
     * @param string $sessioncode
     * @param int $userid
     * @return void.
     */
    public static function execute($courseid, $sessioncode, $userid) {
        require_once(dirname(__FILE__) . '/../../locallib.php');

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'sessioncode' => $sessioncode,
            'userid' => $userid,
        ]);

        $task = new \block_learning_session\task\export_data();
        $task->set_custom_data((object)[
            'courseid' => $params['courseid'],
            'sessioncode' => $params['sessioncode'],
            'userid' => $userid,
        ]);

        // Queue the task and check for existing (bool).
        \core\task\manager::queue_adhoc_task($task, true);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function execute_returns() {
        return new external_value(PARAM_BOOL, 'Success');
    }
}
