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
 * Learning Session block action controller for creating a new learning session.
 *
 * @package   block_learning_session
 * @copyright 2026 onwards Pierre Duverneix - Fondation UNIT (http://unit.eu)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

require_once($CFG->dirroot . '/group/lib.php');

$courseid = required_param('courseid', PARAM_INT);

// Context checking.
require_login($courseid);
$context = \context_course::instance($courseid);
require_capability('block/learning_session:create_session', $context);

$PAGE->set_url('/blocks/learning_session/create_session.php', ['courseid' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('createsession', 'block_learning_session'));

$returnurl = new \moodle_url('/course/view.php', ['id' => $courseid]);

$mform = new \block_learning_session\form\create_session_form(null, ['courseid' => $courseid]);

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    // Form validated and sesskey already checked by moodleform.
    $transaction = $DB->start_delegated_transaction();
    $code = block_learning_session_generate_unique_code();

    $groupdata = new \stdClass();
    $groupdata->courseid = $data->courseid;
    $groupdata->name = $code;
    $groupid = groups_create_group($groupdata);
    // Add the creator to the group members.
    groups_add_member($groupid, $USER);

    $record = new \stdClass();
    $record->code = $code;
    $record->userid = $USER->id;
    $record->sessionenddate = $data->sessionenddate;
    $record->timecreated = time();
    $DB->insert_record('block_learning_session_grouplog', $record);

    $transaction->allow_commit();

    redirect($returnurl, get_string('sessioncreated', 'block_learning_session'), null, \core\output\notification::NOTIFY_SUCCESS);
} else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
