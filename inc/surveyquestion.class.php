<?php 
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/// Disk class
class PluginSatisfactionSurveyQuestion extends CommonDBChild {

   // From CommonDBChild
   public $itemtype  = 'PluginSatisfactionSurvey';
   public $items_id  = 'plugin_satisfaction_surveys_id';
   public $dohistory = true;

   function canCreate() {
      $item = new $this->itemtype;
      return $item->canCreate();
   }

   function canView() {
      $item = new $this->itemtype;
      return $item->canView();
   }

   static function getTypeName() {
      global $LANG;
      return $LANG['plugin_satisfaction']['question']['plural'];
   }

   static function install(Migration $migration) {
      global $DB;

      //create table
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE `$table` (
                           `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
                           `plugin_satisfaction_surveys_id` INT( 11 ) NOT NULL,
                           `name` TEXT collate utf8_unicode_ci default NULL,
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
      if ($item->getType() == 'PluginSatisfactionSurvey') {
         if ($_SESSION['glpishow_count_on_tabs']) {
            $table = getTableForItemType(__CLASS__);
            return self::createTabEntry(self::getTypeName(),
                                        countElementsInTable($table, $this->items_id.
                                                                     " = '".$item->getID()."'"));
         } else {
            return self::getTypeName();
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      self::showForSurvey($item, $withtemplate);
      return true;
   }


   static function showForSurvey(PluginSatisfactionSurvey $survey, $withtemplate='') {
      global $LANG, $CFG_GLPI;

      $squestions_obj = new self;
      $sID = $survey->fields['id'];
      $rand = mt_rand();

      $showprivate   = Session::haveRight("show_full_ticket", "1");
      $caneditall    = Session::haveRight("update_followups", "1");
      $tmp           = array($squestions_obj->items_id => $sID);
      $canadd        = $squestions_obj->can(-1, 'w', $tmp);

      //check if answer exists to forbid edition
      $answer = new PluginSatisfactionSurveyAnswer;
      $found_answer = $answer->find($answer->items_id." = ".$survey->fields['id']);
      if (count($found_answer) > 0) {
         echo "<span style='font-weight:bold; color:red'>".$LANG['plugin_satisfaction']['survey']['error'][1]."</span>";
         return false;
      }

      echo "<div id='viewquestion" . $sID . "$rand'></div>\n";
      echo "<script type='text/javascript' >\n";
      echo "function viewAddQuestion$sID$rand() {\n";
      $params = array('type'                    => __CLASS__,
                      'parenttype'              => 'PluginSatisfactionSurvey',
                      $squestions_obj->items_id => $sID,
                      'id'                      => -1);
      Ajax::updateItemJsCode("viewquestion$sID$rand",
                             $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
      echo "};";
      echo "</script>\n";
      echo "<div class='center'>".
           "<a href='javascript:viewAddQuestion$sID$rand();'>";
      echo $LANG['plugin_satisfaction']['question']['add']."</a></div><br>\n";
      
      // Display existing questions
      $questions = $squestions_obj->find($squestions_obj->items_id." = ".$sID);
      if (count($questions) == 0) {
         echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2'>";
         echo "<th class='b'>" . $LANG['plugin_satisfaction']['question']['none']."</th>";
         echo "</tr></table>";
      } else {
         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr><th>".$LANG['plugin_satisfaction']['question']['name']."</th></tr>\n";

         foreach ($questions as $question) {
            if ($squestions_obj->getFromDB($question['id'])) {
               $squestions_obj->showOne($rand);
            }
         }
         echo "</table>";
      }
   }

   function showForm($ID, $options=array()) {
      global $LANG;

      if (isset($options['parent']) && !empty($options['parent'])) {
         $survey = $options['parent'];
      }

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $input = array($this->items_id => $survey->getField('id'));
         $this->check(-1,'w',$input);
      }
         $this->showFormHeader($options);
         echo "<tr class='tab_bg_1'>";
         echo "<td>".
              $LANG['plugin_satisfaction']['question']['name']."&nbsp;:</td>";
         echo "<td><textarea name='name' cols='50' rows='6'>".
               $this->fields["name"]."</textarea></td>";
         echo "<input type='hidden' name='".$this->items_id."' value='".
              $this->fields[$this->items_id]."'>";
         echo "</td></tr>\n";
         $this->showFormButtons($options);
   }

   function showOne ($rand) {
      global $CFG_GLPI;

      echo "<tr class='tab_bg_2' style='cursor:pointer' onClick=\"viewEditQuestion".
                        $this->fields[$this->items_id].
                        $this->fields['id']."$rand();\"".
             " id='viewquestion" .  $this->fields[$this->items_id] . $this->fields["id"] . "$rand'>";

      echo "\n<script type='text/javascript' >\n";
      echo "function viewEditQuestion" . $this->fields[$this->items_id] . $this->fields["id"] . "$rand() {\n";
      $params = array('type'          => __CLASS__,
                      'parenttype'    => $this->itemtype,
                      $this->items_id => $this->fields[$this->items_id],
                      'id'            => $this->fields["id"]);
      Ajax::updateItemJsCode("viewquestion" . $this->fields[$this->items_id] . "$rand",
                             $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
      echo "};";
      echo "</script>\n";
      echo "<td class='left'>" . nl2br($this->fields["name"]) . "</td>";
      echo "</tr>\n";
   }

}