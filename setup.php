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

define ("PLUGIN_SATISFACTION_VERSION", "1.6.0");

// Minimal GLPI version, inclusive
define('PLUGIN_SATISFACTION_MIN_GLPI', '10.0');
// Maximum GLPI version, exclusive
define('PLUGIN_SATISFACTION_MAX_GLPI', '11.0');

function plugin_init_satisfaction() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['satisfaction'] = true;
   $PLUGIN_HOOKS['change_profile']['satisfaction'] = [PluginSatisfactionProfile::class, 'initProfile'];

   $plugin = new Plugin();
   if ($plugin->isInstalled('satisfaction') && $plugin->isActivated('satisfaction')) {

      //if glpi is loaded
      if (Session::getLoginUserID()) {

         Plugin::registerClass(PluginSatisfactionProfile::class,
                               ['addtabon' => Profile::class]);

         $PLUGIN_HOOKS['pre_item_form']['satisfaction'] = [PluginSatisfactionSurveyAnswer::class, 'displaySatisfaction'];

         $PLUGIN_HOOKS['pre_item_update']['satisfaction'][TicketSatisfaction::class] = [PluginSatisfactionSurveyAnswer::class,
                                                                                        'preUpdateSatisfaction'];

         $PLUGIN_HOOKS['item_get_events']['satisfaction'] = [NotificationTargetTicket::class => 'getEvents'];

         $PLUGIN_HOOKS['item_delete']['satisfaction'] = ['Ticket' => ['PluginSatisfactionReminder', 'deleteItem']];

         //current user must have config rights
         if (Session::haveRight('plugin_satisfaction', READ)) {
            $config_page = 'front/survey.php';
            $PLUGIN_HOOKS['config_page']['satisfaction'] = $config_page;

            $PLUGIN_HOOKS["menu_toadd"]['satisfaction'] = ['admin' => PluginSatisfactionMenu::class];
         }

         if (isset($_SESSION['glpiactiveprofile']['interface'])
             && $_SESSION['glpiactiveprofile']['interface'] == 'central') {
            $PLUGIN_HOOKS['add_javascript']['satisfaction'] = ["satisfaction.js"];
         }
         if (class_exists('PluginMydashboardMenu')) {
            $PLUGIN_HOOKS['mydashboard']['satisfaction'] = [PluginSatisfactionDashboard::class];
         }
      }

      $PLUGIN_HOOKS['item_get_datas']['satisfaction'] = [NotificationTargetTicket::class => [PluginSatisfactionSurveyAnswer::class,
         'addNotificationDatas']];
   }
}

/**
 * Get the name and the version of the plugin - Needed
 *
 * @return array
 */
function plugin_version_satisfaction() {

   $author = "<a href='www.teclib.com'>TECLIB'</a>";
   $author.= ", <a href='http://blogglpi.infotel.com/'>Infotel</a>";
   return [
      'name'           => __("More satisfaction", 'satisfaction'),
      'version'        => PLUGIN_SATISFACTION_VERSION,
      'author'         => $author,
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
