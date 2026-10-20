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

use core\message\message;
use moodle_url;

class export_data extends \core\task\adhoc_task {
    public function get_name(): string {
        return get_string('task:export_data', 'block_learning_session');
    }

    public function execute(): void {
        mtrace("Request Data Task");

        $data = $this->get_custom_data();

        $users = block_learning_session_get_group_users($data->courseid, $data->sessioncode);
        $file = block_learning_session_create_pdf($users, $data->userid, $data->courseid);

        if ($file) {
            self::generate_message($data->userid, $data->courseid, $data->sessioncode, $file);
        }
    }

    private static function generate_message(int $userid, int $courseid, string $sessioncode, \stored_file $file) {
        $contexturl = new moodle_url('/blocks/learning_session/view.php', [
            'courseid' => $courseid,
            'sessioncode' => $sessioncode,
        ]);

        $fullmessage = get_string('messageprovider:export_data_fullmessage', 'block_learning_session');
        $smallmessage = get_string('messageprovider:export_data_smallmessage', 'block_learning_session');

        $fileurl = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename(),
            false
        );

        $fullmessage .= '<p><a href="' . $fileurl->out(false) . '" target="_blank">' . $fileurl->out(false) . '</a></p>';

        $message = new message();
        $message->component         = 'block_learning_session';
        $message->name              = 'export_data';
        $message->userfrom          = \core_user::get_noreply_user();
        $message->userto            = $userid;
        $message->subject           = get_string('messageprovider:export_data', 'block_learning_session');
        $message->fullmessageformat = FORMAT_HTML;
        $message->fullmessage       = html_to_text($fullmessage);
        $message->fullmessagehtml   = $fullmessage;
        $message->smallmessage      = $smallmessage;
        $message->notification      = 1;
        $message->contexturl        = $contexturl;
        $message->contexturlname    = get_string('task:export_data', 'block_learning_session');

        if ($file) {
            $message->attachment = $file;
            $message->attachname = $file->get_filename();
        }

        message_send($message);
    }
}
