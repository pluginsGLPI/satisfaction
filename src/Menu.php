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

use CommonGLPI;
use Session;

/**
 * Class Menu
 */
class Menu extends CommonGLPI
{
    public static $rightname = 'plugin_satisfaction';

    /**
     * @return string
     */
    public static function getMenuName()
    {
        return __('Satisfaction survey', 'satisfaction');
    }

    /**
     * @return array
     */
    public static function getMenuContent()
    {

        $menu = [];

        if (Session::haveRight('plugin_satisfaction', READ)) {
            $web_dir = '/plugins/satisfaction';
            $menu['title']           = self::getMenuName();
            $menu['page']            = $web_dir . "/front/survey.php";
            $menu['links']['search'] = Survey::getSearchURL(false);
            if (Survey::canCreate()) {
                $menu['links']['add'] = Survey::getFormURL(false);
            }
        }

        $menu['icon'] = self::getIcon();

        return $menu;
    }

    public static function getIcon()
    {
        return "ti ti-thumb-up";
    }

    public static function removeRightsFromSession()
    {
        if (isset($_SESSION['glpimenu']['admin']['types'][Menu::class])) {
            unset($_SESSION['glpimenu']['admin']['types'][Menu::class]);
        }
        if (isset($_SESSION['glpimenu']['admin']['content'][Menu::class])) {
            unset($_SESSION['glpimenu']['admin']['content'][Menu::class]);
        }
    }
}
