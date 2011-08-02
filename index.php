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
 * List of all widgetspaces in course
 *
 * @package    mod
 * @subpackage widgetspace
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_widgetspacelayout('incourse');

add_to_log($course->id, 'widgetspace', 'view all', "index.php?id=$course->id", '');

$strwidgetspace         = get_string('modulename', 'widgetspace');
$strwidgetspaces        = get_string('modulenameplural', 'widgetspace');
$strsectionname  = get_string('sectionname', 'format_'.$course->format);
$strname         = get_string('name');
$strintro        = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/widgetspace/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strwidgetspaces);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strwidgetspaces);
echo $OUTPUT->header();

if (!$widgetspaces = get_all_instances_in_course('widgetspace', $course)) {
    notice(get_string('thereareno', 'moodle', $strwidgetspaces), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_all_sections($course->id);
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $table->head  = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strlastmodified, $strname, $strintro);
    $table->align = array ('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($widgetspaces as $widgetspace) {
    $cm = $modinfo->cms[$widgetspace->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($widgetspace->section !== $currentsection) {
            if ($widgetspace->section) {
                $printsection = get_section_name($course, $sections[$widgetspace->section]);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $widgetspace->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($widgetspace->timemodified)."</span>";
    }

    $class = $widgetspace->visible ? '' : 'class="dimmed"'; // hidden modules are dimmed

    $table->data[] = array (
        $printsection,
        "<a $class href=\"view.php?id=$cm->id\">".format_string($widgetspace->name)."</a>",
        format_module_intro('widgetspace', $widgetspace, $cm->id));
}

echo html_writer::table($table);

echo $OUTPUT->footer();
