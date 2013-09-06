<?php

/**
 * Handles viewing a certificate
 *
 * @package    mod
 * @subpackage simplecertificate
 * @copyright  Carlos Fonseca <carlos.alexandre@outlook.com>, Chardelle Busch, Mark Nelson <mark@moodle.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once("$CFG->dirroot/mod/simplecertificate/lib.php");
require_once("$CFG->libdir/pdflib.php");
require_once("$CFG->dirroot/mod/simplecertificate/locallib.php");

$id = required_param('id', PARAM_INT); // Course Module ID
$action = optional_param('action', '', PARAM_ALPHA);
$tab = optional_param('tab', simplecertificate::DEFAULT_VIEW, PARAM_INT);
$sort = optional_param('sort', '', PARAM_RAW);
$type = optional_param('type', '', PARAM_ALPHA);
$page = optional_param('page', 0, PARAM_INT);
// TODO colocar SIMPLECERT_PER_PAGE e SIMPLECERT_MAX_PER_PAGE no settings
$perpage = optional_param('perpage', SIMPLECERT_PER_PAGE, PARAM_INT);

if (! $cm = get_coursemodule_from_id( 'simplecertificate', $id)) {
	print_error('Course Module ID was incorrect');
}

if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
	print_error('course is misconfigured');
}

if (! $certificate = $DB->get_record('simplecertificate', array('id' => $cm->instance))) {
	print_error('course module is incorrect');
}

require_login( $course->id, false, $cm);
// Set thr context
$context = context_module::instance ( $cm->id );
require_capability('mod/simplecertificate:view', $context);
$canmanage = has_capability('mod/simplecertificate:manage', $context);

$url = new moodle_url('/mod/simplecertificate/view.php', array (
		'id' => $cm->id,
		'tab' => $tab,
		'page' => $page,
		'perpage' => $perpage
));

if ($type) {
	$url->param('type', $type);
}

if ($sort) {
	$url->param ('sort', $sort);
}

if ($action) {
	$url->param ('action', $action);
}

// log update
$simplecertificate = new simplecertificate($certificate, $context);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Initialize $PAGE, compute blocks
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($certificate->name));
$PAGE->set_heading(format_string($course->fullname));


switch ($tab) {
	case $simplecertificate::ISSUED_CERTIFCADES_VIEW :
		$simplecertificate->issued_certificates_view($url);
	break;
	
	case $simplecertificate::BULK_ISSUE_CERTIFCADES_VIEW :
		$simplecertificate->bulk_certificates_view($url);
	break;
	
	default :
		$simplecertificate->default_view($url, $canmanage);
	break;
}
