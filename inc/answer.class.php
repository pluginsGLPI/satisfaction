<?php

class PluginSatisfactionAnswer extends CommonDBChild {
   // From CommonDBChild
   public $itemtype  = 'PluginSatisfactionSurvey';
   public $items_id  = 'plugin_satisfaction_surveys_id';
   public $dohistory = true;

   function canCreate() {
      return true;
   }

   function canView() {
      return true;
   }

   static function getTypeName() {
      global $LANG;
      return $LANG['plugin_satisfaction']['answer']['name'];
   }

   static function install(Migration $migration) {
      global $DB;

      //create table
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE `$table` (
                           `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
                           `answer` TEXT collate utf8_unicode_ci default NULL,
                           `plugin_satisfaction_surveys_id` INT( 11 ) NOT NULL,
                           PRIMARY KEY ( `id` )
                           ) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());
      }

      return true;
   }

   static function uninstall() {
      global $DB;
      
      $query = "DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`";
      return $DB->query($query) or die($DB->error());
   }
}