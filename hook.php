<?php

/**
 * @return bool
 */
function plugin_satisfaction_install() {
   global $DB;

   include_once(GLPI_ROOT . "/plugins/satisfaction/inc/profile.class.php");
   include_once(GLPI_ROOT . "/plugins/satisfaction/inc/notificationtargetticket.class.php");

   if (!$DB->tableExists("glpi_plugin_satisfaction_surveys")) {
      $DB->runFile(GLPI_ROOT . "/plugins/satisfaction/install/sql/empty-1.4.3.sql");

   } else {
      if (!$DB->fieldExists("glpi_plugin_satisfaction_surveyquestions", "type")) {
         $DB->runFile(GLPI_ROOT . "/plugins/satisfaction/install/sql/update-1.1.0.sql");
      }
      //version 1.2.1
      if (!$DB->fieldExists("glpi_plugin_satisfaction_surveyquestions", "default_value")) {
         $DB->runFile(GLPI_ROOT . "/plugins/satisfaction/install/sql/update-1.2.2.sql");
      }
      //version 1.4.1
      if (!$DB->tableExists("glpi_plugin_satisfaction_surveytranslations")) {
         $DB->runFile(GLPI_ROOT . "/plugins/satisfaction/install/sql/update-1.4.1.sql");
      }

      //version 1.4.3
      if (!$DB->tableExists("glpi_plugin_satisfaction_surveyreminders")) {
         $DB->runFile(GLPI_ROOT . "/plugins/satisfaction/install/sql/update-1.4.3.sql");
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

   include_once(GLPI_ROOT . "/plugins/satisfaction/inc/profile.class.php");
   include_once(GLPI_ROOT . "/plugins/satisfaction/inc/menu.class.php");
   include_once(GLPI_ROOT . "/plugins/satisfaction/inc/notificationtargetticket.class.php");

   $tables = [
      "glpi_plugin_satisfaction_surveys",
      "glpi_plugin_satisfaction_surveyquestions",
      "glpi_plugin_satisfaction_surveyanswers",
      "glpi_plugin_satisfaction_surveyreminders",
      "glpi_plugin_satisfaction_surveytranslations"
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

function plugin_satisfaction_get_events(NotificationTargetTicket $target) {
   $target->events['survey_reminder'] = __("Ticket Satisfaction Reminder", 'satisfaction');
}