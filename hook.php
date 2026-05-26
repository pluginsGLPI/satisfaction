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

use GlpiPlugin\Satisfaction\Menu;
use GlpiPlugin\Satisfaction\NotificationTargetTicket;
use GlpiPlugin\Satisfaction\Profile;
use GlpiPlugin\Satisfaction\Reminder;
use GlpiPlugin\Satisfaction\Survey;

/**
 * @return bool
 */
function plugin_satisfaction_install()
{
    global $DB;

    if (!$DB->tableExists("glpi_plugin_satisfaction_surveys")) {
        $DB->runFile(Plugin::getPhpDir('satisfaction') . "/install/sql/empty-1.6.0.sql");
    } else {
        if (!$DB->fieldExists("glpi_plugin_satisfaction_surveyquestions", "type")) {
            $DB->runFile(Plugin::getPhpDir('satisfaction') . "/install/sql/update-1.1.0.sql");
        }
        //version 1.2.1
        if (!$DB->fieldExists("glpi_plugin_satisfaction_surveyquestions", "default_value")) {
            $DB->runFile(Plugin::getPhpDir('satisfaction') . "/install/sql/update-1.2.2.sql");
        }
        //version 1.4.1
        if (!$DB->tableExists("glpi_plugin_satisfaction_surveytranslations")) {
            $DB->runFile(Plugin::getPhpDir('satisfaction') . "/install/sql/update-1.4.1.sql");
        }

        //version 1.4.3
        if (!$DB->tableExists("glpi_plugin_satisfaction_surveyreminders")) {
            $DB->runFile(Plugin::getPhpDir('satisfaction') . "/install/sql/update-1.4.3.sql");
        }

        //version 1.4.5
        if (!$DB->fieldExists("glpi_plugin_satisfaction_surveys", "reminders_days")) {
            $DB->runFile(Plugin::getPhpDir('satisfaction') . "/install/sql/update-1.4.5.sql");
        }

        //version 1.7.1
        $DB->runFile(Plugin::getPhpDir('satisfaction') . "/install/sql/update-1.7.1.sql");

    }

    //DisplayPreferences Migration
    $classes = ['PluginSatisfactionSurvey' => Survey::class];

    foreach ($classes as $old => $new) {
        $displayusers = $DB->request([
            'SELECT' => [
                'users_id'
            ],
            'DISTINCT' => true,
            'FROM' => 'glpi_displaypreferences',
            'WHERE' => [
                'itemtype' => $old,
            ],
        ]);

        if (count($displayusers) > 0) {
            foreach ($displayusers as $displayuser) {
                $iterator = $DB->request([
                    'SELECT' => [
                        'num',
                        'id'
                    ],
                    'FROM' => 'glpi_displaypreferences',
                    'WHERE' => [
                        'itemtype' => $old,
                        'users_id' => $displayuser['users_id'],
                        'interface' => 'central'
                    ],
                ]);

                if (count($iterator) > 0) {
                    foreach ($iterator as $data) {
                        $iterator2 = $DB->request([
                            'SELECT' => [
                                'id'
                            ],
                            'FROM' => 'glpi_displaypreferences',
                            'WHERE' => [
                                'itemtype' => $new,
                                'users_id' => $displayuser['users_id'],
                                'num' => $data['num'],
                                'interface' => 'central'
                            ],
                        ]);
                        if (count($iterator2) > 0) {
                            foreach ($iterator2 as $dataid) {
                                $query = $DB->buildDelete(
                                    'glpi_displaypreferences',
                                    [
                                        'id' => $dataid['id'],
                                    ]
                                );
                                $DB->doQuery($query);
                            }
                        } else {
                            $query = $DB->buildUpdate(
                                'glpi_displaypreferences',
                                [
                                    'itemtype' => $new,
                                ],
                                [
                                    'id' => $data['id'],
                                ]
                            );
                            $DB->doQuery($query);
                        }
                    }
                }
            }
        }
    }

    NotificationTargetTicket::install();
    Profile::initProfile();
    Profile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

    CronTask::Register(Reminder::class, Reminder::CRON_TASK_NAME, DAY_TIMESTAMP);
    return true;
}

/**
 * @return bool
 */
function plugin_satisfaction_uninstall()
{
    global $DB;

    $tables = [
        "glpi_plugin_satisfaction_surveys",
        "glpi_plugin_satisfaction_surveyquestions",
        "glpi_plugin_satisfaction_surveyanswers",
        "glpi_plugin_satisfaction_surveyreminders",
        "glpi_plugin_satisfaction_surveytranslations",
        "glpi_plugin_satisfaction_reminders",
    ];

    foreach ($tables as $table) {
        $DB->dropTable($table, true);
    }

    $itemtypes = ['Alert',
        'DisplayPreference',
        'Document_Item',
        'ImpactItem',
        'Item_Ticket',
        'Link_Itemtype',
        'Notepad',
        'SavedSearch',
        'DropdownTranslation'];
    foreach ($itemtypes as $itemtype) {
        $item = new $itemtype();
        $item->deleteByCriteria(['itemtype' => Survey::class]);
    }


    //Delete rights associated with the plugin
    $profileRight = new ProfileRight();
    foreach (Profile::getAllRights() as $right) {
        $profileRight->deleteByCriteria(['name' => $right['field']]);
    }
    Profile::removeRightsFromSession();

    Menu::removeRightsFromSession();

    NotificationTargetTicket::uninstall();

    CronTask::Register(Reminder::class, Reminder::CRON_TASK_NAME, DAY_TIMESTAMP);

    return true;
}
