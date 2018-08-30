<?php

/**
 * @return bool
 */
function plugin_satisfaction_install() {
   global $DB;

   include_once(GLPI_ROOT . "/plugins/satisfaction/inc/profile.class.php");

   if (!$DB->tableExists("glpi_plugin_satisfaction_surveys")) {
      $DB->runFile(GLPI_ROOT . "/plugins/satisfaction/install/sql/empty-1.2.2.sql");
   } else {
      if (!$DB->fieldExists("glpi_plugin_satisfaction_surveyquestions", "type")) {
         $DB->runFile(GLPI_ROOT . "/plugins/satisfaction/install/sql/update-1.1.0.sql");
      }
      //version 1.2.1
      if (!$DB->fieldExists("glpi_plugin_satisfaction_surveyquestions", "default_value")) {
         $DB->runFile(GLPI_ROOT . "/plugins/satisfaction/install/sql/update-1.2.2.sql");
      }
   }

   PluginSatisfactionProfile::initProfile();
   PluginSatisfactionProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

/**
 * @return bool
 */
function plugin_satisfaction_uninstall() {
   global $DB;

   include_once(GLPI_ROOT . "/plugins/satisfaction/inc/profile.class.php");
   include_once(GLPI_ROOT . "/plugins/satisfaction/inc/menu.class.php");

   $tables = ["glpi_plugin_satisfaction_surveys",
                   "glpi_plugin_satisfaction_surveyquestions",
                   "glpi_plugin_satisfaction_surveyanswers"];

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

   return true;
}