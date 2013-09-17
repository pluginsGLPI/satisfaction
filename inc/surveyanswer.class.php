<?php

class PluginSatisfactionSurveyAnswer extends CommonDBChild {
   // From CommonDBChild
   public $itemtype  = 'PluginSatisfactionSurvey';
   public $items_id  = 'plugin_satisfaction_surveys_id';
   public $dohistory = true;

   function canCreate() {
      return true;
   }

   function canView() {
      return true;
   }

   static function getTypeName() {
      global $LANG;
      return $LANG['plugin_satisfaction']['answer']['name'];
   }

   static function install(Migration $migration) {
      global $DB;

      //create table
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE `$table` (
                           `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
                           `answer` TEXT collate utf8_unicode_ci default NULL,
                           `comment` TEXT collate utf8_unicode_ci default NULL,
                           `plugin_satisfaction_surveys_id` INT( 11 ) NOT NULL,
                           `tickets_id` INT( 11 ) NOT NULL,
                           PRIMARY KEY ( `id` )
                           ) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());
      }

      return true;
   }

   static function uninstall() {
      global $DB;
      
      $query = "DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`";
      return $DB->query($query) or die($DB->error());
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      // can exists for template
      if ($item->getType() == 'Ticket' && $item->fields['status'] == "closed") {
         // a survey is active for current entity of item
         $survey = PluginSatisfactionSurvey::getObjectForEntity($item->fields['entities_id']);
         if ($survey !== false) {
            return $LANG['plugin_satisfaction']['name'];
         }
      } elseif ($item->getType() == 'PluginSatisfactionSurvey') {
         return $LANG['plugin_satisfaction']['answer']['preview'];
      }

      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      if ($item->getType() == 'Ticket') {
         self::showSurvey($item);

      } elseif ($item->getType() == 'PluginSatisfactionSurvey') {
         self::showSurvey($item, true);

      }
      return true;
   }

   static function showSurvey(CommonGLPI $item, $preview = false) {
      global $LANG;

      //find existing answer
      $sanswer_obj = new self;
      $ID = 0;
      if ($item instanceof Ticket) {
         $found_answer = $sanswer_obj->find("tickets_id = ".$item->getID());
         if (count($found_answer) > 0) {
            $first_answer = array_shift($found_answer);
            $ID = $first_answer['id'];
         }
      }

      $survey = PluginSatisfactionSurvey::getObjectForEntity($item->fields['entities_id'], !$preview);
      if ($survey === false) return false;

      //rights checks
      if ($ID > 0) {
         $sanswer_obj->check($ID,'r');
      } else {
         // Create item
         $input = array($sanswer_obj->items_id => $survey->getID());
         $sanswer_obj->check(-1,'w',$input);
      }

      //show form
      echo "<form name='form' method='post' action='".$sanswer_obj->getFormURL()."'>";
      echo "<input type='hidden' name='".$sanswer_obj->items_id."' value='".
           $sanswer_obj->fields[$sanswer_obj->items_id]."'>";
      echo "<div class='spaced' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".$LANG['plugin_satisfaction']['name']."&nbsp;:</th></tr>";

      //list survey questions
      $squestion_obj = new PluginSatisfactionSurveyQuestion;
      foreach ($squestion_obj->find($squestion_obj->items_id." = ".$survey->getID()) as $question) {
         echo "<tr>";
         echo "<th>".$question['name']."</th>";
         echo "<td>";
         self::showStarAnswer($question['id']);
         echo "</td>";
         echo "</tr>";
      }

      //add comment field
      echo "<tr>";
      echo "<th>".$LANG['common'][25]."&nbsp;:</th>";
      echo "<td>";
      echo "<textarea cols='80' rows=7' name='comment' >".
           $sanswer_obj->fields["comment"]."</textarea>";
      echo "</td></tr>\n";

      //close form
      if (!$preview) {
         $sanswer_obj->showFormButtons();
      }
   }

   static function showStarAnswer($questions_id, $value = 0) {
      echo "<input type='hidden' id='answer_$questions_id' 
                   name='answer_$questions_id' value='$value'>";
      echo  "<script type='text/javascript'>\n
         Ext.onReady(function() {
            var md = new Ext.form.StarRate({
                    hiddenName: 'answer_$questions_id',
                    starConfig: {
                     minValue: 0,
                     maxValue: 5,
                     value:$value
                    },
                    applyTo : 'answer_$questions_id'
            });
         })
         </script>";
   }
}