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
 * The Learning Session block helper functions.
 *
 * @package   block_learning_session
 * @copyright 2026 onwards Pierre Duverneix - Fondation UNIT (http://unit.eu)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/enrol/locallib.php');
require_once($CFG->dirroot . '/group/lib.php');

function block_learning_session_generate_unique_code($length = 8) {
    global $DB;

    do {
        $code = random_string($length);
    } while ($DB->record_exists('block_learning_session_grouplog', ['code' => $code]));

    return $code;
}

function block_learning_session_generate_password($length = 8) {
    $upperletters = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $lowerletters = 'abcdefghijkmnpqrstuvwxyz';
    $digits = '23456789';
    $specials = '!@#$%';

    if ($length < 7) {
        throw new coding_exception('Password length must be at least 7 characters.');
    }

    $password = [];

    // 4 letters.
    for ($i = 0; $i < 2; $i++) {
        $password[] = $upperletters[random_int(0, strlen($upperletters) - 1)];
    }
    for ($i = 0; $i < 2; $i++) {
        $password[] = $lowerletters[random_int(0, strlen($lowerletters) - 1)];
    }
    // 3 digits.
    for ($i = 0; $i < 3; $i++) {
        $password[] = $digits[random_int(0, strlen($digits) - 1)];
    }
    // 1 special character.
    $password[] = $specials[random_int(0, strlen($specials) - 1)];

    return implode('', $password);
}

function block_learning_session_check_rate_limit() {
    $ip = getremoteaddr();
    $cache = \cache::make('block_learning_session', 'ratelimit');
    $key = 'create_' . md5($ip);
    $count = $cache->get($key) ?: 0;

    if ($count >= 5) {
        http_response_code(429);
        die(get_string('ratelimited', 'block_learning_session'));
    }

    $cache->set($key, $count + 1);
}

function block_learning_session_enrol_user($userid, $courseid, $roleid = null) {
    global $DB;

    if ($roleid === null) {
        $studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
        $roleid = $studentrole->id;
    }

    $instance = $DB->get_record('enrol', [
        'courseid' => $courseid,
        'enrol'    => 'manual',
    ], '*', MUST_EXIST);

    $enrolplugin = enrol_get_plugin('manual');
    $enrolplugin->enrol_user($instance, $userid, $roleid);
}

function block_learning_session_get_existing_userlog($code, $firstname, $lastname) {
    global $DB;

    $sql = "SELECT sul.*
            FROM {block_learning_session_userlog} sul
            JOIN {user} u ON u.username = sul.username
            WHERE u.firstname = :firstname
            AND u.lastname = :lastname
            AND sul.sessioncode = :sessioncode";

    $userlog = $DB->get_record_sql($sql, [
        'firstname' => $firstname,
        'lastname' => $lastname,
        'sessioncode' => $code,
    ]);

    return $userlog;
}

function block_learning_session_get_group_users($courseid, $code) {
    global $DB;

    $session = $DB->get_record(
        'block_learning_session_grouplog',
        ['courseid' => $courseid, 'code' => $code],
        '*',
        MUST_EXIST,
    );

    print_r($session);

    return groups_get_members($session->groupid);
}
