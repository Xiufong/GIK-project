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
 * Function to get all the students in the course.
 *
 * @param int $courseid        	
 * @return An object with all the students
 */
function throwquestions_get_students($courseid) {
	global $DB;
	
	$query = 'SELECT u.id, u.idnumber, u.firstname as name, u.lastname as last, e.enrol
			FROM {user_enrolments} ue
			JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = ?)
			JOIN {context} c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
			JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.roleid = 5 AND ra.userid = ue.userid)
			JOIN {user} u ON (ue.userid = u.id)
			ORDER BY lastname ASC';
	
	// Se toman los resultados del query dentro de una variable.
	$rs = $DB->get_recordset_sql ( $query, array (
			$courseid 
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
function get_all_students($users, $cmid) {
	global $PAGE, $CFG, $OUTPUT, $DB;
	
	$data = '';
	foreach ( $users as $user ) {
		$userid = $user->id;
		$url = new moodle_url ( '/mod/throwquestions/questions.php', array (
				'id' => $cmid,
				'user' => $userid 
		) );
		$data [] = array (
				$user->name . ' ' . $user->last,
				$userid,
				$OUTPUT->single_button ( $url, 'Challenge' ) 
		);
	}
	$table = new html_table ();
	$table->attributes ['style'] = "width: 50%; text-align:center;";
	$table->head = array (
			'Students',
			'ID',
			'Who do you want to battle?' 
	);
	$table->data = $data;
	
	$userstable = html_writer::table ( $table );
	return $userstable;
}
/**
 * This function get a table with all the question from que question bank in the course.
 *
 * @param int $context        	
 * @return A table with all the multi choice questions from the question bank
 */
function get_all_the_questions_from_question_bank_table($context, $cmid) {
	global $PAGE, $CFG, $OUTPUT, $DB;
	
	$categorias = get_categories_for_contexts ( $context, 'name ASC' );
	
	$data = '';
	
	foreach ( $categorias as $categoria ) {
		$preguntas = $DB->get_records ( 'question', array (
				'category' => $categoria->id,
				'qtype' => 'multichoice' 
		) );
		foreach ( $preguntas as $pregunta ) {
			$url = new moodle_url ( '/mod/throwquestions/view.php', array (
					'id' => $cmid 
			) );
			$data [] = array (
					$pregunta->questiontext . ' ' . $pregunta->id,
					$OUTPUT->single_button ( $url, 'I want this question!' ) 
			);
		}
	}
	$table = new html_table ();
	$table->attributes ['style'] = "text-align:center;";
	$table->head = array (
			'Question',
			'Select' 
	);
	$table->data = $data;
	$questiontable = html_writer::table ( $table );
	
	return $questiontable;
}
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
	$tab = new tabobject ( 'list', $CFG->wwwroot . "/mod/throwquestions/view.php?id={$cmid}", 'Throwquestions' );
	$tab->subtree [] = new tabobject ( 'viewlist', $CFG->wwwroot . "/mod/throwquestions/view.php?id={$cmid}", 'List' );
	//check if has capability to create questions
	if (has_capability ( 'moodle/question:add', $context )) {
		$tab->subtree [] = new tabobject ( 'create', $CFG->wwwroot . "/question/question.php?category=&courseid={$courseid}&sesskey={$sesskey}&qtype=multichoice&returnurl=%2Fmod%2Fthrowquestions%2Fview.php%3Fid%3D{$cmid}&courseid={$courseid}&category=1", 'Create Question' );
	}
	$tab->subtree [] = new tabobject ( 'check', $CFG->wwwroot . "/mod/throwquestions/view.php?id={$cmid}", 'Check' );
	$tabs [] = $tab;
	return $tabs;
}
