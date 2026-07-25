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

use CommonDBTM;
use DbUtils;
use Glpi\Application\View\TemplateRenderer;
use Html;
use Session;
use Ticket;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class NotificationMailing
 */
class NotificationMailing extends CommonDBTM
{
    public static $rightname = 'plugin_satisfaction';

    /**
     * Return the localized name of the current Type
     * Should be overloaded in each new class
     *
     * @param integer $nb Number of items
     *
     * @return string
     **/
    public static function getTypeName($nb = 0)
    {

        return __('Notification satisfaction reminder', 'satisfaction');
    }

    /**
     * Have I the global right to "create" the Object
     * May be overloaded if needed (ex KnowbaseItem)
     *
     * @return
     **/
    public static function canCreate(): bool
    {
        return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
    }

    /**
     * Have I the global right to "view" the Object
     *
     * Default is true and check entity if the objet is entity assign
     *
     * May be overloaded if needed
     *
     * @return
     **/
    public static function canView(): bool
    {
        return Session::haveRight(self::$rightname, READ);
    }


    /**
     * Function get the Status
     *
     * @return  array
     */
    public static function getStatus($value)
    {
        $data = self::getAllStatusArray();
        return $data[$value];
    }

    /**
     * Get the SNMP Status list
     *
     * @return array
     */
    public static function getAllStatusArray()
    {

        // To be overridden by class
        $tab = ['report'  => __('Resource creation', 'resources'),
            'other'   => __('Other', 'resources')];

        return $tab;
    }

    /**
     * if profile deleted
     *
     * @param Ticket $resource
     */
    public static function purgeNotification(Ticket $ticket)
    {
        $temp = new self();
        $temp->deleteByCriteria(['tickets_id' => $ticket->getField("id")]);
    }
}
