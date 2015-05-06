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
 * Defines the version and other meta-info about the plugin
 *
 * Setting the $plugin->version to 0 prevents the plugin from being installed.
 * See https://docs.moodle.org/dev/version.php for more info.
 *
 * @package mod_throwquestions
 * @copyright 2015 Xiu-Fong Lin <xlin@alumnos.uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/config.php'); // Required
require_once ($CFG->dirroot . "/mod/throwquestions/locallib.php");

global $PAGE, $CFG, $OUTPUT, $DB;

$cmid = required_param ( 'id', PARAM_INT ); // Course module id

if (! $cm = get_coursemodule_from_id ( 'throwquestions', $cmid )) {
	print_error ( "Invalid Course Module" );
}
if (! $throwquestion = $DB->get_record ( 'throwquestions', array (
		'id' => $cm->instance 
) )) {
	print_error ( "error" );
}
if (! $course = $DB->get_record ( 'course', array (
		'id' => $throwquestion->course 
) )) {
	print_error ( 'error' );
}

$url = new moodle_url ( '/mod/throwquestions/view.php', array (
		'id' => $cm->id 
) ); // URL
     
// context module
$context = context_module::instance ( $cm->id );

// require login
require_login ( $course->id );

$PAGE->set_url ( $url );
$PAGE->set_context ( $context );
$PAGE->set_course ( $course );
$PAGE->set_heading ( $course->fullname );
$PAGE->navbar->add ( get_string ( "throwquestions", 'mod_throwquestions' ) );
$PAGE->set_pagelayout ( 'standard' );

$users = throwquestions_get_students ( $course->id );
// make an array for the table data
$data = '';
foreach ( $users as $user ) {
	$data [] = array (
			$user->name .' '.$user->last,
			'' 
	);
}
// create table class
$table = new html_table ();
$table->attributes ['style'] = "width: 100%; text-align:center;";
$table->head = array (
		'Alumnos',
		'Algo' 
);
$table->data = $data;
$userstable = html_writer::table ( $table );

/* ----------VIEW---------- */

echo $OUTPUT->header ();
echo $OUTPUT->heading ( get_string ( "throwquestions", 'mod_throwquestions' ) );
// print table
echo $userstable;

echo $OUTPUT->footer ();
