<?php

function plugin_satisfaction_install() {
   $migration = new Migration("0.83+1.0");
   
   foreach (array('PluginSatisfactionSurvey', 
                  'PluginSatisfactionSurveyQuestion', 
                  'PluginSatisfactionSurveyAnswer') as $itemtype) {
      if ($plug=isPluginItemType($itemtype)) {
         $plugname = strtolower($plug['plugin']);
         $dir      = GLPI_ROOT . "/plugins/$plugname/inc/";
         $item     = strtolower($plug['class']);
         if (file_exists("$dir$item.class.php")) {
            include_once ("$dir$item.class.php");
            if (!call_user_func(array($itemtype,'install'), $migration)) {
               return false;
            }
         }
      }
   }

   return true;
}

function plugin_satisfaction_uninstall() {
   foreach (array('PluginSatisfactionSurvey', 
                  'PluginSatisfactionSurveyQuestion', 
                  'PluginSatisfactionSurveyAnswer') as $itemtype) {
      if ($plug=isPluginItemType($itemtype)) {
         $plugname = strtolower($plug['plugin']);
         $dir      = GLPI_ROOT . "/plugins/$plugname/inc/";
         $item     = strtolower($plug['class']);
         if (file_exists("$dir$item.class.php")) {
            include_once ("$dir$item.class.php");
            call_user_func(array($itemtype, 'uninstall'));
         }
      }
   }
   return true;
}

function plugin_satisfaction_getAddSearchOptions($itemtype) {
   global $LANG;

   if ($itemtype == 'Ticket') {
         return PluginSatisfactionSurvey::getAddSearchOptionsForTicket();
   }
}

function plugin_satisfaction_giveItem($type,$ID,$data,$num) {
   $out = "";
   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case "glpi_plugin_satisfaction_surveyanswers.answer" :
         if (!empty($data["ITEM_$num"])) {
            $answers = json_decode($data["ITEM_$num"], true);
            $index = $searchopt[$ID]["questions_id"];
            if (isset($answers[$index])) {
               $out = $answers[$index];
            } else {
               return " ";
            }
         }
         
         return $out;
   }
   return "";
}


?>