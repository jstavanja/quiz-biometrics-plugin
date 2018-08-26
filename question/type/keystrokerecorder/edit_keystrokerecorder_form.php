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
 * Defines the editing form for the keystrokerecorder question type.
 *
 * @package    qtype
 * @subpackage keystrokerecorder
 * @copyright  2018 Jaka Stavanja (stavanja.xyz)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * keystrokerecorder question editing form definition.
 *
 * @copyright  2018 Jaka Stavanja (stavanja.xyz)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_keystrokerecorder_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        $name = 'quiz_id';
        $label = "Quiz ID from the dashboard";
        $mform->addElement('text', $name, $label, array('size' => 3));
        $mform->setType($name, PARAM_INT);
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_hints($question);

        return $question;
    }

    public function qtype() {
        return 'keystrokerecorder';
    }
}
