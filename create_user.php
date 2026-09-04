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
require_once(__DIR__ . '/locallib.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

$code = required_param('code', PARAM_TEXT);
$courseid = required_param('courseid', PARAM_INT);

$context = \context_system::instance();

$PAGE->set_url('/blocks/learning_session/create_user.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('createuser', 'block_learning_session'));

$returnurl = new \moodle_url('/course/view.php', ['id' => $courseid]);

$mform = new \block_learning_session\form\create_user_form();
$mform->set_data(['code' => $code, 'courseid' => $courseid]); // Pre-fill hidden fields.

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    // Generate a unique username.
    $token = \core\uuid::generate();
    $pw = block_learning_session_generate_password();
    $localpart = strtolower($data->firstname[0] . $data->lastname[0])
        . '_' . substr(str_replace('-', '', $token), 0, 8);

    $newuser = new \stdClass();
    $newuser->firstname = $data->firstname;
    $newuser->lastname = $data->lastname;
    $newuser->username = strtolower($localpart);
    $newuser->email = $localpart . '@example.com';
    $newuser->auth = 'manual';
    $newuser->confirmed = 1;
    $newuser->mnethostid = $CFG->mnet_localhost_id;
    $newuser->lang = current_language();
    // Controlled password generation.
    $newuser->password = $pw;

    $transaction = $DB->start_delegated_transaction();

    try {
        $newuserid = user_create_user($newuser, true, true); // updatepassword=true, triggerevent=true.

        $record = new \stdClass();
        $record->userid = $newuserid;
        $record->username = $newuser->username;
        $record->password = $pw;
        $record->courseid = $courseid;
        $record->sessioncode = $code;
        $record->ip = getremoteaddr();
        $record->timecreated = time();
        $DB->insert_record('block_learning_session_userlog', $record);

        $transaction->allow_commit();

        \core\notification::success(
            get_string('usercreated', 'block_learning_session', $newuser->username)
        );
    } catch (\Exception $e) {
        $transaction->rollback($e);
        \core\notification::error(get_string('errorcreatinguser', 'block_learning_session'));
    }

    // Enrol the user into the course.
    block_learning_session_enrol_user($newuserid, $courseid);
    // Add the user to the group.
    $groupid = groups_get_group_by_name($courseid, $code);
    groups_add_member($groupid, $newuserid);

    redirect($returnurl);
} else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
