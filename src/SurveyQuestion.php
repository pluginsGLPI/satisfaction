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
use DbUtils;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Html;
use Session;
use Toolbox;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class SurveyQuestion
 */
class SurveyQuestion extends CommonDBChild
{
    public static $rightname = "plugin_satisfaction";
    public $dohistory = true;

    // From CommonDBChild
    public static $itemtype = Survey::class;
    public static $items_id = 'plugin_satisfaction_surveys_id';

    public const YESNO    = 'yesno';
    public const TEXTAREA = 'textarea';
    public const NOTE     = 'note';

    /**
     * Return the localized name of the current Type
     * Should be overloaded in each new class
     *
     * @return string
     **/
    public static function getTypeName($nb = 0)
    {
        return _n('Question', 'Questions', $nb, 'satisfaction');
    }

    /**
     * Get Tab Name used for itemtype
     *
     * NB : Only called for existing object
     *      Must check right on what will be displayed + template
     *
     * @since version 0.83
     *
     * @param CommonGLPI $item CommonDBTM object for which the tab need to be displayed
     * @param bool|int              $withtemplate boolean  is a template object ? (default 0)
     *
     * @return string tab name
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        // can exists for template
        if ($item->getType() == Survey::class) {
            if ($_SESSION['glpishow_count_on_tabs']) {
                $dbu = new DbUtils();
                $table = $dbu->getTableForItemType(__CLASS__);
                return self::createTabEntry(
                    self::getTypeName(),
                    $dbu->countElementsInTable(
                        $table,
                        [self::$items_id => $item->getID()]
                    )
                );
            }
            return self::createTabEntry(self::getTypeName());
        }
        return '';
    }
    public static function getIcon()
    {
        return "ti ti-user-question";
    }
    /**
     * show Tab content
     *
     * @since version 0.83
     *
     * @param          $item                  CommonGLPI object for which the tab need to be displayed
     * @param          $tabnum       integer  tab number (default 1)
     * @param bool|int $withtemplate boolean  is a template object ? (default 0)
     *
     * @return true
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == Survey::class) {
            self::showForSurvey($item, $withtemplate);
        }
        return true;
    }


    /**
     * Question display
     *
     * @param Survey $survey
     * @param string                    $withtemplate
     *
     * @return bool
     */
    public static function showForSurvey(Survey $survey, $withtemplate = '')
    {
        global $CFG_GLPI;

        $squestions_obj = new self();
        $sID            = $survey->fields['id'];
        $rand_survey    = mt_rand();

        $canadd   = Session::haveRight(self::$rightname, CREATE);
        $canedit  = Session::haveRight(self::$rightname, UPDATE);
        $canpurge = Session::haveRight(self::$rightname, PURGE);

        //check if answer exists to forbid edition
        $answer        = new SurveyAnswer();
        $found_answer  = $answer->find([self::$items_id => $survey->fields['id']]);
        $answers_exist = count($found_answer) > 0;
        if ($answers_exist) {
            $canedit  = false;
            $canadd   = false;
            $canpurge = false;
        }

        // Add-question ajax action
        $add_script  = '';
        $add_onclick = "viewAddQuestion$sID$rand_survey();";
        if ($canadd) {
            // Emit the JS via Html::scriptBlock() (framework primitive) instead of
            // a raw echoed <script>; interpolated ids are numeric (survey id + rand).
            $params = ['type'          => __CLASS__,
                'parenttype'    => Survey::class,
                self::$items_id => $sID,
                'id'            => -1];
            $js  = "function viewAddQuestion$sID$rand_survey() {\n";
            $js .= Ajax::updateItemJsCode(
                "viewquestion$sID$rand_survey",
                $CFG_GLPI["root_doc"] . "/ajax/viewsubitem.php",
                $params,
                "",
                false
            );
            $js .= "};";
            $add_script = Html::scriptBlock($js);
        }

        // Display existing questions
        $questions = $squestions_obj->find([self::$items_id => $sID], 'id');

        $rows      = [];
        $ma_top    = '';
        $ma_bottom = '';
        $checkall  = '';
        if (count($questions) > 0) {
            $rand = mt_rand();
            if ($canpurge) {
                ob_start();
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass' . __CLASS__ . $rand];
                Html::showMassiveActions($massiveactionparams);
                $ma_top   = ob_get_clean();
                $checkall = Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            }

            foreach ($questions as $question) {
                if ($squestions_obj->getFromDB($question['id'])) {
                    $rows[] = $squestions_obj->getListRow($canedit, $canpurge, $rand_survey);
                }
            }

            if ($canpurge) {
                ob_start();
                $paramsma['ontop'] = false;
                Html::showMassiveActions($paramsma);
                Html::closeForm();
                $ma_bottom = ob_get_clean();
            }
        }

        TemplateRenderer::getInstance()->display('@satisfaction/surveyquestion_list.html.twig', [
            'answers_exist' => $answers_exist,
            'container_id'  => "viewquestion$sID$rand_survey",
            'can_add'       => $canadd,
            'can_purge'     => $canpurge,
            'add_script'    => $add_script,
            'add_onclick'   => $add_onclick,
            'type_name'     => self::getTypeName(2),
            'checkall'      => $checkall,
            'ma_top'        => $ma_top,
            'ma_bottom'     => $ma_bottom,
            'rows'          => $rows,
        ]);
    }

