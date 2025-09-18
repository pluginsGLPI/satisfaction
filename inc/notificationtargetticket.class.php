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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginSatisfactionNotificationTargetTicket
 */
class PluginSatisfactionNotificationTargetTicket extends NotificationTarget
{

    public function getEvents()
    {
        return ["survey_reminder" => __('Survey Reminder', 'satisfaction')];
    }

    public static function addEvents(NotificationTargetTicket $target)
    {

        $target->events['survey_reminder']
         = __('Survey Reminder', 'satisfaction');
    }

    public function addDataForTemplate($event, $options = [])
    {
    }

    public function addSpecificTargets($data, $options)
    {
    }

    public static function sendReminder($tickets_id)
    {

        $ticketDBTM = new Ticket();
        if ($ticketDBTM->getFromDB($tickets_id)) {
            NotificationEvent::raiseEvent("survey_reminder", $ticketDBTM);
        }
    }

    public function getTags()
    {
        $notification_target_ticket = new NotificationTargetTicket();
        $notification_target_ticket->getTags();
        $this->tag_descriptions = $notification_target_ticket->tag_descriptions;
    }

    public function getDatasForObject(CommonDBTM $item, array $options, $simple = false)
    {
        $notification_target_ticket = new NotificationTargetTicket();
        $data = $notification_target_ticket->getDataForObject($item, $options, $simple);
        return $data;
    }

    public static function install()
    {

        $notificationTemplateDBTM = new NotificationTemplate();
        if (!$notificationTemplateDBTM->getFromDBByCrit(['name' => 'Ticket Satisfaction Reminder'])) {
            $notificationTemplateId = $notificationTemplateDBTM->add([
            'name'     => "Ticket Satisfaction Reminder",
            'itemtype' => 'Ticket',
            'comment'  => "Created by the plugin satisfaction"
            ]);
        }

        $notificationDBTM = new Notification();
        if (!$notificationDBTM->getFromDBByCrit(['name' => 'Ticket Satisfaction Reminder'])) {
            $notifications_id   = $notificationDBTM->add([
            'name'                     => "Ticket Satisfaction Reminder",
            'entities_id'              => 0,
            'is_recursive'             => 1,
            'is_active'                => 1,
            'itemtype'                 => 'Ticket',
            'event'                    => "survey_reminder",
            'comment'                  => "Created by the plugin Satisfaction"
            ]);
        }
    }

    public static function uninstall()
    {
        global $DB;

        $notificationDBTM = new Notification();
        $notificationDBTM->getFromDBByCrit(['event'=>'survey-reminder']);

        $notification_notificationTemplate = new Notification_NotificationTemplate();

        if ($notification_notificationTemplate->find(['notifications_id' => $notificationDBTM->getID()])) {
            $DB->delete("glpi_notificationtemplatetranslations", [
                'notificationtemplates_id' => $notification_notificationTemplate->getField(
                    'notificationtemplates_id'
                )]);
            $DB->delete("glpi_notificationtargets", ['notifications_id' => $notificationDBTM->getID()]);
            $DB->delete("glpi_notifications_notificationtemplates", [
                'id' => $notification_notificationTemplate->getField('notificationtemplates_id')]);
            $DB->delete("glpi_notificationtemplates", [
                'id' => $notification_notificationTemplate->getField('notificationtemplates_id')]);
            $DB->delete("glpi_notifications", ['id' => $notificationDBTM->getID()]);
        }
    }
}
