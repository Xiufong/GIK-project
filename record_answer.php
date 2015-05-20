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
 *         
 */
require_once ('../../config.php');
require_once ($CFG->dirroot . "/mod/throwquestions/locallib.php");
require_once ($CFG->dirroot . "/lib/questionlib.php");

global $PAGE, $CFG, $OUTPUT, $DB;

$cmid = required_param ( 'id', PARAM_INT );
$questionid = required_param ( 'qid', PARAM_INT );
$sender = required_param ( 'sender', PARAM_INT );
$receiver = required_param ( 'receiver', PARAM_INT );
$battleid = required_param ( 'battleid', PARAM_INT );
$answerid = required_param ( 'answerid', PARAM_INT );
$percentage = required_param ( 'percentage', PARAM_INT );
$url = new moodle_url ( '/mod/throwquestions/view.php', array (
		'id' => $cmid 
) );
if ($percentage == 1) {
	$winner = $receiver;
	$message = 'Yay congrats you have won!';
} elseif ($percentage == - 1) {
	$winner = $sender;
	$message = 'Sorry you have lost, get some REVENGE';
} else {
	$message = "The answer of this question doesn't fulfill the parameters";
	redirect ( $url, $message, 10 );
}
$update = array (
		'id' => $battleid,
		'status' => 1,
		'winner' => $winner,
		'answer' => $answerid 
);
$endbattle = $DB->update_record ( 'battle', $update );

if (! $endbattle) {
	$validation = "Error";
	redirect($url,$validation,10);
}else {
	redirect($url,$message,10);
}



