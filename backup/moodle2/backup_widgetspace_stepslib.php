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
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define all the backup steps that will be used by the backup_widgetspace_activity_task
 */

/**
 * Define the complete widgetspace structure for backup, with file and id annotations
 */
class backup_widgetspace_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $widgetspace = new backup_nested_element('widgetspace', array('id'), array(
            'name', 'intro', 'introformat', 'numbercolumn', 'content', 'contentformat',
            'legacyfiles', 'legacyfileslast', 'display', 'displayoptions',
            'revision', 'timemodified'));
        // Build the tree
        // (love this)

        // Define sources
        //$widgetspace->set_source_table('widgetspace', array('id' => backup::VAR_ACTIVITYID));
        $widgetspace->set_source_table('widgetspace', array('id' => backup::VAR_ACTIVITYID));

        
        //still need to backup the gadgets data related to this widgetspace;
        $gadgets = new backup_nested_element('gadgets', array('id'), array(
            'widgetspaceid', 'url', 'name', 'height', 'thumbnail',
            'screenshot', 'description', 'timemodified'));
        $widgetspace->add_child($gadgets);
        
        $gadgets->set_source_sql('SELECT * FROM {widgetspace_gadgets} WHERE  widgetspaceid= ?', array(backup::VAR_ACTIVITYID));
        
        // Define id annotations
        // (none)

        // Define file annotations
        $widgetspace->annotate_files('mod_widgetspace', 'intro', null); // This file areas haven't itemid
        $widgetspace->annotate_files('mod_widgetspace', 'content', null); // This file areas haven't itemid

        // Return the root element (widgetspace), wrapped into standard activity structure
        return $this->prepare_activity_structure($widgetspace);
    }
}
