<?php

/*
 -------------------------------------------------------------------------
 satisfaction plugin for GLPI
 Copyright (C) 2018-2026 by the satisfaction Development Team.

 https://github.com/pluginsGLPI/satisfaction
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

use CommonDBChild;
use CommonGLPI;
use DbUtils;
use Glpi\Application\View\TemplateRenderer;
use Html;
use Ticket;
use TicketSatisfaction;

/**
 * Class SurveyResult
 */
class SurveyResult extends CommonDBChild
{
    public static $rightname = "plugin_satisfaction";
    public $dohistory = true;

    // From CommonDBChild
    public static $itemtype = Survey::class;
    public static $items_id = 'plugin_satisfaction_surveys_id';

    /**
     * Return the localized name of the current Type
     * Should be overloaded in each new class
     *
     * @return string
     **/
    public static function getTypeName($nb = 0)
    {
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
     * @param $item                     CommonGLPI object for which the tab need to be displayed
     * @param $withtemplate    boolean  is a template object ? (default 0)
     *
     * @return string tab name
     **/
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        // can exists for template
        if ($item->getType() == Survey::class) {
            return self::createTabEntry(__('Result', 'satisfaction'));
        }

        return '';
    }

    public static function getIcon()
    {
        return "ti ti-report-analytics";
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
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == Survey::class) {
            self::showResult($item);
        }
        return true;
    }

    public static function showResult(Survey $item)
    {
        global $DB;

        if (isset($_GET["start"])) {
            $start = intval($_GET["start"]);
        } else {
            $start = 0;
        }

        // Total Number of events
        $total_number = countElementsInTable(
            "glpi_plugin_satisfaction_surveyanswers",
            ['plugin_satisfaction_surveys_id' => $item->getID()]
        );

        $questions = [];
        $rows      = [];

        if ($total_number > 0) {
            // Display the pager
            Html::printAjaxPager(self::getTypeName($total_number), $start, $total_number, '', true);

            $squestion_obj = new SurveyQuestion();
            foreach ($squestion_obj->find([
                SurveyQuestion::$items_id => $item->getID()]) as $question) {
                $questions[] = $question['name'];
            }

            $dbu               = new DbUtils();
            $obj_survey_answer = new SurveyAnswer();

            $query          = [
                'FROM'  => 'glpi_plugin_satisfaction_surveyanswers',
                'WHERE' => [
                    'plugin_satisfaction_surveys_id' => $item->getID(),
                ],
                'ORDER' => 'id DESC',
            ];
            $query['START'] = (int) $start;
            $query['LIMIT'] = (int) $_SESSION['glpilist_limit'];

            $iterator = $DB->request($query);
            foreach ($iterator as $data) {
                $ticket_satisfaction = new TicketSatisfaction();
                $ticket_satisfaction->getFromDBByRequest(['WHERE'
                                                         => ["id" => $data['ticketsatisfactions_id']]]);

                $ticket = new Ticket();
                $ticket->getFromDB($ticket_satisfaction->getField('tickets_id'));

                $answers          = $dbu->importArrayFromDB($data['answer']);
                $answers_rendered = [];
                foreach ($answers as $questions_id => $answer) {
                    $squestion_obj->getFromDB($questions_id);
                    $answers_rendered[] = $obj_survey_answer->getAnswer($squestion_obj->fields, $answer);
                }

                $date_answered = "";
                if (!empty($ticket_satisfaction->getField('date_answered'))
                && $ticket_satisfaction->getField('date_answered') != "N/A") {
                    $date_answered = $ticket_satisfaction->getField('date_answered');
                }

                $rows[] = [
                    'tickets_id'   => (int) $ticket_satisfaction->getField('tickets_id'),
                    'ticket_link'  => $ticket->getLink(),
                    'answers'      => $answers_rendered,
                    'satisfaction' => (int) $ticket_satisfaction->getField('satisfaction'),
                    'comment'      => (string) $ticket_satisfaction->getField('comment'),
                    'date'         => Html::convDateTime($date_answered),
                ];
            }
        }

        TemplateRenderer::getInstance()->display('@satisfaction/surveyresult.html.twig', [
            'total_number' => $total_number,
            'questions'    => $questions,
            'rows'         => $rows,
        ]);
    }
}
