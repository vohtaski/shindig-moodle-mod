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
 * @package    mod
 * @subpackage widgetspace
 * @copyright  2011 Evgeny Bogdanov (http://vohtaski.blogspot.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in Widgetspace module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function widgetspace_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;

        default: return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function widgetspace_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function widgetspace_reset_userdata($data) {
    return array();
}

/**
 * List of view style log actions
 * @return array
 */
function widgetspace_get_view_actions() {
    return array('view','view all');
}

/**
 * List of update style log actions
 * @return array
 */
function widgetspace_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add widgetspace instance.
 * @param object $data
 * @param object $mform
 * @return int new widgetspace instance id
 */
function widgetspace_add_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid        = $data->coursemodule;
    $draftitemid = $data->widgetspace['itemid'];

    $data->timemodified = time();
    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    $displayoptions['printheading'] = $data->printheading;
    $displayoptions['printintro']   = $data->printintro;
    $data->displayoptions = serialize($displayoptions);

    $data->content       = $data->widgetspace['text'];
    $data->contentformat = $data->widgetspace['format'];

    $data->id = $DB->insert_record('widgetspace', $data);

    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));
    $context = get_context_instance(CONTEXT_MODULE, $cmid);

    if ($draftitemid) {
        $data->content = file_save_draft_area_files($draftitemid, $context->id, 'mod_widgetspace', 'content', 0, widgetspace_get_editor_options($context), $data->content);
        $DB->update_record('widgetspace', $data);
    }
    
    // add widgets for a widgetspace
    $widgetspace = $data;
    foreach ($widgetspace->gadget as $key => $value) {
        $value = trim($value);
        if (isset($value) && $value <> '') {
            $gadget = new stdClass();
            $gadget->url = $value;
            $gadget->widgetspaceid = $widgetspace->id;
            $gadget->timemodified = time();
            // set height and name of a gadget
            set_gadget_metadata($gadget->url,$gadget);
            $DB->insert_record("widgetspace_gadgets", $gadget);
        }
    }

    return $data->id;
}

/**
 * Update widgetspace instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function widgetspace_update_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid        = $data->coursemodule;
    $draftitemid = $data->widgetspace['itemid'];

    $data->timemodified = time();
    $data->id           = $data->instance;
    $data->revision++;

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    $displayoptions['printheading'] = $data->printheading;
    $displayoptions['printintro']   = $data->printintro;
    $data->displayoptions = serialize($displayoptions);

    $data->content       = $data->widgetspace['text'];
    $data->contentformat = $data->widgetspace['format'];
    
    //update, delete or insert gadgets
    $widgetspace = $data;
    foreach ($widgetspace->gadget as $key => $value) {
        $value = trim($value);
        $gadget = new stdClass();
        $gadget->url = $value;
        $gadget->widgetspaceid = $widgetspace->id;
        // set height and name of a gadget
        set_gadget_metadata($gadget->url,$gadget);
        $gadget->timemodified = time();
        if (isset($widgetspace->gadgetid[$key]) && !empty($widgetspace->gadgetid[$key])){//existing choice record
            $gadget->id=$widgetspace->gadgetid[$key];
            if (isset($value) && $value <> '') {
                $DB->update_record("widgetspace_gadgets", $gadget);
            } else { //empty old option - needs to be deleted.
                $DB->delete_records("widgetspace_gadgets", array("id"=>$gadget->id));
            }
        } else {
            if (isset($value) && $value <> '') {
                $DB->insert_record("widgetspace_gadgets", $gadget);
            }
        }
    }

    $DB->update_record('widgetspace', $data);

    $context = get_context_instance(CONTEXT_MODULE, $cmid);
    if ($draftitemid) {
        $data->content = file_save_draft_area_files($draftitemid, $context->id, 'mod_widgetspace', 'content', 0, widgetspace_get_editor_options($context), $data->content);
        $DB->update_record('widgetspace', $data);
    }

    return true;
}

/**
 * Request gadget metadata from shindig
 * 
 * Takes $gadget as a parameter and adds height and name for it
 */
function set_gadget_metadata($gadget_url,$gadget) {
    global $CFG;
    require_once($CFG->dirroot.'/mod/widgetspace/lib/container.php');
    
    $gadget_container = new GadgetContainer(null);
    $shindig_url = $gadget_container->get_shindig_url();
    
    $request = $shindig_url.'/gadgets/metadata?st=0:0:0:0:0:0:0';
  
    $c = new curl();
    $c->setopt(array('CURLOPT_TIMEOUT' => 3, 'CURLOPT_CONNECTTIMEOUT' => 3, 'CURLOPT_HTTPHEADER' => array("Content-Type: application/json","Accept: application/json")));
    // , "Content-length: ".strlen($data)
  
    $data = '{"context":{"view":"canvas","container":"default"},"gadgets":[{"url":"'.$gadget_url.'", "moduleId":0}]}';
    $response = $c->post($request,$data);
    $json = json_decode($response);
    // var_dump($json);
    $gadgets = $json->gadgets;
    //set height of gadget
    $gadget->height = ($gadgets[0]->height == 0) ? 200 : $gadgets[0]->height; 
    $gadget->name = $gadgets[0]->title; //set name of gadget
}

/**
 * Delete widgetspace instance.
 * @param int $id
 * @return bool true
 */
