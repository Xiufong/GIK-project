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
 * Internal library of functions for module newmodule
 *
 * All the newmodule specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package mod_throwquestions
 * @copyright 2015 Xiu-Fong Lin <xlin@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

/**
 * This function gets a navigation tab.
 *
 * @param int $cmid        	
 * @param int $courseid        	
 * @param text $sesskey        	
 * @param obj $context        	
 * @return multitype:tabobject
 */
function option_tab($cmid, $courseid, $sesskey, $context) {
	global $PAGE, $CFG, $OUTPUT, $DB;
	
	$tabs = array ();
	// Initialize the object tab.
	$tab = new tabobject ( 'list', $CFG->wwwroot . "/mod/throwquestions/view.php?id={$cmid}", 'Throwquestions' );
	// First sub tree called viewlist.
	$tab->subtree [] = new tabobject ( 'viewlist', $CFG->wwwroot . "/mod/throwquestions/view.php?id={$cmid}", 'List' );
	// Check if has capability to create questions.
	if (has_capability ( 'moodle/question:add', $context )) {
		// Second subtree that will be only show to the users with the capability to add question.
		$tab->subtree [] = new tabobject ( 'create', $CFG->wwwroot . "/question/question.php?category=&courseid={$courseid}&sesskey={$sesskey}&qtype=multichoice&returnurl=%2Fmod%2Fthrowquestions%2Fview.php%3Fid%3D{$cmid}&courseid={$courseid}&category=1", 'Create Question' );
	}
	// Third subtree that will show the pending battles of the user.
	$tab->subtree [] = new tabobject ( 'check', $CFG->wwwroot . "/mod/throwquestions/lobby.php?id={$cmid}", 'Check' );
	// saves all the tabs in a variable.
	$tabs [] = $tab;
	return $tabs;
}
/**
 * Function to get all the students in the course.
 *
 * @param int $courseid        	
 * @return An object with all the students
 */
function throwquestions_get_students($courseid, $user) {
	global $DB;
	// Query to get all the students from the course, except from the current user.
	$query = 'SELECT u.id, u.idnumber, u.firstname as name, u.lastname as last, e.enrol
			FROM {user_enrolments} ue
			JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ?)
			JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
			JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = 5 AND ra.userid = ue.userid)
			JOIN {user} u ON (ue.userid = u.id) and (u.id!=?)
			ORDER BY lastname ASC';
	
	// Takes all the data from the query and saves it in a variable
	$rs = $DB->get_recordset_sql ( $query, array (
			$courseid,
			$user 
	) );
	
	return $rs;
}
/**
 * This funtions is the main menu to select the oponent for the battle.
 *
 * @param obj $users        	
 * @param int $cmid        	
 * @return Table with all the students and a button to challenge.
 */
function get_all_students($users, $cmid, $sender) {
	global $PAGE, $CFG, $OUTPUT, $DB;
	
	$data = '';
	// Run the object $users to get the information to display the table
	foreach ( $users as $user ) {
		$userid = $user->id;
		$url = new moodle_url ( '/mod/throwquestions/questions.php', array (
				'id' => $cmid,
				'oponentid' => $userid,
				'sender' => $sender 
		) );
		// Prepares the data for the table.
		$data [] = array (
				$user->name . ' ' . $user->last,
				$userid,
				$OUTPUT->single_button ( $url, get_string ( 'challenge', 'mod_throwquestions' ) ) 
		);
	}
	// Initialize the object table
	$table = new html_table ();
	// Creates the atributes
	$table->attributes ['style'] = "width: 50%; text-align:center;";
	// Table Headings
	$table->head = array (
			get_string ( 'students', 'mod_throwquestions' ),
			get_string ( 'id', 'mod_throwquestions' ),
			get_string ( 'whodoyouwanttobattle', 'mod_throwquestions' ) 
	);
	// Insert the data in the table
	$table->data = $data;
	// Render the table in a variable.
	$userstable = html_writer::table ( $table );
	return $userstable;
}
/**
 * This function get a table with all the question from que question bank in the course.
 *
 * @param int $context        	
 * @param int $cmid        	
 * @param array $duelists        	
 * @return A table with all the multi choice questions from the question bank
 */
function get_all_the_questions_from_question_bank_table($context, $cmid, $duelists) {
	global $PAGE, $CFG, $OUTPUT, $DB;
	
	// Get all the categories from the course context
	$categories = get_categories_for_contexts ( $context, 'name ASC' );
	
	$data = '';
	
	foreach ( $categories as $category ) {
		// Gets all the questions from the category
		$questions = $DB->get_records ( 'question', array (
				'category' => $category->id,
				'qtype' => 'multichoice' 
		) );
		foreach ( $questions as $question ) {
			// Sets a new url to be run in the single button.
			$url = new moodle_url ( '/mod/throwquestions/insert_battle.php', array (
					'id' => $cmid,
					'qid' => $question->id,
					'sender' => $duelists ['sender'],
					'oponent' => $duelists ['oponent'],
					'status' => 0 
			) );
			// Prepares the data for the table.
			$data [] = array (
					$question->questiontext . ' ' . $question->id,
					$OUTPUT->single_button ( $url, get_string ( 'iwantthisone', 'mod_throwquestions' ) ) 
			);
		}
	}
	// Initialize the object table
	$table = new html_table ();
	// Creates the atributes
	$table->attributes ['style'] = "text-align:center;";
	// Table Headings
	$table->head = array (
			get_string ( 'question', 'mod_throwquestions' ),
			get_string ( 'select', 'mod_throwquestions' ) 
	);
	// Insert the data in the table
	$table->data = $data;
	// Render the table in a variable.
	$questiontable = html_writer::table ( $table );
	
	return $questiontable;
}
/**
 * Function to get the answer menu
 *
 * @param obj $context        	
 * @param int $cmid        	
 * @param int $questionid        	
 * @return table with all the posible answer for the question
 */
