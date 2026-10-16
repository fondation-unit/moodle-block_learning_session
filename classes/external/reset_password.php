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
 * Block learning session plugin's external functions file.
 *
 * @package   block_learning_session
 * @copyright 2026 onwards Pierre Duverneix - Fondation UNIT (http://unit.eu)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_learning_session\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/blocks/learning_session/locallib.php');

use external_api;
use external_function_parameters;
use external_value;

class reset_password extends external_api {

    /**
     * Définit les paramètres de la requête AJAX.
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User ID'),
        ]);
    }

    /**
     * Génère un nouveau mot de passe pour un utilisateur.
     */
    public static function execute($userid) {
        global $DB, $CFG;

        $params = self::validate_parameters(
            self::execute_parameters(),
            ['userid' => $userid]
        );

        // Récupère le compte cible.
        $user = \core_user::get_user($params['userid']);

        // Génère le mot de passe via ta fonction existante.
        $password = block_learning_session_generate_password();

        // Utilise l'API Moodle pour mettre à jour le mot de passe.

        $localuser = $DB->get_record('block_learning_session_userlog', [
            'userid' => $params['userid']
        ]);

        if (!$localuser) {
            throw new \moodle_exception(
                'User record not found in learning session',
                'block_learning_session'
            );
        }

        $localuser->password = $password;

        $DB->update_record('block_learning_session_userlog', $localuser);

        return $password;
    }

    /**
     * Définit le type de retour pour AJAX.
     */
    public static function execute_returns() {
        return new external_value(
            PARAM_TEXT,
            'Generated password'
        );
    }
}
