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
 * Folder module upgrade related helper functions
 *
 * @package    mod
 * @subpackage widgetspace
 * @copyright  2011 Evgeny Bogdanov (http://vohtaski.blogspot.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Migrate widgetspace module data from 1.9 resource_old table to new widgetspace table
 * @return void
 */
function widgetspace_20_migrate() {
    global $CFG, $DB;
    require_once("$CFG->libdir/filelib.php");
    require_once("$CFG->libdir/resourcelib.php");
    require_once("$CFG->dirroot/course/lib.php");

    if (!file_exists("$CFG->dirroot/mod/resource/db/upgradelib.php")) {
        // bad luck, somebody deleted resource module
        return;
    }

    require_once("$CFG->dirroot/mod/resource/db/upgradelib.php");

    // create resource_old table and copy resource table there if needed
    if (!resource_20_prepare_migration()) {
        // no modules or fresh install
        return;
    }

    $fs = get_file_storage();

    $candidates = $DB->get_recordset('resource_old', array('type'=>'html', 'migrated'=>0));
    foreach ($candidates as $candidate) {
        widgetspace_20_migrate_candidate($candidate, $fs, FORMAT_HTML);
    }
    $candidates->close();

    $candidates = $DB->get_recordset('resource_old', array('type'=>'text', 'migrated'=>0));
    foreach ($candidates as $candidate) {
        //there might be some rubbish instead of format int value
        $format = (int)$candidate->reference;
        if ($format < 0 or $format > 4) {
            $format = FORMAT_MOODLE;
        }
        widgetspace_20_migrate_candidate($candidate, $fs, $format);
    }
    $candidates->close();

    // clear all course modinfo caches
    rebuild_course_cache(0, true);

}

function widgetspace_20_migrate_candidate($candidate, $fs, $format) {
    global $CFG, $DB;
    upgrade_set_timeout();

    if ($CFG->texteditors !== 'textarea') {
        $intro       = text_to_html($candidate->intro, false, false, true);
        $introformat = FORMAT_HTML;
    } else {
        $intro       = $candidate->intro;
        $introformat = FORMAT_MOODLE;
    }

    $widgetspace = new stdClass();
    $widgetspace->course        = $candidate->course;
    $widgetspace->name          = $candidate->name;
    $widgetspace->intro         = $intro;
    $widgetspace->introformat   = $introformat;
    $widgetspace->content       = $candidate->alltext;
    $widgetspace->contentformat = $format;
    $widgetspace->revision      = 1;
    $widgetspace->timemodified  = time();

    // convert links to old course files - let the automigration do the actual job
    $usedfiles = array("$CFG->wwwroot/file.php/$widgetspace->course/", "$CFG->wwwroot/file.php?file=/$widgetspace->course/");
    $widgetspace->content = str_ireplace($usedfiles, '@@PLUGINFILE@@/', $widgetspace->content);
    if (strpos($widgetspace->content, '@@PLUGINFILE@@/') === false) {
        $widgetspace->legacyfiles = RESOURCELIB_LEGACYFILES_NO;
    } else {
        $widgetspace->legacyfiles = RESOURCELIB_LEGACYFILES_ACTIVE;
    }

    $options = array('printheading'=>0, 'printintro'=>0);
    if ($candidate->popup) {
        $widgetspace->display = RESOURCELIB_DISPLAY_POPUP;
        if ($candidate->popup) {
            $rawoptions = explode(',', $candidate->popup);
            foreach ($rawoptions as $rawoption) {
                list($name, $value) = explode('=', trim($rawoption), 2);
                if ($value > 0 and ($name == 'width' or $name == 'height')) {
                    $options['popup'.$name] = $value;
                    continue;
                }
            }
        }
    } else {
        $widgetspace->display = RESOURCELIB_DISPLAY_OPEN;
    }
    $widgetspace->displayoptions = serialize($options);

    $widgetspace = resource_migrate_to_module('widgetspace', $candidate, $widgetspace);

    // now try to migrate files from site files
    // note: this can not work for html widgetspaces or files with other relatively linked files :-(
    $siteid = get_site()->id;
    if (preg_match_all("|$CFG->wwwroot/file.php(\?file=)?/$siteid(/[^\s'\"&\?#]+)|", $widgetspace->content, $matches)) {
        $context     = get_context_instance(CONTEXT_MODULE, $candidate->cmid);
        $sitecontext = get_context_instance(CONTEXT_COURSE, $siteid);
        $file_record = array('contextid'=>$context->id, 'component'=>'mod_widgetspace', 'filearea'=>'content', 'itemid'=>0);
        $fs = get_file_storage();
        foreach ($matches[2] as $i=>$sitefile) {
            if (!$file = $fs->get_file_by_hash(sha1("/$sitecontext->id/course/legacy/0".$sitefile))) {
                continue;
            }
            try {
                $fs->create_file_from_storedfile($file_record, $file);
                $widgetspace->content = str_replace($matches[0][$i], '@@PLUGINFILE@@'.$sitefile, $widgetspace->content);
            } catch (Exception $x) {
            }
        }
        $DB->update_record('widgetspace', $widgetspace);
    }
}