    /**
     * @param       $ID
     * @param array $options
     *
     * @return bool
     */
    public function showForm($ID, $options = [])
    {
        global $CFG_GLPI;

        if (isset($options['parent']) && !empty($options['parent'])) {
            $survey = $options['parent'];
        }

        $surveyquestion = new self();
        if ($ID <= 0) {
            $surveyquestion->getEmpty();
        } else {
            $surveyquestion->getFromDB($ID);
        }

        if (!$surveyquestion->canView()) {
            return false;
        }

        $array = self::getQuestionTypeList();
        ob_start();
        Dropdown::showFromArray('type', $array, ['value'     => $surveyquestion->fields['type'],
            'on_change' => "plugin_satisfaction_loadtype(this.value, \"" . self::NOTE . "\");"]);
        $type_dropdown = ob_get_clean();

        $script = "function plugin_satisfaction_loadtype(val, note){";
        $script .= "if(val == note) {
                  $('#show_note').show();
               } else {
                  $('#show_note').hide();
               }";
        $script .= "};";
        $type_script = Html::scriptBlock($script);

        ob_start();
        Dropdown::showNumber('number', ['max'   => 10,
            'min'   => 2,
            'value' => $surveyquestion->fields['number'],
            'on_change' => "plugin_satisfaction_load_defaultvalue(\"" . PLUGINSATISFACTION_WEBDIR . "\", this.value);"]);
        $number_dropdown = ob_get_clean();

        if (!empty($surveyquestion->fields['number'])) {
            $max_default_value = $surveyquestion->fields['number'];
        } else {
            $max_default_value = 2;
        }

        ob_start();
        Dropdown::showNumber('default_value', ['max'   => $max_default_value,
            'min'   => 1,
            'value' => $surveyquestion->fields['default_value']]);
        $default_value_dropdown = ob_get_clean();

        $is_new = ($ID <= 0);

        TemplateRenderer::getInstance()->display('@satisfaction/surveyquestion_form.html.twig', [
            'item_form_url'          => Toolbox::getItemTypeFormURL(self::getType()),
            'type_name'              => self::getTypeName(1),
            'items_id_field'         => self::$items_id,
            'items_id_value'         => $surveyquestion->fields[self::$items_id],
            'name_value'             => $surveyquestion->fields["name"],
            'comment_value'          => $surveyquestion->fields["comment"],
            'type_dropdown'          => $type_dropdown,
            'type_script'            => $type_script,
            'number_dropdown'        => $number_dropdown,
            'default_value_dropdown' => $default_value_dropdown,
            'show_note'              => ($surveyquestion->fields['type'] == self::NOTE),
            'is_new'                 => $is_new,
            'item_id'                => (int) $ID,
            'parent_id'              => $is_new && isset($survey) ? (int) $survey->getField('id') : 0,
        ]);
    }

    /**
     * Build the data of a single question row for the list template.
     *
     * @param bool $canedit
     * @param bool $canpurge
     * @param int  $rand
     *
     * @return array
     */
    public function getListRow($canedit, $canpurge, $rand)
    {
        global $CFG_GLPI;

        $items_id = $this->fields[self::$items_id];
        $id       = $this->fields["id"];

        $checkbox = '';
        if ($canpurge) {
            ob_start();
            Html::showMassiveActionCheckBox(__CLASS__, $id);
            $checkbox = ob_get_clean();
        }

        $edit_script = '';
        if ($canedit) {
            // Emit the JS via Html::scriptBlock() (framework primitive) instead of
            // a raw echoed <script>; interpolated ids are numeric (item/question id + rand).
            $params = ['type'          => __CLASS__,
                'parenttype'    => self::$itemtype,
                self::$items_id => $items_id,
                'id'            => $id];
            $js  = "function viewEditQuestion" . $items_id . $id . "$rand() {\n";
            $js .= Ajax::updateItemJsCode(
                "viewquestion" . $items_id . "$rand",
                $CFG_GLPI["root_doc"] . "/ajax/viewsubitem.php",
                $params,
                "",
                false
            );
            $js .= "};";
            $edit_script = Html::scriptBlock($js);
        }

        return [
            'edit'         => $canedit,
            'edit_onclick' => "viewEditQuestion" . $items_id . $id . "$rand();",
            'row_id'       => "viewquestion" . $items_id . $id . $rand,
            'checkbox'     => $checkbox,
            'edit_script'  => $edit_script,
            'name'         => $this->fields["name"],
            'type'         => self::getQuestionType($this->fields["type"]),
        ];
    }

    /**
     * List of question types
     *
     * @return array
     */
    public static function getQuestionTypeList()
    {
        $array                 = [];
        $array[self::YESNO]    = __('Yes') . '/' . __('No');
        $array[self::TEXTAREA] = __('Text', 'satisfaction');
        $array[self::NOTE]     = __('Note', 'satisfaction');
        return $array;
    }

    /**
     * Return the type
     *
     * @return array
     */
    public static function getQuestionType($type)
    {
        switch ($type) {
            case self::YESNO:
                return __('Yes') . '/' . __('No');
            case self::TEXTAREA:
                return __('Text', 'satisfaction');
            case self::NOTE:
                return __('Note', 'satisfaction');
        }
        return "";
    }

    /**
     * Get the standard massive actions which are forbidden
     *
     * @since version 0.84
     *
     * @return an array of massive actions
     **/
    public function getForbiddenStandardMassiveAction()
    {

        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }
}
