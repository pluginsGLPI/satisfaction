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
   die("Sorry. You can't access this file directly");
}

/**
 * Class PluginSatisfactionSurvey
 */
class PluginSatisfactionSurvey extends CommonDBTM {

   static $rightname = "plugin_satisfaction";
   public $dohistory = true;

   public $can_be_translated = true;

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {
      return _n('Satisfaction survey', 'Satisfaction surveys', $nb, 'satisfaction');
   }
    static function getIcon() {
        return PluginSatisfactionMenu::getIcon();
    }
   /**
    * Define tabs to display
    *
    * NB : Only called for existing object
    *
    * @param $options array
    *     - withtemplate is a template view ?
    *
    * @return array containing the onglets
    **/
   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(PluginSatisfactionSurveyQuestion::class, $ong, $options);
      $this->addStandardTab(PluginSatisfactionSurveyAnswer::class, $ong, $options);
      $this->addStandardTab(PluginSatisfactionSurveyResult::class, $ong, $options);
      $this->addStandardTab(PluginSatisfactionSurveyTranslation::class, $ong, $options);
      $this->addStandardTab(PluginSatisfactionSurveyReminder::class, $ong, $options);

      $this->addStandardTab(Log::class, $ong, $options);
      return $ong;
   }

   /**
    * Is translation enabled for this itemtype
    *
    * @return true if translation is available, false otherwise
    **/
   function maybeTranslated () {
      return $this->can_be_translated;
   }

   /**
    * Have I the right to "create" the Object
    *
    * Default is true and check entity if the objet is entity assign
    *
    * May be overloaded if needed
    *
    * @return boolean
    **/
   function canCreateItem(): bool
   {

      if (!$this->checkEntity()) {
         return false;
      }
      return true;
   }

   /**
    * @return array
    */
   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => self::getTypeName(2)
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'itemlink_type'      => $this->getType(),
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'is_active',
         'name'               => __('Active'),
         'datatype'           => 'bool'
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __('Comments'),
         'datatype'           => 'text'
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'date_mod',
         'name'               => __('Last update'),
         'massiveaction'      => false,
         'datatype'           => 'datetime'
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'date_creation',
         'name'               => __('Creation date'),
         'datatype'           => 'date'
      ];

      $tab[] = [
         'id'                 => '11',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __('Child entities'),
         'datatype'           => 'bool'
      ];

      $tab[] = [
         'id'                 => '30',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'datatype'           => 'number'
      ];

      $tab[] = [
         'id'                 => '80',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __('Entity'),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '86',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __('Child entities'),
         'datatype'           => 'bool'
      ];

      return $tab;
   }


   /**
    * Print survey
    *
    * @param       $ID
    * @param array $options
    *
    * @return bool
    */
   function showForm($ID, $options = []) {

      if (!$this->canView()) {
         return false;
      }

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Name') . "</td>";
      echo "<td>";
      echo Html::input('name', ['value' => $this->fields['name'], 'size' => 40]);
      echo "</td>";
      echo "<td>" . __('Comments') . "</td>";
      echo "<td>";
      echo Html::textarea([
                             'name'    => 'comment',
                             'value'    => $this->fields["comment"],
                             'cols'    => '60',
                             'rows'    => '6',
                             'display' => false,
                          ]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Active') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("is_active", $this->fields["is_active"]);
      echo "</td><td colspan='2'></td></tr>";

      $this->showFormButtons($options);
      Html::closeForm();

      return true;
   }

   /**
    * Prepare input datas for adding the item
    *
    * @param $input datas used to add the item
    *
    * @return the modified $input array
    **/
   function prepareInputForAdd($input) {

      if ($input['is_active'] == 1) {
         $dbu = new DbUtils();
         //we must store only one survey by entity
         $condition  = ['is_active' => 1]
                        + $dbu->getEntitiesRestrictCriteria($this->getTable(), 'entities_id', $input['entities_id'], true);
         $found = $this->find($condition);
         if (count($found) > 0) {
            Session::addMessageAfterRedirect(__('Error : only one survey is allowed by entity', 'satisfaction'), false, ERROR);
            return false;
         }
      }

      return $input;
   }

   /**
    * Prepare input datas for updating the item
    *
    * @param $input datas used to update the item
    *
    * @return the modified $input array
    **/
   function prepareInputForUpdate($input) {

      //active external survey for entity
      if ($input['is_active'] == 1) {
         $dbu = new DbUtils();
         //we must store only one survey by entity (other this one)
         $condition  = ['is_active' => 1,
                        ['NOT' => ['id' => $this->getID()]]]
                       + $dbu->getEntitiesRestrictCriteria($this->getTable(), 'entities_id', $input['entities_id'], true);
         $found = $this->find($condition);
         if (count($found) > 0) {
            Session::addMessageAfterRedirect(__('Error : only one survey is allowed by entity',
                                                'satisfaction'), false, ERROR);
            return false;
         }
      }

      return $input;
   }

   /**
    * Actions done before the DELETE of the item in the database /
    * Maybe used to add another check for deletion
    *
    * @return bool : true if item need to be deleted else false
    **/
   function pre_deleteItem() {
      //we must delete associated questions and answers
      $question = new PluginSatisfactionSurveyQuestion;
      $question->deleteByCriteria([PluginSatisfactionSurveyQuestion::$items_id => $this->getID()]);

      $answer = new PluginSatisfactionSurveyAnswer;
      $answer->deleteByCriteria([PluginSatisfactionSurveyAnswer::$items_id => $this->getID()]);

      $reminder = new PluginSatisfactionSurveyReminder();
      $reminder->deleteByCriteria([PluginSatisfactionSurveyReminder::$items_id => $this->getID()]);

      return true;
   }

   /**
    * Return survey by entity
    *
    * @param $entities_id
    *
    * @return bool|\PluginSatisfactionSurvey
    */
   static function getObjectForEntity($entities_id) {
      global $DB;
      $dbu = new DbUtils();
      $where = $dbu->getEntitiesRestrictRequest("AND", "survey", 'entities_id', $entities_id, true);

      $query = "SELECT `survey`.`id`
                FROM `".$dbu->getTableForItemType(__CLASS__)."` as `survey`
                LEFT JOIN `glpi_entities`
                  ON (`glpi_entities`.`id` = `survey`.`entities_id`)
                WHERE `is_active` = 1 $where
                ORDER BY `glpi_entities`.`level` DESC
                LIMIT 1";

      $result = $DB->doQuery($query);
      if (($id = $DB->result($result,0,"id")) === NULL) {
         return false;
      } else {
         return $id;
      }
      return false;
   }

   /**
    * @see CommonDBTM::getSpecificMassiveActions()
    **/
   function getSpecificMassiveActions($checkitem = null) {

      $canadd = Session::haveRight(self::$rightname, CREATE);
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($canadd) {
         $actions[__CLASS__.MassiveAction::CLASS_ACTION_SEPARATOR.'duplicate'] = _x('button', 'Duplicate');
      }
      return $actions;
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case 'duplicate' :
            $entity_assign = false;
            $dbu = new DbUtils();
            foreach ($ma->getitems() as $itemtype => $ids) {
               if ($item = $dbu->getItemForItemtype($itemtype)) {
                  if ($item->isEntityAssign()) {
                     $entity_assign = true;
                     break;
                  }
               }
            }
            if ($entity_assign) {
               Entity::dropdown();
            }
            echo "<br><br>".Html::submit(_x('button', 'Duplicate'),
                                         ['name' => 'massiveaction', 'class' => 'btn btn-primary']);
            return true;

      }
      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {

      switch ($ma->getAction()) {
         case 'duplicate':
            $survey = new self();
            foreach ($ids as $id) {
               if ($item->getFromDB($id)) {
                  if ($survey->duplicateSurvey($id, $ma->POST['entities_id'])) {
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                     $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                  }
               } else {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                  $ma->addMessage($item->getErrorMessage(ERROR_NOT_FOUND));
               }
            }
            break;

      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }

   /**
    * Duplicate a survey
    *
    * @param $ID        of the rule to duplicate
    *
    * @since version 0.85
    *
    * @return true if all ok
    **/
   function duplicateSurvey($ID, $entities_id) {

      //duplicate survey
      $survey = new self();
      $survey->getFromDB($ID);

      //Update fields of the new duplicate
      $survey->fields['name']        = sprintf(__('Copy of %s'),
                                               $survey->fields['name']);
      $survey->fields['is_active']   = 0;
      $survey->fields['entities_id'] = $entities_id;
      unset($survey->fields['id']);

      //add new duplicate
      $input = $survey->fields;
      $newID = $survey->add($input);
      if (!$newID) {
         return false;
      }
      //find and duplicate questions
      $question_obj  = new PluginSatisfactionSurveyQuestion();
      $questions = $question_obj->find(['plugin_satisfaction_surveys_id' => $ID]);
      $questions = $questions;
      foreach ($questions as $question) {
         $question['plugin_satisfaction_surveys_id'] = $newID;
         $question_id = $question['id'];
         unset($question['id']);
         if (!$new_question_id = $question_obj->add($question)) {
            return false;
         }
         //find and duplicate translations
         $translation_obj  = new PluginSatisfactionSurveyTranslation();
         $translations = $translation_obj->find([
            'plugin_satisfaction_surveys_id' => $ID,
            'glpi_plugin_satisfaction_surveyquestions_id' => $question_id
         ]);
         $translations = $translations;
         foreach ($translations as $translation) {
            $translation_obj->newSurveyTranslation([
               'survey_id' => $newID,
               'question_id' => $new_question_id,
               'language' => $translation['language'],
               'value' => $translation['value']
            ]);
         }
      }

      return true;
   }

}
