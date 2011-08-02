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
 * Widgetspace module version information
 *
 * @package    mod
 * @subpackage widgetspace
 * @copyright  2011 Evgeny Bogdanov (http://vohtaski.blogspot.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/widgetspace/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID
$p       = optional_param('p', 0, PARAM_INT);  // Widgetspace instance ID
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

if ($p) {
    if (!$widgetspace = $DB->get_record('widgetspace', array('id'=>$p))) {
        print_error('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('widgetspace', $widgetspace->id, $widgetspace->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('widgetspace', $id)) {
        print_error('invalidcoursemodule');
    }
    $widgetspace = $DB->get_record('widgetspace', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/widgetspace:view', $context);

add_to_log($course->id, 'widgetspace', 'view', 'view.php?id='.$cm->id, $widgetspace->id, $cm->id);

// Update 'viewed' state if required by completion system
require_once($CFG->libdir . '/completionlib.php');
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/widgetspace/view.php', array('id' => $cm->id));

$options = empty($widgetspace->displayoptions) ? array() : unserialize($widgetspace->displayoptions);

if ($inpopup and $widgetspace->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_widgetspacelayout('popup');
    $PAGE->set_title($course->shortname.': '.$widgetspace->name);
    if (!empty($options['printheading'])) {
        $PAGE->set_heading($widgetspace->name);
    } else {
        $PAGE->set_heading('');
    }
    echo $OUTPUT->header();

} else {
    $PAGE->set_title($course->shortname.': '.$widgetspace->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($widgetspace);
    echo $OUTPUT->header();

    if (!empty($options['printheading'])) {
        echo $OUTPUT->heading(format_string($widgetspace->name), 2, 'main', 'widgetspaceheading');
    }
}

if (!empty($options['printintro'])) {
    if (trim(strip_tags($widgetspace->intro))) {
        echo $OUTPUT->box_start('mod_introbox', 'widgetspaceintro');
        echo format_module_intro('widgetspace', $widgetspace, $cm->id);
        echo $OUTPUT->box_end();
    }
}

$content = file_rewrite_pluginfile_urls($widgetspace->content, 'pluginfile.php', $context->id, 'mod_widgetspace', 'content', $widgetspace->revision);
$formatoptions = array('noclean'=>true, 'overflowdiv'=>true);
$content = format_text($content, $widgetspace->contentformat, $formatoptions, $course->id);
echo $OUTPUT->box($content, "generalbox center clearfix");

// add widgets container
// build container with 1-2-3 columns
require_once($CFG->dirroot.'/mod/widgetspace/lib/container.php');
$gadget_container = new GadgetContainer($widgetspace->id);
$gadget_container->build();

// add all gadgets for the container
require_once($CFG->dirroot.'/mod/widgetspace/lib/gadget.php');
$gadget = new Gadget();
$gadget->build($widgetspace->gadgeturl,$widgetspace->id);

$strlastmodified = get_string("lastmodified");
echo "<div class=\"modified\">$strlastmodified: ".userdate($widgetspace->timemodified)."</div>";

echo $OUTPUT->footer();
