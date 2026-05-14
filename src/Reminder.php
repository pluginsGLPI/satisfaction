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

namespace GlpiPlugin\Satisfaction;

use CommonDBTM;
use CronTask;
use Entity;
use Glpi\DBAL\QueryExpression;
use Ticket;
use TicketSatisfaction;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Class Reminder
 *
 * Used to store reminders to send automatically
 */
class Reminder extends CommonDBTM
{

    public static $rightname = "plugin_satisfaction";
    public $dohistory = true;

    public static $itemtype = TicketSatisfaction::class;
    public static $items_id = 'ticketsatisfactions_id';

    public const CRON_TASK_NAME = 'SatisfactionReminder';


   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @return string
    **/
    public static function getTypeName($nb = 0)
    {
        return _n('Satisfaction reminder', 'Satisfaction reminders', $nb, 'satisfaction');
    }

   ////// CRON FUNCTIONS ///////

   /**
    * @param $name
    *
    * @return array
    */
    public static function cronInfo($name)
    {

        switch ($name) {
            case self::CRON_TASK_NAME:
                return ['description' => __('Send automaticaly survey reminders', 'satisfaction')];   // Optional
            break;
        }
        return [];
    }

    public static function deleteItem(Ticket $ticket)
    {
        $reminder = new self;
        if ($reminder->getFromDBByCrit(['tickets_id' => $ticket->fields['id']])) {
            $reminder->delete(['id' => $reminder->fields["id"]]);
        }
    }

   /**
    * Cron action
    *
    * @param  $task for log
    *
    * @global $CFG_GLPI
    *
    * @global $DB
    */
    public static function cronSatisfactionReminder($task = null)
    {

        $CronTask = new CronTask();
        if ($CronTask->getFromDBbyName(Reminder::class, Reminder::CRON_TASK_NAME)) {
            if ($CronTask->fields["state"] == CronTask::STATE_DISABLE) {
                return 0;
            }
        } else {
            return 0;
        }

        self::sendReminders();
    }

   /**
    * @param $date_begin
    * @param $date_answered
    * @param $entities_id
    *
    * @return array
    * @throws \GlpitestSQLError
    */
    public static function getTicketSatisfaction($date_begin, $date_answered, $entities_id)
    {
        global $DB;

        $where = [
            't.entities_id' => (int) $entities_id,
            ['ts.date_begin' => ['>', new QueryExpression("DATE(" . $DB->quoteValue($date_begin) . ")")]],
        ];

        if ($date_answered === null) {
            $where['ts.date_answered'] = null;
        } else {
            $where[] = ['ts.date_answered' => new QueryExpression("DATE(" . $DB->quoteValue($date_answered) . ")")];
        }

        $ticketSatisfactions = [];
        foreach ($DB->request([
            'SELECT'     => 'ts.*',
            'FROM'       => TicketSatisfaction::getTable() . ' AS ts',
            'INNER JOIN' => [
                Ticket::getTable() . ' AS t' => [
                    'FKEY' => ['ts' => 'tickets_id', 't' => 'id'],
                ],
            ],
            'WHERE' => $where,
        ]) as $data) {
            $ticketSatisfactions[] = $data;
        }
        return $ticketSatisfactions;
    }

    public static function sendReminders()
    {

        $entityDBTM = new Entity();

        $Survey         = new Survey();
        $SurveyReminder = new SurveyReminder();
        $Reminder       = new Reminder();

        $surveys = $Survey->find(['is_active' => true]);

        foreach ($surveys as $survey) {
           // Entity
            $entityDBTM->getFromDB($survey['entities_id']);

           // Don't get tickets satisfaction with date older than max_close_date
 //                           $max_close_date = date('Y-m-d', strtotime($entityDBTM->getField('max_closedate')));
            $nb_days = $survey['reminders_days'];
            $dt             = date("Y-m-d");
            $max_close_date = date('Y-m-d', strtotime("$dt - ".$nb_days." day"));

           // Ticket Satisfaction
            $ticketSatisfactions = self::getTicketSatisfaction($max_close_date, null, $survey['entities_id']);


            foreach ($ticketSatisfactions as $k => $ticketSatisfaction) {
                // Survey Reminders
                $surveyReminderCrit = [
                 'plugin_satisfaction_surveys_id' => $survey['id'],
                 'is_active'                      => 1,
                ];
                $surveyReminders    = $SurveyReminder->find($surveyReminderCrit);

                $potentialReminderToSendDates = [];

                // Calculate the next date of next reminders
                foreach ($surveyReminders as $surveyReminder) {
                    $reminders = null;
                    $reminders = $Reminder->find([
                        'tickets_id' => $ticketSatisfaction['tickets_id'],
                                                                   'type'       => $surveyReminder['id']]);

                    if (count($reminders)) {
                         continue;
                    } else {
                        $lastSurveySendDate = date('Y-m-d', strtotime($ticketSatisfaction['date_begin']));

                      // Date when glpi satisfaction was sended for the first time
                        $reminders_to_send = $Reminder->find([
                            'tickets_id' => $ticketSatisfaction['tickets_id']]);
                        if (count($reminders_to_send)) {
                              $reminder           = array_pop($reminders_to_send);
                              $lastSurveySendDate = date('Y-m-d', strtotime($reminder['date']));
                        }

                        $date = null;

                        switch ($surveyReminder[SurveyReminder::COLUMN_DURATION_TYPE]) {
                            case SurveyReminder::DURATION_DAY:
                                $add  = " +" . $surveyReminder[
                                    SurveyReminder::COLUMN_DURATION] . " day";
                                $date = strtotime(date("Y-m-d", strtotime($lastSurveySendDate)) . $add);
                                $date = date('Y-m-d', $date);
                                break;

                            case SurveyReminder::DURATION_MONTH:
                                 $add  = " +" . $surveyReminder[
                                     SurveyReminder::COLUMN_DURATION] . " month";
                                 $date = strtotime(date("Y-m-d", strtotime($lastSurveySendDate)) . $add);
                                 $date = date('Y-m-d', $date);
                                break;
                            default:
                                  $date = null;
                        }

                        if (!is_null($date)) {
                            $potentialReminderToSendDates[] = ["tickets_id" => $ticketSatisfaction['tickets_id'],
                                                        "type"       => $surveyReminder['id'],
                                                        "date"       => $date];
                        }
                    }
                }
                // Order dates
                usort($potentialReminderToSendDates, function($a, $b) {
                    strtotime($a["date"]) - strtotime($b["date"]);
                });
                $dateNow = date("Y-m-d");

                if (isset($potentialReminderToSendDates[0])) {
                    $potentialTimestamp = strtotime($potentialReminderToSendDates[0]['date']);
                    $nowTimestamp       = strtotime($dateNow);
                   //
                    if ($potentialTimestamp <= $nowTimestamp) {
                      // Send notification
                        NotificationTargetTicket::sendReminder($ticketSatisfaction['tickets_id']);
                        $self = new self();
                        $self->add([
                                'type'       => $potentialReminderToSendDates[0]['type'],
                                'tickets_id' => $ticketSatisfaction['tickets_id'],
                                'date'       => $dateNow
                             ]);
                    }
                }
            }
        }
    }
}
