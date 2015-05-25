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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * The main throwquestions configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package mod_throwquestions
 * @copyright 2015 Xiu-Fong Lin <xlin@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *         
 */
require_once ('../../config.php');
require_once ($CFG->dirroot . "/mod/throwquestions/locallib.php");
require_once ($CFG->dirroot . "/lib/questionlib.php");

global $PAGE, $CFG, $OUTPUT, $DB;

// required parameters in that where passed via url
$cmid = required_param ( 'id', PARAM_INT );
$oponent = required_param ( 'oponentid', PARAM_INT );
$sender = required_param ( 'sender', PARAM_INT );

// Corroborates Course Module
if (! $cm = get_coursemodule_from_id ( 'throwquestions', $cmid )) {
	print_error ( get_string ( 'invalidcoursemodule', 'mod_throwquestions' ) );
}
if (! $throwquestion = $DB->get_record ( 'throwquestions', array (
		'id' => $cm->instance 
) )) {
	print_error ( get_string ( 'error', 'mod_throwquestions' ) );
}

// Corroborates the course
if (! $course = $DB->get_record ( 'course', array (
		'id' => $throwquestion->course 
) )) {
	print_error ( get_string ( 'error', 'mod_throwquestions' ) );
}
// context module
$context = context_module::instance ( $cm->id );

// course id
$id = $course->id;

// require login
require_login ( $id );

// context of the course
$contextcourse = context_course::instance ( $id );

// URL
$url = new moodle_url ( '/mod/throwquestions/questions.php', array (
		'id' => $cmid,
		'sender' => $sender,
		'oponent' => $oponent 
) );
// Page setup and breadcrumbs
$PAGE->set_url ( $url );
$PAGE->set_course ( $course );
$PAGE->set_heading ( $course->fullname );
$PAGE->navbar->add ( get_string ( "throwquestions", 'mod_throwquestions' ) );
$PAGE->set_pagelayout ( 'standard' );
// Variable that conteins the sender and the receiver of the question.
$duelists = array (
		'sender' => $sender,
		'oponent' => $oponent 
);

/* ----------VIEW---------- */
echo $OUTPUT->header ();
echo $OUTPUT->heading ( get_string ( "throwquestions", 'mod_throwquestions' ) );
// print table with all the questions to be selected
echo get_all_the_questions_from_question_bank_table ( $contextcourse->id, $cm->id, $duelists );
echo $OUTPUT->footer ();