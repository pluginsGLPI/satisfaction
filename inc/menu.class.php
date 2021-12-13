<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 satisfaction plugin for GLPI
 Copyright (C) 2009-2016 by the satisfaction Development Team.

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
         $web_dir = '/' . Plugin::getWebDir('satisfaction', false);
         $menu['title']           = self::getMenuName();
         $menu['page']            = $web_dir."/front/survey.php";
         $menu['page']            = $web_dir."/front/survey.php";
         $menu['links']['search'] = PluginSatisfactionSurvey::getSearchURL(false);
         if (PluginSatisfactionSurvey::canCreate()) {
            $menu['links']['add'] = PluginSatisfactionSurvey::getFormURL(false);
         }
      }

      $menu['icon'] = self::getIcon();

      return $menu;
   }

   static function getIcon() {
      return "ti ti-thumb-up";
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
