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
 * Widgetspace configuration form
 *
 * @package    mod
 * @subpackage widgetspace
 * @copyright  2011 Evgeny Bogdanov (http://vohtaski.blogspot.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/widgetspace/locallib.php');
require_once($CFG->libdir.'/filelib.php');

class mod_widgetspace_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $config = get_config('widgetspace');

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $this->add_intro_editor($config->requiremodintro);

        //-------------------------------------------------------
        $mform->addElement('header', 'contentsection', get_string('contentheader', 'widgetspace'));
        $mform->addElement('editor', 'widgetspace', get_string('content', 'widgetspace'), null, widgetspace_get_editor_options($this->context));
        $mform->addRule('widgetspace', get_string('required'), 'required', null, 'client');

        // $mform->addElement('text', 'gadgeturl', get_string('gadgeturl', 'widgetspace'));
        // --------------------------------------------------------
        // number of gadget columns 
        $menuoptions = array();
        $menuoptions[0] = "1";
        $menuoptions[1] = "2";
        $menuoptions[2] = "3";
        $mform->addElement('header', 'timerestricthdr', get_string('layoutsettings', 'widgetspace'));
        $mform->addElement('select', 'numbercolumn', get_string('numbercolumn', 'widgetspace'), $menuoptions);
        //-------------------------------------------------------------------------------
        
        $repeatarray = array();
        $repeatarray[] = &MoodleQuickForm::createElement('header', '', get_string('gadget','widgetspace').' {no}');
        $repeatarray[] = &MoodleQuickForm::createElement('text', 'gadget', get_string('gadgeturl','widgetspace'));
        $repeatarray[] = &MoodleQuickForm::createElement('hidden', 'gadgetid', 0);

        if ($this->_instance){
            $repeatno = $DB->count_records('widgetspace_gadgets', array('widgetspaceid'=>$this->_instance));
            $repeatno += 2;
        } else {
            $repeatno = 5;
        }

        $repeateloptions = array();
        $repeateloptions['gadget']['helpbutton'] = array('widgetspacegadgets', 'widgetspace');
        $mform->setType('gadget', PARAM_CLEAN);

        $mform->setType('gadgetid', PARAM_INT);

        $this->repeat_elements($repeatarray, $repeatno,
                    $repeateloptions, 'gadget_repeats', 'gadget_add_fields', 3);
                    
                            
        //-------------------------------------------------------
        $mform->addElement('header', 'optionssection', get_string('optionsheader', 'widgetspace'));

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }
        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'widgetspace'), $options);
            $mform->setDefault('display', $config->display);
            $mform->setAdvanced('display', $config->display_adv);
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'widgetspace'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);
            $mform->setAdvanced('popupwidth', $config->popupwidth_adv);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'widgetspace'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
            $mform->setAdvanced('popupheight', $config->popupheight_adv);
        }

        $mform->addElement('advcheckbox', 'printheading', get_string('printheading', 'widgetspace'));
        $mform->setDefault('printheading', $config->printheading);
        $mform->setAdvanced('printintro', $config->printheading_adv);
        $mform->addElement('advcheckbox', 'printintro', get_string('printintro', 'widgetspace'));
        $mform->setDefault('printintro', $config->printintro);
        $mform->setAdvanced('printintro', $config->printintro_adv);

        // add legacy files flag only if used
        if (isset($this->current->legacyfiles) and $this->current->legacyfiles != RESOURCELIB_LEGACYFILES_NO) {
            $options = array(RESOURCELIB_LEGACYFILES_DONE   => get_string('legacyfilesdone', 'widgetspace'),
                             RESOURCELIB_LEGACYFILES_ACTIVE => get_string('legacyfilesactive', 'widgetspace'));
            $mform->addElement('select', 'legacyfiles', get_string('legacyfiles', 'widgetspace'), $options);
            $mform->setAdvanced('legacyfiles', 1);
        }

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();

        //-------------------------------------------------------
        $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
    }

    function data_preprocessing(&$default_values) {
      global $DB;
      
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('widgetspace');
            $default_values['widgetspace']['format'] = $default_values['contentformat'];
            $default_values['widgetspace']['text']   = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_widgetspace', 'content', 0, widgetspace_get_editor_options($this->context), $default_values['content']);
            $default_values['widgetspace']['itemid'] = $draftitemid;
        }
        if (!empty($default_values['displayoptions'])) {
            $displayoptions = unserialize($default_values['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $default_values['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printheading'])) {
                $default_values['printheading'] = $displayoptions['printheading'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
        }
        
        if (!empty($this->current->instance) && ($gadgets = $DB->get_records_menu('widgetspace_gadgets',array('widgetspaceid'=>$this->current->instance), 'id', 'id,url'))) {
            $widgetspaceids=array_keys($gadgets);
            $gadgets=array_values($gadgets);

            foreach (array_keys($gadgets) as $key){
                $default_values['gadget['.$key.']'] = $gadgets[$key];
                $default_values['gadgetid['.$key.']'] = $widgetspaceids[$key];
            }

        }
    }
    
    // function data_preprocessing(&$default_values){
    //     
    // 
    //     // if (empty($default_values['timeopen'])) {
    //     //     $default_values['timerestrict'] = 0;
    //     // } else {
    //     //     $default_values['timerestrict'] = 1;
    //     // }
    // 
    // }
}

