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
 * Learning Session block action controller for creating a new user.
 *
 * @package   block_learning_session
 * @copyright 2026 onwards Pierre Duverneix - Fondation UNIT (http://unit.eu)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$sessioncode = required_param(parname: 'sessioncode', type: PARAM_TEXT);

require_login($courseid);
$context = \context_course::instance($courseid);
require_capability('block/learning_session:create_session', $context);

$task = new \block_learning_session\task\export_data();
$task->set_custom_data((object)[
    'courseid' => $courseid,
    'sessioncode' => $sessioncode,
    'userid' => $USER->id,
]);

// \core\task\manager::queue_adhoc_task($task, true);

$users = block_learning_session_get_group_users($courseid, $sessioncode);

$file = block_learning_session_create_pdf($users, $USER->id, $courseid);

if ($file) {
    $filename = $file->get_filename();
    $filepath = $file->get_filepath();

    $fileurl = moodle_url::make_pluginfile_url(
        $file->get_contextid(),
        $file->get_component(),
        $file->get_filearea(),
        $file->get_itemid(),
        $file->get_filepath(),
        $file->get_filename(),
        false
    );

    echo '<a href="' . $fileurl->out(false) . '" target="_blank">' . $fileurl->out(false) . '</a>';
}
