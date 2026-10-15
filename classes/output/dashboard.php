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
 * Dashboard renderable.
 *
 * @package    block_learning_session
 * @copyright  2026 onwards Pierre Duverneix - Fondation UNIT (http://unit.eu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_learning_session\output;

use renderable;
use renderer_base;
use templatable;

/**
 * Dashboard renderable class.
 *
 * @package    block_learning_session
 * @copyright  2026 onwards Pierre Duverneix - Fondation UNIT (http://unit.eu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard implements renderable, templatable {
    /** @var int courseid. */
    protected $courseid;
    /** @var string Session code. */
    protected $sessioncode;
    /** @var int userid. */
    protected $userid;

    /**
     * Constructor.
     * @param int $courseid The course id.
     * @param string $sessioncode The session code.
     * @param int $userid The user id.
     */
    public function __construct($courseid, $sessioncode, $userid) {
        $this->courseid = (int) $courseid;
        $this->sessioncode = $sessioncode;
        $this->userid = (int) $userid;
    }

    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $PAGE->requires->js_call_amd(
            'block_learning_session/export_data',
            'init',
            [
                'courseid' => $this->courseid,
                'sessioncode' => $this->sessioncode,
                'userid' => $this->userid,
            ]
        );

        $users = block_learning_session_get_group_users(
            $this->courseid,
            $this->sessioncode
        );

        $users = array_values(array_map(function ($user) {
            return [
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'username' => $user->username,
                'password' => $user->password,
            ];
        }, $users));

        return [
            'sessioncode' => $this->sessioncode,
            'users' => $users,
        ];
    }
}
