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
 * keystrokerecorder question renderer class.
 *
 * @package    qtype
 * @subpackage keystrokerecorder
 * @copyright  2018 Jaka Stavanja (stavanja.xyz)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/pagelib.php');
/**
 * Generates the output for keystrokerecorder questions.
 *
 * @copyright  2018 Jaka Stavanja (stavanja.xyz)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_keystrokerecorder_renderer extends qtype_renderer {
    
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        global $USER, $PAGE;

        // inject iframe with the plugin form TODO: hash user id somehow
        $keystroke_form_html .= '<iframe src="http://localhost:1337/?user=' . strval($USER->id) . '" style="width: 100%; height: 500px;">';

        $question = $qa->get_question();

        $questiontext = $question->format_questiontext($qa);
        $placeholder = false;
        
        if (preg_match('/_____+/', $questiontext, $matches)) {
            $placeholder = $matches[0];
        }
        $input = '**subq controls go in here**';

        if ($placeholder) {
            $questiontext = substr_replace($questiontext, $input,
                    strpos($questiontext, $placeholder), strlen($placeholder));
        }

        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));
        $result .= html_writer::tag('div', $keystroke_form_html, array('class' => 'qform'));

        return $result;
    }

    public function specific_feedback(question_attempt $qa) {
        // TODO.
        return '';
    }

    public function correct_response(question_attempt $qa) {
        // TODO.
        return '';
    }
}
