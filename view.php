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
$context = context_course::instance($courseid);
require_capability('block/learning_session:create_session', $context);

$PAGE->set_url('/blocks/learning_session/view.php', ['courseid' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('learningsession', 'block_learning_session'));

echo $OUTPUT->header();

echo $OUTPUT->footer();
