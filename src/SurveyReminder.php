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
use Html;
use Session;
use Toolbox;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class SurveyReminder extends CommonDBChild
{
    public static $rightname = "plugin_satisfaction";
    public $dohistory = true;

    // From CommonDBChild
    public static $itemtype = Survey::class;
    public static $items_id = 'plugin_satisfaction_surveys_id';

    // Durations
    public const DURATION_DAY   = 0;
    public const DURATION_MONTH = 1;

    // Is active
    public const ACTIVE_OFF = 0;
    public const ACTIVE_ON  = 1;

    // Columns names
    public const COLUMN_NAME          = 'name';
    public const COLUMN_DURATION_TYPE = 'duration_type';
    public const COLUMN_DURATION      = 'duration';
    public const COLUMN_IS_ACTIVE     = 'is_active';
    public const COLUMN_COMMENT       = 'comment';

    // Predefined reminders
    public const PREDEFINED_1_WEEK               = 0;
    public const PREDEFINED_2_WEEK               = 1;
    public const PREDEFINED_1_MONTH              = 2;
    public const PREDEFINED_REMINDER_OPTION_NAME = 'presetreminder';


    /**
     * Return the localized name of the current Type
     * Should be overloaded in each new class
     *
     * @return string
     **/
    public static function getTypeName($nb = 0)
    {
        return _n('Reminder', 'Reminders', $nb, 'satisfaction');
    }

    /**
     * Get Tab Name used for itemtype
     *
     * NB : Only called for existing object
     *      Must check right on what will be displayed + template
     *
     * @param $item                     CommonGLPI object for which the tab need to be displayed
     * @param $withtemplate    boolean  is a template object ? (default 0)
     *
     * @return string tab name
     **@since version 0.83
     *
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        // can exists for template
        if ($item->getType() == Survey::class) {
            return self::createTabEntry(_n('Reminder', 'Reminders', 2, 'satisfaction'));
        }

        return '';
    }
    public static function getIcon()
    {
        return "ti ti-bell";
    }
    /**
     * show Tab content
     *
     * @param $item                  CommonGLPI object for which the tab need to be displayed
     * @param $tabnum       integer  tab number (default 1)
     * @param $withtemplate boolean  is a template object ? (default 0)
     *
     * @return true
     **@since version 0.83
     *
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == Survey::class) {
            self::showSurvey($item, true);
        }
        return true;
    }

    /**
     * Print survey
     *
     * @param CommonGLPI $item
     * @param bool        $preview
     *
     * @return bool
     */
    public static function showSurvey(Survey $survey, $preview = false)
    {

        global $CFG_GLPI;

        $surveyReminder = new self();
        $sID            = $survey->fields['id'];
        $rand_survey    = mt_rand();

        $canadd   = Session::haveRight(self::$rightname, CREATE);
        $canedit  = Session::haveRight(self::$rightname, UPDATE);
        $canpurge = Session::haveRight(self::$rightname, PURGE);

        // Add reminder / predefined reminder ajax actions
        $add_scripts = '';
        if ($canadd) {
            ob_start();
            echo "<script type='text/javascript'>\n";

            // Add reminder ajax action
            echo "function viewAddReminder$sID$rand_survey() {\n";
            $params = [
                'type'          => __CLASS__,
                'parenttype'    => Survey::class,
                self::$items_id => $sID,
                'id'            => -1,
            ];
            Ajax::updateItemJsCode(
                "viewreminder$sID$rand_survey",
                $CFG_GLPI["root_doc"] . "/ajax/viewsubitem.php",
                $params
            );
            echo "};";

            // Add predefined reminder ajax action
            echo "function viewAddPredefinedReminder$sID$rand_survey() {\n";
            $params = [
                'type'                                => __CLASS__,
                'parenttype'                          => Survey::class,
                self::$items_id                       => $sID,
                'id'                                  => -1,
                self::PREDEFINED_REMINDER_OPTION_NAME => 1,
            ];
            Ajax::updateItemJsCode(
                "viewreminder$sID$rand_survey",
                PLUGINSATISFACTION_WEBDIR . "/ajax/viewsubitem_reminder.php",
                $params
            );
            echo "};";

            echo "</script>\n";
            $add_scripts = ob_get_clean();
        }

        ob_start();
        Dropdown::showNumber('reminders_days', ['value' => $survey->fields["reminders_days"],
            'min'   => 1,
            'max'   => 365]);
        $reminders_days_dropdown = ob_get_clean();

        // Display existing reminders
        $remminders = $surveyReminder->find([self::$items_id => $sID], 'id');

        $rows      = [];
        $ma_top    = '';
        $ma_bottom = '';
        $checkall  = '';
        if (count($remminders) > 0) {
            $rand = mt_rand();
            if ($canpurge) {
                ob_start();
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass' . __CLASS__ . $rand];
                Html::showMassiveActions($massiveactionparams);
                $ma_top   = ob_get_clean();
                $checkall = Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            }

            foreach ($remminders as $reminder) {
                if ($surveyReminder->getFromDB($reminder['id'])) {
                    $rows[] = $surveyReminder->getListRow($canedit, $canpurge, $rand_survey);
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

        TemplateRenderer::getInstance()->display('@satisfaction/surveyreminder_list.html.twig', [
            'container_id'            => "viewreminder$sID$rand_survey",
            'can_add'                 => $canadd,
            'can_purge'               => $canpurge,
            'add_scripts'             => $add_scripts,
            'add_onclick'             => "viewAddReminder$sID$rand_survey();",
            'add_predefined_onclick'  => "viewAddPredefinedReminder$sID$rand_survey();",
            'reminders_days_dropdown' => $reminders_days_dropdown,
            'survey_id'               => (int) $sID,
            'checkall'                => $checkall,
            'ma_top'                  => $ma_top,
            'ma_bottom'               => $ma_bottom,
            'col_name'                => $surveyReminder->getColumnTitles(self::COLUMN_NAME),
            'col_duration_type'       => $surveyReminder->getColumnTitles(self::COLUMN_DURATION_TYPE),
            'col_duration'            => $surveyReminder->getColumnTitles(self::COLUMN_DURATION),
            'col_is_active'           => $surveyReminder->getColumnTitles(self::COLUMN_IS_ACTIVE),
            'rows'                    => $rows,
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
        if (isset($options['parent']) && !empty($options['parent'])) {
            $survey = $options['parent'];
        }

        $surveyReminder = new self();
        if ($ID <= 0) {
            $surveyReminder->getEmpty();
        } else {
            $surveyReminder->getFromDB($ID);
        }

        if (!$surveyReminder->canView()) {
            return false;
        }

        $displayPresetReminderForm = isset($options[self::PREDEFINED_REMINDER_OPTION_NAME])
                                   && $options[self::PREDEFINED_REMINDER_OPTION_NAME];

        $is_new = ($ID <= 0);

        $data = [
            'item_form_url'   => Toolbox::getItemTypeFormURL(self::getType()),
            'predefined'      => $displayPresetReminderForm,
            'items_id_field'  => self::$items_id,
            'is_new'          => $is_new,
            'item_id'         => (int) $ID,
            'parent_id'       => $is_new && isset($survey) ? (int) $survey->getField('id') : 0,
        ];

        if ($displayPresetReminderForm) {
            $data['preset_dropdown'] = self::getPresetReminderDropdown(self::PREDEFINED_REMINDER_OPTION_NAME);
        } else {
            ob_start();
            Dropdown::showNumber(self::COLUMN_DURATION, ['value' => $surveyReminder->fields[self::COLUMN_DURATION],
                'min'   => 1,
                'max'   => 365]);
            $duration_dropdown = ob_get_clean();

            ob_start();
            Dropdown::showYesNo(self::COLUMN_IS_ACTIVE, $surveyReminder->fields[self::COLUMN_IS_ACTIVE]);
            $is_active_dropdown = ob_get_clean();

            $data += [
                'col_name'               => self::getColumnTitles(self::COLUMN_NAME),
                'col_comment'            => self::getColumnTitles(self::COLUMN_COMMENT),
                'col_duration_type'      => self::getColumnTitles(self::COLUMN_DURATION_TYPE),
                'col_duration'           => self::getColumnTitles(self::COLUMN_DURATION),
                'col_is_active'          => self::getColumnTitles(self::COLUMN_IS_ACTIVE),
                'name_value'             => $surveyReminder->fields["name"],
                'comment_value'          => $surveyReminder->fields[self::COLUMN_COMMENT],
                'items_id_value'         => $surveyReminder->fields[self::$items_id],
                'duration_type_dropdown' => self::getDurationDropdown(
                    self::COLUMN_DURATION_TYPE,
                    $surveyReminder->fields[self::COLUMN_DURATION_TYPE]
                ),
                'duration_dropdown'      => $duration_dropdown,
                'is_active_dropdown'     => $is_active_dropdown,
            ];
        }

        TemplateRenderer::getInstance()->display('@satisfaction/surveyreminder_form.html.twig', $data);
    }

    /**
     * Build the data of a single reminder row for the list template.
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
            ob_start();
            echo "<script type='text/javascript'>\n";
            echo "function viewEditReminder" . $items_id . $id . "$rand() {\n";
            $params = ['type'          => __CLASS__,
                'parenttype'    => self::$itemtype,
                self::$items_id => $items_id,
                'id'            => $id];
            Ajax::updateItemJsCode(
                "viewreminder" . $items_id . "$rand",
                $CFG_GLPI["root_doc"] . "/ajax/viewsubitem.php",
                $params
            );
            echo "};";
            echo "</script>\n";
            $edit_script = ob_get_clean();
        }

        return [
            'edit'          => $canedit,
            'edit_onclick'  => "viewEditReminder" . $items_id . $id . "$rand();",
            'row_id'        => "viewquestion" . $items_id . $id . $rand,
            'checkbox'      => $checkbox,
            'edit_script'   => $edit_script,
            'name'          => $this->fields[self::COLUMN_NAME],
            'duration_type' => $this->getDurationTitles($this->fields[self::COLUMN_DURATION_TYPE]),
            'duration'      => (string) $this->fields[self::COLUMN_DURATION],
            'is_active'     => $this->getActiveTitles($this->fields[self::COLUMN_IS_ACTIVE]),
        ];
    }

    /**
     * Get the standard massive actions which are forbidden
     *
     * @return array array of massive actions
     **@since version 0.84
     *
     */
    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }

    public function getActiveTitles($id)
    {
        $titles = [
            self::ACTIVE_OFF => __('No'),
            self::ACTIVE_ON  => __('Yes'),
        ];
        return $titles[$id];
    }

    public function getDurationTitles($id = null)
    {

        $titles = [
            self::DURATION_DAY   => __('Day'),
            self::DURATION_MONTH => __('Month', 'satisfaction'),
        ];

        if (is_null($id)) {
            return $titles;
        } else {
            return $titles[$id];
        }
    }

    public function getPresetReminderTitles($id = null)
    {
        $yolo = 2;

        $titles = [
            self::PREDEFINED_1_WEEK  => __('One Week', 'satisfaction'),
            self::PREDEFINED_2_WEEK  => __('Two Week', 'satisfaction'),
            self::PREDEFINED_1_MONTH => __('One Month', 'satisfaction'),
        ];

        if (is_null($id)) {
            return $titles;
        } else {
            return $titles[$id];
        }
    }

    public function getColumnTitles($id)
    {
        $titles = [
            self::COLUMN_NAME          => __('Name'),
            self::COLUMN_DURATION_TYPE => __("Duration Type", "satisfaction"),
            self::COLUMN_DURATION      => __("Duration", "satisfaction"),
            self::COLUMN_COMMENT       => __("Comments"),
            self::COLUMN_IS_ACTIVE     => __("Active"),
        ];

        return $titles[$id];
    }

    public function getDurationDropdown($name, $defaultValue)
    {

        $params = [
            'display' => false,
            'value'   => $defaultValue,
        ];

        return Dropdown::showFromArray($name, self::getDurationTitles(), $params);
    }

    public function getPresetReminderDropdown($name)
    {
        $params = [
            'display' => false,
        ];

        return Dropdown::showFromArray($name, self::getPresetReminderTitles(), $params);
    }

    public function generatePredefinedReminderForAdd($postValues)
    {

        $namePrefix = __('Reminder', 'satisfaction');
        $comment    = __('Preset Reminder');

        switch (intval($postValues[self::PREDEFINED_REMINDER_OPTION_NAME])) {
            case self::PREDEFINED_1_WEEK:
                $postValues[self::COLUMN_NAME]          = $namePrefix . " " . self::getPresetReminderTitles(
                    self::PREDEFINED_1_WEEK
                );
                $postValues[self::COLUMN_COMMENT]       = $comment;
                $postValues[self::COLUMN_DURATION_TYPE] = self::DURATION_DAY;
                $postValues[self::COLUMN_DURATION]      = 7;
                break;
            case self::PREDEFINED_2_WEEK:
                $postValues[self::COLUMN_NAME]          = $namePrefix . " " . self::getPresetReminderTitles(
                    self::PREDEFINED_2_WEEK
                );
                $postValues[self::COLUMN_COMMENT]       = $comment;
                $postValues[self::COLUMN_DURATION_TYPE] = self::DURATION_DAY;
                $postValues[self::COLUMN_DURATION]      = 14;
                break;
            case self::PREDEFINED_1_MONTH:
                $postValues[self::COLUMN_NAME]          = $namePrefix . " " . self::getPresetReminderTitles(
                    self::PREDEFINED_1_MONTH
                );
                $postValues[self::COLUMN_COMMENT]       = $comment;
                $postValues[self::COLUMN_DURATION_TYPE] = self::DURATION_MONTH;
                $postValues[self::COLUMN_DURATION]      = 1;
                break;
        }
        return $postValues;
    }

    /**
     * Verify this survey does not have a reminder with exact same duration type and duration
     *
     * @param $input
     *
     * @return array|bool
     */
    public function prepareInputForAdd($input)
    {
        $crit = [
            self::COLUMN_DURATION_TYPE => $input[self::COLUMN_DURATION_TYPE],
            self::COLUMN_DURATION      => $input[self::COLUMN_DURATION],
        ];

        $items = $this->find($crit);

        if (count($items)) {
            $item = array_pop($items);

            $errorMessage = __(
                'You already have a reminder with the same duration type and duration named : %s',
                'satisfaction'
            );

            Session::addMessageAfterRedirect(sprintf($errorMessage, $item['name']), false, ERROR);
            return false;
        }
        return $input;
    }

    /**
     * Verify survey remindr can be updated only if a column is different
     *
     * @param $input
     *
     * @return array|bool
     */
    public function prepareInputForUpdate($input)
    {
        $crit = [
            self::COLUMN_DURATION_TYPE => $input[self::COLUMN_DURATION_TYPE],
            self::COLUMN_DURATION      => $input[self::COLUMN_DURATION],
            self::COLUMN_IS_ACTIVE     => $input[self::COLUMN_IS_ACTIVE],
            self::COLUMN_NAME          => $input[self::COLUMN_NAME],
            self::COLUMN_COMMENT       => $input[self::COLUMN_COMMENT],
        ];

        $items = $this->find($crit);

        if (count($items)) {
            $item = array_pop($items);

            $errorMessage = __('There are nothing to save', 'satisfaction');

            Session::addMessageAfterRedirect(sprintf($errorMessage, $item['name']), false, ERROR);
            return false;
        }
        return $input;
    }
}
