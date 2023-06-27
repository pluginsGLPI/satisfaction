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
 * Class PluginSatisfactionSurveyResult
 */
class PluginSatisfactionSurveyResult extends CommonDBChild {

   static $rightname = "plugin_satisfaction";
   public $dohistory = true;

   // From CommonDBChild
   public static $itemtype = 'PluginSatisfactionSurvey';
   public static $items_id = 'plugin_satisfaction_surveys_id';

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {
      return _n('Result of the survey', 'Results of the survey', $nb, 'satisfaction');
   }


   /**
    * Get Tab Name used for itemtype
    *
    * NB : Only called for existing object
    *      Must check right on what will be displayed + template
    *
    * @since version 0.83
    *
    * @param $item                     CommonDBTM object for which the tab need to be displayed
    * @param $withtemplate    boolean  is a template object ? (default 0)
    *
    * @return string tab name
    **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      // can exists for template
      if ($item->getType() == 'PluginSatisfactionSurvey') {
         return __('Result', 'satisfaction');
      }

      return '';
   }


   /**
    * show Tab content
    *
    * @since version 0.83
    *
    * @param $item                  CommonGLPI object for which the tab need to be displayed
    * @param $tabnum       integer  tab number (default 1)
    * @param $withtemplate boolean  is a template object ? (default 0)
    *
    * @return true
    **/
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'PluginSatisfactionSurvey') {
         self::showResult($item);

      }
      return true;
   }

   static function showResult(PluginSatisfactionSurvey $item) {
      global $DB;

      if (isset($_GET["start"])) {
         $start = intval($_GET["start"]);
      } else {
         $start = 0;
      }

      // Total Number of events
      $total_number = countElementsInTable("glpi_plugin_satisfaction_surveyanswers",
                                           ['plugin_satisfaction_surveys_id' => $item->getID()]);

      // No Events in database
      if ($total_number == 0) {
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><th>" . __('No result of the survey', 'satisfaction') . "</th></trclass>";
         echo "</table>";
         echo "</div><br>";
         return;
      }

      // Display the pager
      Html::printAjaxPager(self::getTypeName(1), $start, $total_number, '', true);


      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixehov'>";
      if ($total_number > 0) {
         echo "<tr class='tab_bg_1'>";
         echo "<th>" . __('ID') . "</th>";
         echo "<th>" . __('Ticket') . "</th>";

         $squestion_obj = new PluginSatisfactionSurveyQuestion;
         foreach ($squestion_obj->find([PluginSatisfactionSurveyQuestion::$items_id => $item->getID()]) as $question) {
            echo "<th>" . nl2br($question['name']) . "</th>";
         }
         echo "<th>".__('Satisfaction with the resolution of the ticket')."</th>";
         echo "<th>".__('Comments')."</th>";
         echo "<th>".__('Response date to the satisfaction survey')."</th>";
         echo "</tr>";

         $dbu               = new DbUtils();
         $obj_survey_answer = new PluginSatisfactionSurveyAnswer();

         $query          = [
            'FROM'  => 'glpi_plugin_satisfaction_surveyanswers',
            'WHERE' => [
               'plugin_satisfaction_surveys_id' => $item->getID(),
            ],
            'ORDER' => 'id DESC'
         ];
         $query['START'] = (int)$start;
         $query['LIMIT'] = (int)$_SESSION['glpilist_limit'];

         $iterator = $DB->request($query);
         foreach ($iterator as $data) {
            echo "<tr class='tab_bg_1'>";

            $ticket_satisfaction = new TicketSatisfaction();
            $ticket_satisfaction->getFromDBByRequest(['WHERE' =>
                                                         ["id" => $data['ticketsatisfactions_id']]]);

            $ticket = new Ticket();
            $ticket->getFromDB($ticket_satisfaction->getField('tickets_id'));
            echo "<td>" . $ticket_satisfaction->getField('tickets_id') . "</td>";
            echo "<td>" . $ticket->getLink() . "</td>";

            $answers = $dbu->importArrayFromDB($data['answer']);
            foreach ($answers as $questions_id => $answer) {
               echo "<td>";
               $squestion_obj->getFromDB($questions_id);
               echo $obj_survey_answer->getAnswer($squestion_obj->fields, $answer);
               echo "</td>";
            }
            echo "<td>" . $ticket_satisfaction->getField('satisfaction') . "</td>";
            echo "<td>" . $ticket_satisfaction->getField('comment') . "</td>";
            echo "<td>" . Html::convDateTime($ticket_satisfaction->getField('date_answered')). "</td>";
            echo "</tr>";
         }

      }

      echo "</table>";
      echo "</div>";
   }
}
