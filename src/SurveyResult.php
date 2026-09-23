<?php

/**
 * -------------------------------------------------------------------------
 * satisfaction plugin for GLPI
 * Copyright (C) 2018-2026 by the satisfaction Development Team.
 *
 * https://github.com/pluginsGLPI/satisfaction
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of satisfaction.
 *
 * satisfaction is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * satisfaction is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with satisfaction. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
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

        // A recursive survey collects answers from every entity below it: only
        // list the answers whose ticket is in the user's entity perimeter.
        $dbu        = new DbUtils();
        $answer_ids = [
            'FROM'       => 'glpi_plugin_satisfaction_surveyanswers',
            'INNER JOIN' => [
                'glpi_ticketsatisfactions' => [
                    'FKEY' => [
                        'glpi_ticketsatisfactions'               => 'id',
                        'glpi_plugin_satisfaction_surveyanswers' => 'ticketsatisfactions_id',
                    ],
                ],
                'glpi_tickets' => [
                    'FKEY' => [
                        'glpi_tickets'             => 'id',
                        'glpi_ticketsatisfactions' => 'tickets_id',
                    ],
                ],
            ],
            'WHERE'      => [
                'glpi_plugin_satisfaction_surveyanswers.plugin_satisfaction_surveys_id' => $item->getID(),
            ] + $dbu->getEntitiesRestrictCriteria('glpi_tickets', '', '', true),
        ];

        // Total Number of events
        $total_number = (int) $DB->request(
            ['COUNT' => 'cpt'] + $answer_ids,
        )->current()['cpt'];

        $questions = [];
        $rows      = [];

        if ($total_number > 0) {
            // Display the pager
            Html::printAjaxPager(self::getTypeName($total_number), $start, $total_number, '', true);

            $squestion_obj    = new SurveyQuestion();
            $survey_questions = $squestion_obj->find([
                SurveyQuestion::$items_id => $item->getID()]);
            foreach ($survey_questions as $question) {
                $questions[] = $question['name'];
            }

            $obj_survey_answer = new SurveyAnswer();

            $query          = [
                'SELECT' => 'glpi_plugin_satisfaction_surveyanswers.*',
                'ORDER'  => 'glpi_plugin_satisfaction_surveyanswers.id DESC',
            ] + $answer_ids;
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
                // Render in the header order, only for questions of this survey:
                // orphan keys are ignored and missing answers keep their column.
                foreach ($survey_questions as $questions_id => $question) {
                    $answers_rendered[] = isset($answers[$questions_id])
                        ? $obj_survey_answer->getAnswer($question, $answers[$questions_id])
                        : '';
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
