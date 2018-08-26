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
 * Question type class for the keystrokerecorder question type.
 *
 * @package    qtype
 * @subpackage keystrokerecorder
 * @copyright  2018 Jaka Stavanja (stavanja.xyz)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/keystrokerecorder/question.php');


/**
 * The keystrokerecorder question type.
 *
 * @copyright  2018 Jaka Stavanja (stavanja.xyz)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_keystrokerecorder extends question_type {

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    public function save_question_options($question) {
        global $DB;
        $this->save_hints($question);
        $result = new stdClass();
        $options = (object)array(
            'question_id' => $question->id,
            'quiz_id' => $question->quiz_id
        );
        if ($options->id = $DB->get_field('qtype_keystrokerecorder', 'id', array('question_id' => $question->id))) {
            if (! $DB->update_record('qtype_keystrokerecorder', $options)) {
                $result->error = 'Could not update quiz options! (id='.$options->id.')';
                return $result;
            }
        } else {
            if (! $options->id = $DB->insert_record('qtype_keystrokerecorder', $options)) {
                $result->error = 'Could not insert question options!';
                return $result;
            }
        }
        parent::save_question_options($question);
        return true;
    }

    public function get_question_options($question) {
        global $DB, $OUTPUT;
        // Load the options.
        if (!$question->options = $DB->get_record('qtype_keystrokerecorder', array('question_id' => $question->id))) {
            echo $OUTPUT->notification('Error: Missing question options!');
            return false;
        }

        $question->quiz_id = $question->options->quiz_id;
        parent::get_question_options($question);
        return true;
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        // TODO.
        parent::initialise_question_instance($question, $questiondata);
    }

    public function get_random_guess_score($questiondata) {
        // TODO.
        return 0;
    }

    public function get_possible_responses($questiondata) {
        // TODO.
        return array();
    }
}