function answer_menu($context, $cmid, $questionid, $sender, $receiver, $battleid) {
	global $PAGE, $CFG, $OUTPUT, $DB;
	
	// get all the categories from the course.
	$categories = get_categories_for_contexts ( $context, 'name ASC' );
	
	// initialization for the variable $data that will go inside the table.
	$data = '';
	
	foreach ( $categories as $category ) {
		// al the question from an specific type of question and from all categories.
		$questions = $DB->get_records ( 'question', array (
				'category' => $category->id,
				'qtype' => 'multichoice' 
		) );
		foreach ( $questions as $question ) {
			if ($question->id == $questionid) {
				// request all the answers for an specific question.
				$answers = $DB->get_records ( 'question_answers', array (
						'question' => $questionid 
				) );
				$questiontext = $question->questiontext;
				// run all the answers
				foreach ( $answers as $answer ) {
					$answerid = $answer->id;
					$answertext = $answer->answer;
					$percentage = $answer->fraction;
					// url to redirect the button.
					$url = new moodle_url ( '/mod/throwquestions/record_answer.php', array (
							'id' => $cmid,
							'qid' => $questionid,
							'answerid' => $answerid,
							'percentage' => $percentage,
							'sender' => $sender,
							'receiver' => $receiver,
							'battleid' => $battleid 
					) );
					$answered = $OUTPUT->single_button ( $url, get_string ( 'iwantthisone', 'mod_throwquestions' ) );
					// Prepares the data for the table.
					$data [] = array (
							$answertext,
							$answered 
					);
				}
			}
		}
	}
	// Initialize the object table
	$table = new html_table ();
	// Creates the atributes
	$table->attributes ['style'] = "text-align:center;";
	// Table Headings
	$table->head = array (
			get_string ( 'answer', 'mod_throwquestions' ),
			get_string ( 'select', 'mod_throwquestions' ) 
	);
	// Insert the data in the table
	$table->data = $data;
	// Render the table in a variable.
	$answertable = html_writer::table ( $table );
	$result = array (
			'table' => $answertable,
			'question' => $questiontext 
	);
	return $result;
}
/**
 * This functions get all the pending battles and displays it in a table
 *
 * @param int $sender        	
 * @param int $cmid        	
 * @return A table with all the pending battles
 */
function get_all_challenges($sender, $cmid) {
	global $PAGE, $CFG, $OUTPUT, $DB, $USER;
	// Sets the parameters to search the battles pending
	$param = array (
			'receiver_id' => $sender,
			'status' => 0 
	);
	// Get all the pending battles in a variable
	$battles = $DB->get_records ( 'battle', $param );
	// Get the user(receiver) name.
	$receivername = $USER->firstname . ' ' . $USER->lastname;
	// Query to get the senders name.
	$getsendersnameqry = "SELECT firstname,lastname
								FROM {user} as u
								WHERE u.id=?";
	$data = '';
	foreach ( $battles as $battle ) {
		// Get all the senders for each battle.
		$sendername = $DB->get_records_sql ( $getsendersnameqry, array (
				$battle->sender_id 
		) );
		// Get the question from the battle
		$question = $DB->get_record ( 'question', array (
				'id' => $battle->question 
		) );
		foreach ( $sendername as $sendernames ) {
			// Sets the redirection URL for the button
			$url = new moodle_url ( '/mod/throwquestions/answer.php', array (
					'id' => $cmid,
					'battleid' => $battle->id,
					'qid' => $question->id,
					'sender' => $battle->sender_id,
					'receiver' => $USER->id 
			) );
			// Prepares the data for the table.
			$data [] = array (
					$battle->id,
					$sendernames->firstname . ' ' . $sendernames->lastname,
					$question->questiontext,
					$OUTPUT->single_button ( $url, get_string ( 'doyouwanttowanswerthequestion', 'mod_throwquestions' ) ) 
			);
		}
	}
	// Initialize the object table
	$table = new html_table ();
	// Creates the atributes
	$table->attributes ['style'] = "text-align:center;";
	// Table Headings
	$table->head = array (
			get_string ( 'battleid', 'mod_throwquestions' ),
			get_string ( 'sender', 'mod_throwquestions' ),
			get_string ( 'question', 'mod_throwquestions' ),
			'' 
	);
	// Insert the data in the table
	$table->data = $data;
	// Render the table in a variable.
	$battletable = html_writer::table ( $table );
	return $battletable;
}






