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
 * @return bool
 */
function plugin_satisfaction_install() {
   global $DB;

   include_once(Plugin::getPhpDir('satisfaction')."/inc/profile.class.php");
   include_once(Plugin::getPhpDir('satisfaction')."/inc/notificationtargetticket.class.php");

   if (!$DB->tableExists("glpi_plugin_satisfaction_surveys")) {
      $DB->runFile(Plugin::getPhpDir('satisfaction')."/install/sql/empty-1.6.0.sql");

   } else {
      if (!$DB->fieldExists("glpi_plugin_satisfaction_surveyquestions", "type")) {
         $DB->runFile(Plugin::getPhpDir('satisfaction')."/install/sql/update-1.1.0.sql");
      }
      //version 1.2.1
      if (!$DB->fieldExists("glpi_plugin_satisfaction_surveyquestions", "default_value")) {
         $DB->runFile(Plugin::getPhpDir('satisfaction')."/install/sql/update-1.2.2.sql");
      }
      //version 1.4.1
      if (!$DB->tableExists("glpi_plugin_satisfaction_surveytranslations")) {
         $DB->runFile(Plugin::getPhpDir('satisfaction')."/install/sql/update-1.4.1.sql");
      }

      //version 1.4.3
      if (!$DB->tableExists("glpi_plugin_satisfaction_surveyreminders")) {
         $DB->runFile(Plugin::getPhpDir('satisfaction')."/install/sql/update-1.4.3.sql");
      }

      //version 1.4.5
      if (!$DB->fieldExists("glpi_plugin_satisfaction_surveys", "reminders_days")) {
         $DB->runFile(Plugin::getPhpDir('satisfaction')."/install/sql/update-1.4.5.sql");
      }
   }

   PluginSatisfactionNotificationTargetTicket::install();
   PluginSatisfactionProfile::initProfile();
   PluginSatisfactionProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

   CronTask::Register(PluginSatisfactionReminder::class, PluginSatisfactionReminder::CRON_TASK_NAME, DAY_TIMESTAMP);
   return true;
}

/**
 * @return bool
 */
function plugin_satisfaction_uninstall() {
   global $DB;

   include_once(Plugin::getPhpDir('satisfaction')."/inc/profile.class.php");
   include_once(Plugin::getPhpDir('satisfaction')."/inc/menu.class.php");
   include_once(Plugin::getPhpDir('satisfaction')."/inc/notificationtargetticket.class.php");

   $tables = [
      "glpi_plugin_satisfaction_surveys",
      "glpi_plugin_satisfaction_surveyquestions",
      "glpi_plugin_satisfaction_surveyanswers",
      "glpi_plugin_satisfaction_surveyreminders",
      "glpi_plugin_satisfaction_surveytranslations",
      "glpi_plugin_satisfaction_reminders"
   ];

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   $tables_glpi = ["glpi_logs"];

   foreach ($tables_glpi as $table_glpi) {
      $DB->query("DELETE FROM `$table_glpi`
               WHERE `itemtype` = 'PluginSatisfactionSurvey';");
   }

   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginSatisfactionProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(['name' => $right['field']]);
   }
   PluginSatisfactionProfile::removeRightsFromSession();

   PluginSatisfactionMenu::removeRightsFromSession();

   PluginSatisfactionNotificationTargetTicket::uninstall();

   CronTask::Register(PluginSatisfactionReminder::class, PluginSatisfactionReminder::CRON_TASK_NAME, DAY_TIMESTAMP);

   return true;
}
