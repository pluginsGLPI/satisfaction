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

class PluginSatisfactionSurveyTranslationDAO
{

    public static $tablename = "glpi_plugin_satisfaction_surveytranslations";

    public static function getSurveyTranslationByCrit($crit = [])
    {
        global $DB;
        $datas = [];

        $query = "SELECT * FROM `".self::$tablename."`";
        if (!empty($crit)) {
            $it = 0;
            foreach ($crit as $key => $value) {
                if ($it == 0) {
                    $query.= " WHERE ";
                } else {
                    $query.= " AND ";
                }
                if (is_string($value)) {
                    $query.= "`$key` = '".$value."'";
                } else {
                    $query.= "`$key` = ".$value;
                }
                $it++;
            }
        }

        $result = $DB->doQuery($query);

        while ($data = $DB->fetchAssoc($result)) {
            $datas[] = $data;
        }
        return $datas;
    }

    public static function countSurveyTranslationByCrit($crit = [])
    {
        global $DB;

        $query = "SELECT count(*) as nb FROM `".self::$tablename."`";
        if (!empty($crit)) {
            $it = 0;
            foreach ($crit as $key => $value) {
                if ($it == 0) {
                    $query.= " WHERE ";
                } else {
                    $query.= " AND ";
                }
                if (is_string($value)) {
                    $query.= "`$key` = '".$value."'";
                } else {
                    $query.= "`$key` = ".$value;
                }

                $it++;
            }
        }

        $result = $DB->doQuery($query);
        while ($data = $DB->fetchAssoc($result)) {
            return $data['nb'];
        }
        return 0;
    }

    public static function getSurveyTranslationByID($ID)
    {
        global $DB;

        $query = "SELECT * FROM `".self::$tablename."`";
        $query .=" WHERE `id` = ".$ID;

        $result = $DB->doQuery($query);
        while ($data = $DB->fetchAssoc($result)) {
            return $data;
        }
    }

    public static function newSurveyTranslation($surveyId, $questionId, $language, $value)
    {
        global $DB;

        $query = "INSERT INTO `".self::$tablename."`";
        $query .= " (
        `plugin_satisfaction_surveys_id`, `glpi_plugin_satisfaction_surveyquestions_id`, `language`, `value`)";
        $query .= " VALUES(".$surveyId.",".$questionId.",'".$language."','".$value."')";

        if ($DB->doQuery($query)) {
            return $DB->insertId();
        } else {
            return null;
        }
    }

    public static function editSurveyTranslation($id, $value)
    {
        global $DB;

        $query = "UPDATE `".self::$tablename."`";
        $query .= " SET `value` = '".$value."'";
        $query .= " WHERE `id` = ".$id;

        return ($DB->doQuery($query));
    }
}
