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

defined('MOODLE_INTERNAL') || die();

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
    /** @var string Code. */
    protected $code;
    /** @var int courseid. */
    protected $courseid;

    /**
     * Constructor.
     * @param string $code The code.
     * @param int $courseid The course id.
     */
    public function __construct($code, $courseid) {
        $this->code = $code;
        $this->courseid = $courseid;
    }

    public function export_for_template(renderer_base $output) {
        $users = block_learning_session_get_group_users(
            $this->courseid,
            $this->code
        );

        return [
            'code' => $this->code,
            'users' => $users,
        ];
    }
}
