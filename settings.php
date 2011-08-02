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
 * Widgetspace module admin settings and defaults
 *
 * @package    mod
 * @subpackage widgetspace
 * @copyright  2011 Evgeny Bogdanov (http://vohtaski.blogspot.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_OPEN, RESOURCELIB_DISPLAY_POPUP));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_OPEN);

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configcheckbox('widgetspace/requiremodintro',
        get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), 1));
    $settings->add(new admin_setting_configmultiselect('widgetspace/displayoptions',
        get_string('displayoptions', 'widgetspace'), get_string('configdisplayoptions', 'widgetspace'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('widgetspacemodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox_with_advanced('widgetspace/printheading',
        get_string('printheading', 'widgetspace'), get_string('printheadingexplain', 'widgetspace'),
        array('value'=>1, 'adv'=>false)));
    $settings->add(new admin_setting_configcheckbox_with_advanced('widgetspace/printintro',
        get_string('printintro', 'widgetspace'), get_string('printintroexplain', 'widgetspace'),
        array('value'=>0, 'adv'=>false)));
    $settings->add(new admin_setting_configselect_with_advanced('widgetspace/display',
        get_string('displayselect', 'widgetspace'), get_string('displayselectexplain', 'widgetspace'),
        array('value'=>RESOURCELIB_DISPLAY_OPEN, 'adv'=>true), $displayoptions));
    $settings->add(new admin_setting_configtext_with_advanced('widgetspace/popupwidth',
        get_string('popupwidth', 'widgetspace'), get_string('popupwidthexplain', 'widgetspace'),
        array('value'=>620, 'adv'=>true), PARAM_INT, 7));
    $settings->add(new admin_setting_configtext_with_advanced('widgetspace/popupheight',
        get_string('popupheight', 'widgetspace'), get_string('popupheightexplain', 'widgetspace'),
        array('value'=>450, 'adv'=>true), PARAM_INT, 7));
}
