<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * Class PluginSatisfactionSurvey
 *
 * Used to store reminders to send automatically
 */
class PluginSatisfactionReminder extends CommonDBTM {

   static $rightname = "plugin_satisfaction";
   public $dohistory = true;

   public static $itemtype = TicketSatisfaction::class;
   public static $items_id = 'ticketsatisfactions_id';

   const CRON_TASK_NAME = 'SatisfactionReminder';


   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {
      return _n('Satisfaction reminder', 'Satisfaction reminders', $nb, 'satisfaction');
   }

////// CRON FUNCTIONS ///////
   /**
    * @param $name
    *
    * @return array
    */
   static function cronInfo($name) {

      switch ($name) {
         case self::CRON_TASK_NAME:
            return ['description' => __('Send automaticaly survey reminders and delete old', 'resources')];   // Optional
            break;
      }
      return [];
   }

   /**
    * Cron action
    *
    * @param  $task for log
    * @global $CFG_GLPI
    *
    * @global $DB
    */
   static function cronSatisfactionReminder($task = NULL) {

      $CronTask = new CronTask();
      if ($CronTask->getFromDBbyName(PluginSatisfactionReminder::class, PluginSatisfactionReminder::CRON_TASK_NAME)) {
         if ($CronTask->fields["state"] == CronTask::STATE_DISABLE) {
            return 0;
         }
      } else {
         return 0;
      }

      // Delete reminders of answered survey
      self::deleteObsoleteReminders();

      // Send reminder survey when date == today
      self::sendRemindersNotifications();
   }

   static function deleteObsoleteReminders(){
      global $DB;

      $selectQuery = "SELECT re.id";
      $selectQuery.= " FROM ".self::getTable() . " as re";
      $selectQuery.= " INNER JOIN ".TicketSatisfaction::getTable() . " as sa";
      $selectQuery.= " ON sa.id = re.".self::$items_id;
      $selectQuery.= " WHERE sa.date_answered IS NOT NULL";

      $deleteQuery = "DELETE FROM ".self::getTable();
      $deleteQuery.= " WHERE id = ";

      $result = $DB->query($selectQuery);

      if ($DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $DB->query($deleteQuery.$data['id']);
         }
      }
   }

   static function sendRemindersNotifications(){
      global $DB;

      $selectQuery = "SELECT sa.*";
      $selectQuery.= " FROM ".self::getTable() . " as re";
      $selectQuery.= " INNER JOIN ".TicketSatisfaction::getTable() . " as sa";
      $selectQuery.= " ON sa.id = re.".self::$items_id;
      $selectQuery.= " WHERE sa.date_answered IS NULL";
      $selectQuery.= " AND DATEDIFF(re.date, CURDATE()) < 0";

      $result = $DB->query($selectQuery);

      if ($DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {

            $t = new Ticket();
            $t->getFromDB($data['tickets_id']);

            PluginSatisfactionNotificationTargetTicket::sendReminder($t);
         }
      }

   }
}