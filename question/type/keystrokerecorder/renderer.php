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

    public function __construct() {

    }

    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {
        global $DB, $PAGE;
        $question = $qa->get_question();
        $is_test = $question->name == 'test';

        if ($is_test) {
            $PAGE->requires->js('/question/type/keystrokerecorder/test.js');
        } else {
            $PAGE->requires->js('/question/type/keystrokerecorder/registration.js');
        }
        $PAGE->requires->js('/question/type/keystrokerecorder/camera.js');

        if ($quiz_id = $DB->get_field('qtype_keystrokerecorder', 'quiz_id', array('question_id' => $question->id))) {
            return $is_test ? $this->getTestHTML($qa, $options, $quiz_id) : $this->getRegistrationHTML($qa, $options, $quiz_id);
        } else {
            $result = new stdClass();
            $result->error = 'Could not update quiz options! (id='.$options->id.')';
            return $result;
        }
    }

    public function specific_feedback(question_attempt $qa) {
        // TODO.
        return '';
    }

    public function correct_response(question_attempt $qa) {
        // TODO.
        return '';
    }

    protected function getTestHTML($qa, $options, $quiz_id)
    {
        global $USER, $PAGE;

        $question = $qa->get_question();

        // inject iframe with the plugin form TODO: hash user id somehow
        // $keystroke_form_html .= '<iframe src="http://localhost:1337/?user=' . strval($USER->id) . '&quizId=' . $quiz_id . '" style="width: 100%; height: 500px;">';
        $keystroke_form_html = '
        <link rel="stylesheet" href="http://localhost:1337/index.css">
        <link rel="stylesheet" href="http://localhost:1337/camera.css">
        <script
          src="https://code.jquery.com/jquery-3.1.1.min.js"
          integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
          crossorigin="anonymous">
        </script>
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.js"></script>
        <script src="http://localhost:1337/keycode_map.js"></script>
        <script src="http://localhost:1337/utils.js"></script>
        <script src="http://localhost:1337/camera.js"></script>
        <div id="insert_content_here" data-user="' . strval($USER->id) . '" data-quiz-id="' . $quiz_id . '"
        <!-- Page Contents -->
        <div class="pusher">
          <div class="ui inverted vertical masthead center aligned segment">
            <div class="ui text container password_display">
              <h3>Password:</h3>
              <div class="ui huge primary button" id="wordDisplay">q-vDf}784L</i></div>
            </div>
      
            <div class="ui huge secondary button progress_password_text" ><span id="remainingRepetitions">loading ...</span> more tries needed</i></div>
      
            <div id="wordInputWrapper" class="ui icon input loading">
              <input type="text" id="wordInput" placeholder="Enter word here ...">
              <i class="check icon"></i>
            </div>
      
            <div class="ui huge secondary button" id="keystroke_finish_text" style="display:none">
                Password test finished  <i class="check circle icon"></i>
            </div>
      
            <div id="face_finish_text" style="display:none">
                <div class="ui huge secondary button">
                    Face test finished  <i class="check circle icon"></i>
                </div>
                <h2 class="ui text container">Good luck!</h2>
            </div>
      
      
            <div id="image-upload-inputs" style="display:none">
              <h3>Take a picture of your face:</h3>
              <div class="camera-functionality">
      
                  <video id="camera-stream"></video>
                  <img id="snap" class="dummy">
      
                  <div class="controls">
                    <a href="#" id="delete-photo" title="Delete Photo" class="disabled"><i class="material-icons">delete</i></a>
                    <a href="#" id="take-photo" title="Take Photo"><i class="material-icons">camera_alt</i></a>
                    <a href="#" id="send-photo-compare" download="selfie.png" title="Save Photo" class="disabled">SEND</a>
                  </div>
      
                  <canvas></canvas>
      
                </div>
            </div>
      
            <div id="image-upload-loader" style="display:none">
              Uploading and comparing your image ...
              <div class="ui active inline loader"></div>
            </div>
          </div>
        </div>
      
      
        <!-- modals -->
        <div class="ui basic modal restart">
          <div class="ui icon header">
            <i class="ban icon"></i>
            Incorrect letter pressed.
          </div>
          <div class="content">
            <p>Restarting ...</p>
          </div>
        </div>
      
        <div class="ui basic modal speed-restart">
          <div class="ui icon header">
            <i class="ban icon"></i>
            You were typing too fast.
          </div>
          <div class="content">
            <p>Restarting ...</p>
          </div>
        </div>
      
        <div class="ui basic modal success">
          <div class="ui icon header">
            <i class="checkmark icon"></i>
            Try successful. Type the word again.
          </div>
          <div class="content">
            <p>Restarting ...</p>
          </div>
        </div>';


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

    protected function getRegistrationHTML($qa, $options, $quiz_id)
    {
        global $USER, $PAGE;

        $question = $qa->get_question();

        // inject iframe with the plugin form TODO: hash user id somehow
        // $keystroke_form_html .= '<iframe src="http://localhost:1337/registration.html?user=' . strval($USER->id) . '&quizId=' . $quiz_id . '" style="width: 100%; height: 500px;">';

        $keystroke_form_html = '
        <link rel="stylesheet" href="http://localhost:1337/index.css">
        <link rel="stylesheet" href="http://localhost:1337/camera.css">
        <script
          src="https://code.jquery.com/jquery-3.1.1.min.js"
          integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
          crossorigin="anonymous">
        </script>
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.js"></script>
        <script src="http://localhost:1337/keycode_map.js"></script>
        <script src="http://localhost:1337/utils.js"></script>
        <script src="http://localhost:1337/camera.js"></script>
        <div id="insert_content_here" data-user="' . strval($USER->id) . '" data-quiz-id="' . $quiz_id . '"></div>
        <div class="pusher">
        <div class="ui inverted vertical masthead center aligned segment">
          <div class="ui text container password_display">
            <h3>Password:</h3>
            <div class="ui huge primary button" id="wordDisplay">q-vDf}784L</i></div>
          </div>
    
          <div class="ui huge secondary button progress_password_text" ><span id="remainingRepetitions">loading ...</span> more tries needed</i></div>
    
          <div id="wordInputWrapper" class="ui icon input loading">
            <input type="text" id="wordInput" placeholder="Enter word here ...">
            <i class="check icon"></i>
          </div>
    
          <div class="ui huge secondary button" id="keystroke_finish_text" style="display:none">
              Password test finished  <i class="check circle icon"></i>
          </div>
    
          <div id="face_finish_text" style="display:none">
              <div class="ui huge secondary button">
                  Face test finished  <i class="check circle icon"></i>
              </div>
              <h2 class="ui text container">Good luck!</h2>
          </div>
    
          <div id="has_record_text" style="display:none">
            <div class="ui huge secondary button">
                You have already registered for this quiz <i class="check circle icon"></i>
            </div>
            <h2 class="ui text container">Good luck with the tests :)</h2>
          </div>
    
          <div id="image-upload-inputs" style="display:none">
            <h3>Take a picture of your face:</h3>
            <div class="camera-functionality">
    
                <video id="camera-stream"></video>
                <img id="snap" class="dummy">
    
                <div class="controls">
                  <a href="#" id="delete-photo" title="Delete Photo" class="disabled"><i class="material-icons">delete</i></a>
                  <a href="#" id="take-photo" title="Take Photo"><i class="material-icons">camera_alt</i></a>
                  <a href="#" id="send-photo" download="selfie.png" title="Save Photo" class="disabled">SEND</a>
                </div>
    
                <canvas></canvas>
    
              </div>
          </div>
    
          <div id="image-upload-loader" style="display:none">
            Uploading and comparing your image ...
            <div class="ui active inline loader"></div>
          </div>
        </div>
      </div>
    
    
      <!-- modals -->
      <div class="ui basic modal restart">
        <div class="ui icon header">
          <i class="ban icon"></i>
          Incorrect letter pressed.
        </div>
        <div class="content">
          <p>Restarting ...</p>
        </div>
      </div>
    
      <div class="ui basic modal speed-restart">
        <div class="ui icon header">
          <i class="ban icon"></i>
          You were typing too fast.
        </div>
        <div class="content">
          <p>Restarting ...</p>
        </div>
      </div>
    
      <div class="ui basic modal success">
        <div class="ui icon header">
          <i class="checkmark icon"></i>
          Try successful. Type the word again.
        </div>
        <div class="content">
          <p>Restarting ...</p>
        </div>
      </div>';

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
}
