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
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

global $DB, $OUTPUT, $PAGE, $USER;

$context = context_system::instance();

$PAGE->set_url('/blocks/learning_session/create_user.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('createuser', 'block_learning_session'));

$returnurl = new moodle_url('/admin/user.php');

$mform = new \block_learning_session\form\create_user_form();

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    // Generate a unique username.
    $token = \core\uuid::generate();
    $localpart = 'user_' . substr(str_replace('-', '', $token), 0, 10);

    $newuser = new stdClass();
    $newuser->firstname   = $data->firstname;
    $newuser->lastname    = $data->lastname;
    $newuser->username    = strtolower($localpart);
    $newuser->email       = $localpart . '@example.com';
    $newuser->auth        = 'manual';
    $newuser->confirmed   = 1;
    $newuser->mnethostid  = $CFG->mnet_localhost_id;
    $newuser->lang        = current_language();

    $newuser->password = block_learning_session_generate_password();

    $transaction = $DB->start_delegated_transaction();

    try {
        $newuserid = user_create_user($newuser, true, true); // updatepassword=true, triggerevent=true.

        $record = new stdClass();
        $record->userid = $newuserid;
        $record->createdby = $USER->id;
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

    redirect($returnurl);
} else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