function widgetspace_delete_instance($id) {
    global $DB;

    if (!$widgetspace = $DB->get_record('widgetspace', array('id'=>$id))) {
        return false;
    }

    // note: all context files are deleted automatically

    $DB->delete_records('widgetspace', array('id'=>$widgetspace->id));

    return true;
}

/**
 * Return use outline
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $widgetspace
 * @return object|null
 */
function widgetspace_user_outline($course, $user, $mod, $widgetspace) {
    global $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'widgetspace',
                                              'action'=>'view', 'info'=>$widgetspace->id), 'time ASC')) {

        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new stdClass();
        $result->info = get_string('numviews', '', $numviews);
        $result->time = $lastlog->time;

        return $result;
    }
    return NULL;
}

/**
 * Return use complete
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $widgetspace
 */
function widgetspace_user_complete($course, $user, $mod, $widgetspace) {
    global $CFG, $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'widgetspace',
                                              'action'=>'view', 'info'=>$widgetspace->id), 'time ASC')) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strmostrecently = get_string('mostrecently');
        $strnumviews = get_string('numviews', '', $numviews);

        echo "$strnumviews - $strmostrecently ".userdate($lastlog->time);

    } else {
        print_string('neverseen', 'widgetspace');
    }
}

/**
 * Returns the users with data in one widgetspace
 *
 * @todo: deprecated - to be deleted in 2.2
 *
 * @param int $widgetspaceid
 * @return bool false
 */
function widgetspace_get_participants($widgetspaceid) {
    return false;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return object info
 */
function widgetspace_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    if (!$widgetspace = $DB->get_record('widgetspace', array('id'=>$coursemodule->instance), 'id, name, display, displayoptions')) {
        return NULL;
    }

    $info = new stdClass();
    $info->name = $widgetspace->name;

    if ($widgetspace->display != RESOURCELIB_DISPLAY_POPUP) {
        return $info;
    }

    $fullurl = "$CFG->wwwroot/mod/widgetspace/view.php?id=$coursemodule->id&amp;inpopup=1";
    $options = empty($widgetspace->displayoptions) ? array() : unserialize($widgetspace->displayoptions);
    $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
    $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
    $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
    $info->extra = "onclick=\"window.open('$fullurl', '', '$wh'); return false;\"";

    return $info;
}


/**
 * Lists all browsable file areas
 * @param object $course
 * @param object $cm
 * @param object $context
 * @return array
 */
function widgetspace_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['content'] = get_string('content', 'widgetspace');
    return $areas;
}

/**
 * File browsing support for widgetspace module content area.
 * @param object $browser
 * @param object $areas
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return object file_info instance or null if not found
 */
function widgetspace_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if (!has_capability('moodle/course:managefiles', $context)) {
        // students can not peak here!
        return null;
    }

    $fs = get_file_storage();

    if ($filearea === 'content') {
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_widgetspace', 'content', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_widgetspace', 'content', 0);
            } else {
                // not found
                return null;
            }
        }
        require_once("$CFG->dirroot/mod/widgetspace/locallib.php");
        return new widgetspace_content_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea], true, true, true, false);
    }

    // note: widgetspace_intro handled in file_browser automatically

    return null;
}

/**
 * Serves the widgetspace files.
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool false if file not found, does not return if found - just send the file
 */
function widgetspace_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);
    if (!has_capability('mod/widgetspace:view', $context)) {
        return false;
    }

    if ($filearea !== 'content') {
        // intro is handled automatically in pluginfile.php
        return false;
    }

    array_shift($args); // ignore revision - designed to prevent caching problems only

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_widgetspace/$filearea/0/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        $widgetspace = $DB->get_record('widgetspace', array('id'=>$cm->instance), 'id, legacyfiles', MUST_EXIST);
        if ($widgetspace->legacyfiles != RESOURCELIB_LEGACYFILES_ACTIVE) {
            return false;
        }
        if (!$file = resourcelib_try_file_migration('/'.$relativepath, $cm->id, $cm->course, 'mod_widgetspace', 'content', 0)) {
            return false;
        }
        //file migrate - update flag
        $widgetspace->legacyfileslast = time();
        $DB->update_record('widgetspace', $widgetspace);
    }

    // finally send the file
    send_stored_file($file, 86400, 0, $forcedownload);
}


/**
 * This function extends the global navigation for the site.
 * It is important to note that you should not rely on PAGE objects within this
 * body of code as there is no guarantee that during an AJAX request they are
 * available
 *
 * @param navigation_node $navigation The widgetspace node within the global navigation
 * @param stdClass $course The course object returned from the DB
 * @param stdClass $module The module object returned from the DB
 * @param stdClass $cm The course module instance returned from the DB
 */
function widgetspace_extend_navigation($navigation, $course, $module, $cm) {
    /**
     * This is currently just a stub so that it can be easily expanded upon.
     * When expanding just remove this comment and the line below and then add
     * you content.
     */
    $navigation->nodetype = navigation_node::NODETYPE_LEAF;
}

/**
 * Return a list of widgetspace types
 * @param string $widgetspacetype current widgetspace type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function widgetspace_widgetspace_type_list($widgetspacetype, $parentcontext, $currentcontext) {
    $module_widgetspacetype = array('mod-widgetspace-*'=>get_string('widgetspace-mod-widgetspace-x', 'widgetspace'));
    return $module_widgetspacetype;
}
