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

   public function deleteItem(Ticket $ticket){
      $reminder = new Self;
      if($reminder->getFromDBByCrit(['tickets_id'=>$ticket->fields['id']])){
         $reminder->delete(['id' => $reminder->fields["id"]]);
      }
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

      self::sendReminders();
   }

   static function getTicketSatisfaction($date_begin, $date_answered, $entities_id){

      $ticketSatisfactions = [];

      global $DB;

      $query =  "SELECT ts.* FROM ".TicketSatisfaction::getTable() . " as ts";
      $query .= " INNER JOIN ".Ticket::getTable() . " as t";
      $query .= " ON ts.tickets_id = t.id";
      $query .= " WHERE t.entities_id = ".$entities_id;
      $query .= " AND ts.date_begin > DATE('".$date_begin."')";
      $query .= " AND ts.date_answered ".(($date_answered == null)? " IS NULL" : " = DATE('".$date_answered."')");

      $result = $DB->query($query);

      if ($DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $ticketSatisfactions[] = $data;
         }
      }
      return $ticketSatisfactions;
   }

   static function sendReminders(){
      //TODO ENTITIES_id
      $entityDBTM = new Entity();

      $pluginSatisfactionSurveyDBTM = new PluginSatisfactionSurvey();
      $pluginSatisfactionSurveyReminderDBTM = new PluginSatisfactionSurveyReminder();
      $pluginSatisfactionReminderDBTM = new PluginSatisfactionReminder();

      $surveys = $pluginSatisfactionSurveyDBTM->find(['is_active' => true]);

      foreach($surveys as $survey){

         // Entity
         $entityDBTM->getFromDB($survey['entities_id']);

         // Don't get tickets satisfaction with date older than max_close_date
         $max_close_date = date('Y-m-d', strtotime($entityDBTM->getField('max_closedate')));

         // Ticket Satisfaction
         $ticketSatisfactions = self::getTicketSatisfaction($max_close_date, null, $survey['entities_id']);

         foreach($ticketSatisfactions as $ticketSatisfaction){

            $reminders = $pluginSatisfactionReminderDBTM->find(['tickets_id' => $ticketSatisfaction['tickets_id']]);

            // Date when glpi satisfaction was sended for the first time
            $lastSurveySendDate = date('Y-m-d', strtotime($ticketSatisfaction['date_begin']));

            $reminder = null;

            // Update lastSurvey with last sended reminder date
            if(count($reminders)){
               $reminder = array_pop($reminders);
               $lastSurveySendDate = date('Y-m-d', strtotime($reminder['date']));
            }

            // Survey Reminders
            $surveyReminderCrit = [
               'plugin_satisfaction_surveys_id' => $survey['id']
            ];
            $surveyReminders = $pluginSatisfactionSurveyReminderDBTM->find($surveyReminderCrit);

            $potentialReminderToSendDates = [];
            $potentialReminderTypes = [];
            $potentialReminderIndexes = [];

            // Calculate the next date of next reminders
            foreach($surveyReminders as $surveyReminder){

               // Don't get the last reminder used
               if(isset($reminder['type']) && $surveyReminder['id'] === $reminder['type']){
                  continue;
               }

               $date = null;

               switch($surveyReminder[PluginSatisfactionSurveyReminder::COLUMN_DURATION_TYPE]){

                  case PluginSatisfactionSurveyReminder::DURATION_DAY:
                     $add = " +".$surveyReminder[PluginSatisfactionSurveyReminder::COLUMN_DURATION]." day";
                     $date = strtotime(date("Y-m-d", strtotime($lastSurveySendDate)).$add);
                     $date = date('Y-m-d', $date);
                     break;

                  case PluginSatisfactionSurveyReminder::DURATION_MONTH:
                     $add = " +".$surveyReminder[PluginSatisfactionSurveyReminder::COLUMN_DURATION]." month";
                     $date = strtotime(date("Y-m-d", strtotime($lastSurveySendDate)).$add);
                     $date = date('Y-m-d', $date);
                     break;
                  default:
                     $date = null;
               }

               if(!is_null($date)){
                  $potentialReminderToSendDates[] = $date;
                  $potentialReminderTypes[$date] = $reminder['type'];
                  $potentialReminderIndexes[$date] = $reminder['id'];
               }
               $types = $surveyReminder['id'];
            }

            if(!function_exists("date_sort")){
               function date_sort($a, $b) {
                  return strtotime($a) - strtotime($b);
               }
            }

            // Order dates
            usort($potentialReminderToSendDates, "date_sort");

            $dateNow = date("Y/m/d");

            foreach($potentialReminderToSendDates as $potentialReminderToSendDate){

               $potentialTimestamp = strtotime($potentialReminderToSendDate);
               $nowTimestamp = strtotime($dateNow);

               if($potentialTimestamp <= $nowTimestamp){

                  // Send notification
                  PluginSatisfactionNotificationTargetTicket::sendReminder($ticketSatisfaction['tickets_id']);

                  // Create new raw in reminder table or update : 1 ROW => 1 ticket
                  if(count($reminders)){
                     self::updateReminderForTicket([
                        'id' => $potentialReminderIndexes['id'],
                        'type' => $potentialReminderTypes[$potentialReminderToSendDate],
                        'tickets_id' => $ticketSatisfaction['tickets_id'],
                        'date' => $dateNow
                     ]);
                  }else{
                     self::addReminderForTicket([
                        'type' => $types,
                        'tickets_id' => $ticketSatisfaction['tickets_id'],
                        'date' => $dateNow
                     ]);
                  }
                  break;
               }
            }
         }
      }
   }

   static function addReminderForTicket($params = []){
      $s = new self();
      $s->add($params);
   }

   static function updateReminderForTicket($params = []){
      $s = new self();
      $s->update($params);
   }

   static function deleteReminderForTicket($tickets_id){
      $s = new self();
      $s->deleteByCriteria(['tickets_id' => $tickets_id]);
   }
}