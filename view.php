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
require_once (dirname ( __FILE__ ) . '/../../config.php'); // Required

global $PAGE, $CFG, $OUTPUT, $DB;

$id = required_param ( 'id', PARAM_INT ); // Course.
if (! $cm = get_coursemodule_from_id ( 'throwquestions', $id )) {
	print_error (  "Invalid Course Module");
}

require_login (); // require login
$strname = "THROW QUESTION";
$url = new moodle_url ( '/mod/throwquestions/view.php' );
$context = context_system::instance ($cm->id); // context_system::instance();
$PAGE->set_context ( $context );
$PAGE->set_url ( $url );
$PAGE->set_url ( '/mod/throwquestions/view.php', array (
		'id' => $id 
) );
$PAGE->navbar->add ( $strname );
$PAGE->set_pagelayout ( 'standard' );

echo $OUTPUT->header ();
echo $OUTPUT->heading ( $strname );
$sqlusers = "SELECT u.username as name, u.lastname as last,c.fullname as coursename from {user} as u 
			INNER JOIN {user_enrolments} as ue ON u.id=ue.userid
			INNER JOIN {course} as c ON ue.enrolid=c.id
			WHERE c.id=? and ";
$users = $DB->get_records_sql ( $sqlusers, array (
		$id 
) );
$data = '';
foreach ( $users as $user ) {
	$data [] = array (
			$user->name . $user->last,
			'' 
	);
}
$table = new html_table ();
$table->attributes ['style'] = "width: 100%; text-align:center;";
$table->head = array (
		'Alumnos',
		'Algo' 
);
$table->data = $data;
echo html_writer::table ( $table );

echo $OUTPUT->footer ();
