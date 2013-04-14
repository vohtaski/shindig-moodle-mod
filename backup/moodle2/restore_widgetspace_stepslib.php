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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_widgetspace_activity_task
 */

/**
 * Structure step to restore one widgetspace activity
 */
class restore_widgetspace_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('widgetspace', '/activity/widgetspace');
		$paths[] = new restore_path_element('gadgets','/activity/widgetspace/gadgets');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_widgetspace($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // insert the widgetspace record
        $newitemid = $DB->insert_record('widgetspace', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
        $this->set_mapping('widgetspace', $oldid, $newitemid);
    }
	protected function process_gadgets($data) {
		global $DB;
		$data = (object)$data;
        $oldid = $data->id;
		$data->widgetspaceid = $this->get_new_parentid('widgetspace');
		$data->timemodified = $this->apply_date_offset($data->timemodified);
		$newitemid = $DB->insert_record('widgetspace_gadgets', $data);
	}
    protected function after_execute() {
        // Add widgetspace related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_widgetspace', 'intro', null);
        $this->add_related_files('mod_widgetspace', 'content', null);
    }
}
