<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 satisfaction plugin for GLPI
 Copyright (C) 2016-2022 by the satisfaction Development Team.

 https://github.com/pluginsglpi/satisfaction
 -------------------------------------------------------------------------

 LICENSE

 This file is part of satisfaction.

 satisfaction is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 satisfaction is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with satisfaction. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Init the hooks of the plugins -Needed
 */

use Glpi\Plugin\Hooks;
use GlpiPlugin\Mydashboard\Menu as MydashboardMenu;
use GlpiPlugin\Satisfaction\Dashboard;
use GlpiPlugin\Satisfaction\Menu;
use GlpiPlugin\Satisfaction\NotificationTargetTicket;
use GlpiPlugin\Satisfaction\Profile;
use GlpiPlugin\Satisfaction\Reminder;
use GlpiPlugin\Satisfaction\SurveyAnswer;

define("PLUGIN_SATISFACTION_VERSION", "1.7.3");

// Minimal GLPI version, inclusive
define('PLUGIN_SATISFACTION_MIN_GLPI', '11.0');
// Maximum GLPI version, exclusive
define('PLUGIN_SATISFACTION_MAX_GLPI', '12.0');
global $CFG_GLPI;
define("PLUGINSATISFACTION_WEBDIR", $CFG_GLPI['root_doc'] . '/plugins/satisfaction');

function plugin_init_satisfaction()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::CHANGE_PROFILE]['satisfaction'] = [Profile::class, 'initProfile'];

    if (Plugin::isPluginActive('satisfaction')) {
       //if glpi is loaded
        if (Session::getLoginUserID()) {
            Plugin::registerClass(
                Profile::class,
                ['addtabon' => Profile::class]
            );

            $PLUGIN_HOOKS[Hooks::PRE_ITEM_FORM]['satisfaction'] = [
                SurveyAnswer::class, 'displaySatisfaction'];

            $PLUGIN_HOOKS[Hooks::PRE_ITEM_UPDATE]['satisfaction'][TicketSatisfaction::class] = [
                SurveyAnswer::class, 'preUpdateSatisfaction'];


            $PLUGIN_HOOKS[Hooks::ITEM_DELETE]['satisfaction'] = ['Ticket' => [Reminder::class, 'deleteItem']];

           //current user must have config rights
            if (Session::haveRight('plugin_satisfaction', READ)) {
                $config_page = 'front/survey.php';
                $PLUGIN_HOOKS[Hooks::CONFIG_PAGE]['satisfaction'] = $config_page;

                $PLUGIN_HOOKS[Hooks::MENU_TOADD]['satisfaction'] = ['admin' => Menu::class];
            }

            if (isset($_SESSION['glpiactiveprofile']['interface'])
             && $_SESSION['glpiactiveprofile']['interface'] == 'central') {
                $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['satisfaction'] = ["satisfaction.js"];
            }
            if (class_exists(MydashboardMenu::class)) {
                $PLUGIN_HOOKS['mydashboard']['satisfaction'] = [Dashboard::class];
            }
        }

        $PLUGIN_HOOKS[Hooks::ITEM_GET_EVENTS]['satisfaction'] =
            [\NotificationTargetTicket::class => [NotificationTargetTicket::class, 'addEvents']];

        $PLUGIN_HOOKS[Hooks::ITEM_GET_DATA]['satisfaction'] = [
            \NotificationTargetTicket::class => [SurveyAnswer::class,
                'addNotificationDatas']];
    }
}

/**
 * Get the name and the version of the plugin - Needed
 *
 * @return array
 */
function plugin_version_satisfaction()
{

    return [
      'name'           => __("More satisfaction", 'satisfaction'),
      'version'        => PLUGIN_SATISFACTION_VERSION,
        'author'       => "<a href='https//blogglpi.infotel.com'>Infotel</a>, Xavier CAILLAUD",
      'license'        => 'GPLv2+',
      'homepage'       => 'https://github.com/pluginsGLPI/satisfaction',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_SATISFACTION_MIN_GLPI,
            'max' => PLUGIN_SATISFACTION_MAX_GLPI,
         ]
      ]
    ];
}
