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

use Ajax;
use CommonDBChild;
use CommonGLPI;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Glpi\Exception\Http\NotFoundHttpException;
use Html;
use Log;
use Session;
use Toolbox;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}


/**
 * SurveyTranslation Class
 **/
class SurveyTranslation extends CommonDBChild
{

    public static $itemtype = 'itemtype';
    public static $items_id = 'items_id';
    public $dohistory       = true;
    public static $rightname       = 'plugin_satisfaction';

    public static function getTypeName($nb = 0)
    {
        return _n('Translation', 'Translations', $nb);
    }

   /**
    * @see CommonGLPI::getTabNameForItem()
    **/
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (self::canBeTranslated($item)) {
            $nb = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
                $nb = self::getNumberOfTranslationsForItem($item);
            }
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
        }
        return '';
    }

    public static function getIcon()
    {
        return "ti ti-language";
    }

   /**
    * Get the standard massive actions which are forbidden
    *
    * @since version 0.84
    *
    * @return array array of massive actions
    **/
    public function getForbiddenStandardMassiveAction()
    {

        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }

   /**
    * Check if an item can be translated
    * It be translated if translation if globally on and item is an instance of CommonDropdown
    * or CommonTreeDropdown and if translation is enabled for this class
    *
    * @param $item
    *
    * @return true if item can be translated, false otherwise
    **/
    public static function canBeTranslated(CommonGLPI $item)
    {
        return $item instanceof Survey && $item->maybeTranslated();
    }

   /**
    * Return the number of translations for an item
    *
    * @param
    *
    * @return the number of translations for this item
    **/
    public static function getNumberOfTranslationsForItem($item)
    {
        return SurveyTranslationDAO::countSurveyTranslationByCrit([
            "plugin_satisfaction_surveys_id" => $item->getID()]);
    }

   /**
    * @param $item            CommonGLPI object
    * @param $tabnum          (default 1)
    * @param $withtemplate    (default 0)
    **/
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if (SurveyTranslation::canBeTranslated($item)) {
            SurveyTranslation::showTranslations($item);
        }
        return true;
    }

   /**
    * Display all translated field for a dropdown
    *
    * @param $item a Dropdown item
    *
    * @return true;
    **/
    public static function showTranslations(Survey $item)
    {
        global $CFG_GLPI;

       // Get all translation from database
        $items = SurveyTranslationDAO::getSurveyTranslationByCrit([
            "plugin_satisfaction_surveys_id" => $item->getID()]);

        $rand    = mt_rand();
        $canedit = $item->can($item->getID(), UPDATE);
        $target  = PLUGINSATISFACTION_WEBDIR . "/ajax/surveytranslation.form.php";

        $add_script = '';
        if ($canedit) {
            ob_start();
            echo "<script type='text/javascript'>\n";
            echo "function addTranslation" . $item->getID() . "$rand() {\n";
            $params = [
                'id'        => -1,
                'survey_id' => $item->getID(),
                'action'    => 'GET',
            ];
            Ajax::updateItemJsCode(
                "viewtranslation" . $item->getID() . "$rand",
                $target,
                $params
            );
            echo "};";
            echo "</script>\n";
            $add_script = ob_get_clean();
        }

        $rows      = [];
        $ma_top    = '';
        $ma_bottom = '';
        $checkall  = '';
        if (count($items)) {
            if ($canedit) {
                ob_start();
                Html::openMassiveActionsForm('mass_satisfaction' . $rand);
                $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass_satisfaction' . $rand];
                Html::showMassiveActions($massiveactionparams);
                $ma_top   = ob_get_clean();
                $checkall = Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            }

            foreach ($items as $data) {
                $edit_script = '';
                if ($canedit) {
                    ob_start();
                    echo "<script type='text/javascript'>\n";
                    echo "function viewEditTranslation" . $data['id'] . "$rand() {\n";
                    $params = [
                        'id'        => $data["id"],
                        'survey_id' => $item->getID(),
                        'action'    => 'GET',
                    ];
                    Ajax::updateItemJsCode(
                        "viewtranslation" . $item->getID() . "$rand",
                        $target,
                        $params
                    );
                    echo "};";
                    echo "</script>\n";
                    $edit_script = ob_get_clean();
                }

                $checkbox = '';
                if ($canedit) {
                    ob_start();
                    Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
                    $checkbox = ob_get_clean();
                }

                $surveyQuestion = new SurveyQuestion();
                $surveyQuestion->getFromDB($data['glpi_plugin_satisfaction_surveyquestions_id']);

                $rows[] = [
                    'edit'        => $canedit,
                    'onclick'     => "viewEditTranslation" . $data['id'] . "$rand();",
                    'checkbox'    => $checkbox,
                    'edit_script' => $edit_script,
                    'language'    => Dropdown::getLanguageName($data['language']),
                    'question'    => $surveyQuestion->getName(),
                    'value'       => $data['value'],
                ];
            }

            if ($canedit) {
                ob_start();
                $massiveactionparams['ontop'] = false;
                Html::showMassiveActions($massiveactionparams);
                Html::closeForm();
                $ma_bottom = ob_get_clean();
            }
        }

        TemplateRenderer::getInstance()->display('@satisfaction/surveytranslation_list.html.twig', [
            'can_edit'     => $canedit,
            'container_id' => "viewtranslation" . $item->getID() . $rand,
            'add_script'   => $add_script,
            'add_onclick'  => "addTranslation" . $item->getID() . "$rand();",
            'checkall'     => $checkall,
            'ma_top'       => $ma_top,
            'ma_bottom'    => $ma_bottom,
            'rows'         => $rows,
        ]);

        return true;
    }

    public function showSurveyTranslationForm($options)
    {
        global $CFG_GLPI;
        $surveyId = Toolbox::cleanInteger($options['survey_id']);

        $item = new Survey();
        $item->getFromDB($surveyId);

        if ($options['id'] > 0) {
            $item->check($surveyId, READ);
        } else {
           // Create item
            $item->check(-1, CREATE);
        }

        $data = [
            'target'    => PLUGINSATISFACTION_WEBDIR . "/front/surveytranslation.form.php",
            'survey_id' => $surveyId,
            'is_edit'   => ($options['id'] > 0),
        ];

        if ($options['id'] > 0) {
            $surveyTranslationData = SurveyTranslationDAO::getSurveyTranslationByID($options['id']);

            // Integrity/anti-IDOR: the requested translation must belong to the survey
            // whose READ right/entity has already been validated above.
            if (
                $surveyTranslationData === null
                || (int) $surveyTranslationData['plugin_satisfaction_surveys_id'] !== $surveyId
            ) {
                throw new NotFoundHttpException();
            }

            $surveyQuestion = new SurveyQuestion();
            $surveyQuestion->getFromDB($surveyTranslationData['glpi_plugin_satisfaction_surveyquestions_id']);

            $data['language_value'] = $surveyTranslationData['language'];
            $data['translation_id'] = $options['id'];
            $data['question_id']    = $surveyQuestion->getID();
            $data['language_name']  = Dropdown::getLanguageName($surveyTranslationData['language']);
            $data['question_name']  = $surveyQuestion->getName();
            $data['value']          = $surveyTranslationData['value'];
        } else {
            ob_start();
            $rand = Dropdown::showLanguages(
                "language",
                ['display_none' => true, 'value' => $_SESSION['glpilanguage']]
            );
            $data['language_dropdown'] = ob_get_clean();

            $params = [
                'language' => '__VALUE__',
                'itemtype' => get_class($item),
                'items_id' => $item->getID(),
            ];

            ob_start();
            Ajax::updateItemOnSelectEvent(
                "dropdown_language$rand",
                "span_fields",
                $CFG_GLPI["root_doc"] . "/ajax/updateTranslationFields.php",
                $params
            );
            $data['language_ajax'] = ob_get_clean();

            $data['question_dropdown'] = $this->getQuestionDropdown($surveyId);
        }

        TemplateRenderer::getInstance()->display('@satisfaction/surveytranslation_form.html.twig', $data);
    }

    public function getQuestionDropdown($surveyId)
    {

        $item = new SurveyQuestion();
        $datas = $item->find(['plugin_satisfaction_surveys_id' => $surveyId]);

        $temp = [];
        foreach ($datas as $data) {
            $temp[$data['id']] = $data['name'];
        }

        $params = [
         "name"=> 'question_id',
         "display"=>false,
         "width"=> '200px',
         'display_emptychoice' => true
        ];

        return Dropdown::showFromArray($params['name'], $temp, $params);
    }

    public function newSurveyTranslation($options)
    {
        global $CFG_GLPI;

        // Integrity/anti-IDOR: the question must belong to the survey whose UPDATE
        // right/entity has already been validated by the controller.
        $question = new SurveyQuestion();
        if (
            !$question->getFromDB((int) $options['question_id'])
            || (int) $question->fields['plugin_satisfaction_surveys_id'] !== (int) $options['survey_id']
        ) {
            Session::addMessageAfterRedirect(
                __("Translation creation failed", "satisfaction"),
                true,
                ERROR
            );
            return;
        }

        $crit = [
         'plugin_satisfaction_surveys_id' => $options['survey_id'],
         'glpi_plugin_satisfaction_surveyquestions_id' => $options['question_id'],
         'language' => $options['language']
        ];

       // Translation already exist
        if (SurveyTranslationDAO::countSurveyTranslationByCrit($crit)) {
            Session::addMessageAfterRedirect(
                sprintf(__(
                    "An %s translation for this Question already exist.",
                    "satisfaction"
                ), $CFG_GLPI['languages'][$options["language"]][0]),
                true,
                WARNING
            );
        } else {  // Translation ready to insert
            $newInsertId = SurveyTranslationDAO::newSurveyTranslation(
                $options['survey_id'],
                $options['question_id'],
                $options['language'],
                $options['value']
            );
            if ($newInsertId != null) {
                Session::addMessageAfterRedirect(__("Translation successfully created.", "satisfaction"), true, INFO);

                if ($this->dohistory) {
                    $changes = [
                     $newInsertId,
                     '',
                     $options['value']
                    ];
                    Log::history(
                        $options['survey_id'],
                        Survey::class,
                        $changes,
                        $this->getType(),
                        static::$log_history_add
                    );
                }
            } else {
                Session::addMessageAfterRedirect(__("Translation creation failed", "satisfaction"), true, ERROR);
            }
        }
    }

    public function editSurveyTranslation($options)
    {
        global $CFG_GLPI;
        $crit = [
         'id' => $options['id']
        ];

        // Translation doesn't exist
        if (!SurveyTranslationDAO::countSurveyTranslationByCrit($crit)) {
            Session::addMessageAfterRedirect(
                __("The translation you want to edit does not exist.", "satisfaction"),
                true,
                WARNING
            );
        }
        // Translation ready to update
        else {
            $surveyTranslationData = SurveyTranslationDAO::getSurveyTranslationByID($options['id']);

            // Integrity/anti-IDOR: the targeted translation must belong to the survey
            // whose UPDATE right/entity has already been validated by the controller.
            if ((int) $surveyTranslationData['plugin_satisfaction_surveys_id'] !== (int) $options['survey_id']) {
                Session::addMessageAfterRedirect(
                    __("The translation you want to edit does not exist.", "satisfaction"),
                    true,
                    WARNING
                );
                return;
            }

            SurveyTranslationDAO::editSurveyTranslation($options['id'], $options['value']);

            Session::addMessageAfterRedirect(__("Translation successfully edited.", "satisfaction"), true, INFO);

            if ($this->dohistory) {
                $changes = [
                $options['id'],
                $surveyTranslationData['value'],
                $options['value']
                ];
                Log::history(
                    $options['survey_id'],
                    Survey::class,
                    $changes,
                    $this->getType(),
                    static::$log_history_update
                );
            }
        }
    }

    public static function hasTranslation($surveyId, $questionId)
    {
        return SurveyTranslationDAO::countSurveyTranslationByCrit([
         'plugin_satisfaction_surveys_id' => $surveyId,
         'glpi_plugin_satisfaction_surveyquestions_id' => $questionId,
         'language' => $_SESSION['glpilanguage']
        ]);
    }

    public static function getTranslation($surveyId, $questionId)
    {

        $crit = [
         'plugin_satisfaction_surveys_id' => $surveyId,
         'glpi_plugin_satisfaction_surveyquestions_id' => $questionId,
         'language' => $_SESSION['glpilanguage']
        ];

        $translationList = SurveyTranslationDAO::getSurveyTranslationByCrit($crit);
        $translation = array_pop($translationList);

        return $translation['value'];
    }
}
