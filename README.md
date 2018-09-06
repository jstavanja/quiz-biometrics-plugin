# quiz-biometrics-plugin
Moodle plugin for question type, that communicates with the quick-biometrics-api.

First, make sure that you have the backend system installed and running on your machine. The backend is available in a separate git repository (https://github.com/jstavanja/quiz-biometrics-api).
The following instructions can also be found in the backend repository.

## Installation of the moodle plugin
Clone the git repository https://github.com/jstavanja/quiz-biometrics-plugin to your disk and put the files inside the root folder of the Moodle installation and install the Moodle plugin in the administration dashboard as any other question type plugin.

## Instructions for the moodle plugin
First, go inside the api project and go into the folder forms, and serve it with a server on port 1337 (or change both this port and the port in the Moodle plugin as well). I found the easiest way for testing and development to be running this python module:
```bash
python -m SimpleHTTPServer 1337
```
This will provide all the forms and static files for the forms that the students will be using.

To add a registration question to a quiz, make a quiz and add a question with the type keystrokerecorder (has to be renamed in the future to something like biometricsquestion).

DEV PHASE: in the current phase, you can edit the moodle quiz ID you wish to use in the forms parameter *Quiz ID from the dashboard*. Security for random people to not be able to access other people's forms still needs to be implemented.

After you set the correct ID to that variable (you may possibly need to clean the Moodle caches at admin/purgecaches.php) you should be able to create registration and test questions.

When creating a question in a quiz, in the title of the question, put only:
  - "registration" -> if you wish to add a first time registration form into a question (that will add a students unique id from moodle to the backend database and save his real typing patterns and face photo). use this only once.

  - "test" -> if you wish to add a test, which compares the newly sent data with the original data set at the registration. all this data gets sent to the api, gets compared and is stored for the instructor to check.
  
In case your SQL migrations for the plugin do not get executed, run this query on your Moodle database:

```SQL
CREATE TABLE mdl_qtype_keystrokerecorder (
    id BIGINT(10) NOT NULL auto_increment,
    question_id BIGINT(10),
    quiz_id BIGINT(10),
CONSTRAINT  PRIMARY KEY (id)
)
 ENGINE = InnoDB
 DEFAULT COLLATE = utf8mb4_unicode_ci ROW_FORMAT=Compressed
 COMMENT='Default comment for qtype_keystrokerecorder, please edit me’;
```
