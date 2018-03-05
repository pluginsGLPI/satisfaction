<?php

/**
 * Class PluginSatisfactionMenu
 */
class PluginSatisfactionMenu extends CommonGLPI
{
   static $rightname = 'plugin_satisfaction';

   /**
    * @return translated
    */
   static function getMenuName() {
      return __('Satisfaction survey', 'satisfaction');
   }

   /**
    * @return array
    */
   static function getMenuContent() {

      $menu = [];

      if (Session::haveRight('plugin_satisfaction', READ)) {
         $menu['title']           = self::getMenuName();
         $menu['page']            = "/plugins/satisfaction/front/survey.php";
         $menu['page']            = "/plugins/satisfaction/front/survey.php";
         $menu['links']['search'] = PluginSatisfactionSurvey::getSearchURL(false);
         if (PluginSatisfactionSurvey::canCreate()) {
            $menu['links']['add'] = PluginSatisfactionSurvey::getFormURL(false);
         }
      }

      return $menu;
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['admin']['types']['PluginSatisfactionMenu'])) {
         unset($_SESSION['glpimenu']['admin']['types']['PluginSatisfactionMenu']);
      }
      if (isset($_SESSION['glpimenu']['admin']['content']['pluginsatisfactionmenu'])) {
         unset($_SESSION['glpimenu']['admin']['content']['pluginsatisfactionmenu']);
      }
   }
}
