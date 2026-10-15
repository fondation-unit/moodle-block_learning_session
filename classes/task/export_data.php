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
 * Export data task.
 *
 * @package    block_learning_session
 * @copyright  2026 onwards Fondation UNIT (http://unit.eu)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_learning_session\task;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '../../../locallib.php');

class export_data extends \core\task\adhoc_task {
    public function get_name(): string {
        return get_string('task:request_data', 'learning_session');
    }

    public function execute(): void {
        mtrace("Request Data Task");

        $data = $this->get_custom_data();

        $users = block_learning_session_get_group_users($data->courseid, $data->sessioncode);

        foreach ($users as $user) {
            mtrace("{$user->username} {$user->password}");
        }

        $file = block_learning_session_create_pdf($users, $data->userid, $data->courseid);

        if ($file) {
            $filename = $file->get_filename();
            $filepath = $file->get_filepath();

            mtrace($filename);
            mtrace($filepath);
        }
    }
}
