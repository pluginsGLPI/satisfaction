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
                           `comment` TEXT collate utf8_unicode_ci default NULL,
                           `plugin_satisfaction_surveys_id` INT( 11 ) NOT NULL,
                           `tickets_id` INT( 11 ) NOT NULL,
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

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      // can exists for template
      if ($item->getType() == 'Ticket') {
            return self::getTypeName();
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      if ($item->getType() == 'Ticket') {
         self::showForTicket($item, $withtemplate);
      }
      return true;
   }

   static function showForTicket(Ticket $item, $withtemplate = '') {
      echo "answer";
   }
}