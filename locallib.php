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

function block_learning_session_generate_unique_code($length = 8) {
    global $DB;

    do {
        $code = strtoupper(random_string($length));
    } while ($DB->record_exists('block_learning_session_grouplog', ['code' => $code]));

    return $code;
}

function block_learning_session_generate_password($length = 16) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%';
    $password = '';

    for ($i = 0, $max = strlen($chars) - 1; $i < $length; $i++) {
        $password .= $chars[random_int(0, $max)];
    }

    return $password;
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
