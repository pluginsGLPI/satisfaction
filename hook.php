<?php

function plugin_satisfaction_install() {
   $migration = new Migration("0.83+1.0");
   
   foreach (array('PluginSatisfactionSurvey', 'PluginSatisfactionSurveyQuestion') as $itemtype) {
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
   foreach (array('PluginSatisfactionSurvey', 'PluginSatisfactionSurveyQuestion') as $itemtype) {
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

?>