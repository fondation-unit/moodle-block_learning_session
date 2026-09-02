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
 * Form for editing Learning Session block instances.
 *
 * @package   block_learning_session
 * @copyright 2026 onwards Pierre Duverneix - Fondation UNIT (http://unit.eu)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_learning_session extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_learning_session');
    }

    /**
     * The block has a global configuration settings form.
     *
     * @return bool 
     */
    function has_config() {
        return true;
    }

    /**
     * Defines on which page types the blocks may be displayed.
     *
     * @return array 
     */
    function applicable_formats() {
        return [
            'all' => false,
            'course' => true,
            'course-view' => true,
            'my' => false,
            'site' => false,
            'site-index' => false,
            'tag' => false,
        ];
    }

    /**
     * Customize the title and other block attributes.
     *
     * @return void 
     */
    function specialization() {
        if (isset($this->config->title)) {
            $this->title = $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        } else {
            $this->title = get_string('newlearningsessionblock', 'block_learning_session');
        }
    }

    /**
     * The block should not have multiple instances within a course.
     *
     * @return bool
     */
    function instance_allow_multiple() {
        return false;
    }

    /**
     * The block should not be dockable.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return false;
    }

    function get_content() {
        global $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();

        $url = new moodle_url($PAGE->url, [
            'blockaction' => 'myaction',
            'sesskey' => sesskey(),
        ]);

        $button = html_writer::link(
            $url,
            get_string('myaction', 'block_myblock'),
            [
                'class' => 'btn btn-primary',
            ]
        );

        $this->content->text = $button;

        return $this->content;
    }

    function instance_delete() {
        global $DB;

        return true;
    }
}
