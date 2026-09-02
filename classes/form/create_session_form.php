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
 * Learning Session block form for creating a new learning session.
 *
 * @package   block_learning_session
 * @copyright 2026 onwards Pierre Duverneix - Fondation UNIT (http://unit.eu)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_learning_session\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class create_session_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        $courseid = $this->_customdata['courseid'];

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        // Add a session end date to let the plugin purge data if needed.
        $mform->addElement('date_time_selector', 'sessionenddate', get_string('sessionenddate', 'block_learning_session'));
        $mform->addHelpButton('sessionenddate', 'sessionenddate', 'block_learning_session');
        $mform->addRule('sessionenddate', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(true, get_string('createsession', 'block_learning_session'));
    }


    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['sessionenddate']) && $data['sessionenddate'] <= time()) {
            $errors['sessionenddate'] = get_string('sessionenddatepast', 'block_learning_session');
        }

        return $errors;
    }
}
