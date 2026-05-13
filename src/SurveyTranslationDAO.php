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

namespace GlpiPlugin\Satisfaction;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class SurveyTranslationDAO
{
    public static $tablename = "glpi_plugin_satisfaction_surveytranslations";

    public static function getSurveyTranslationByCrit($crit = [])
    {
        global $DB;

        $rows = [];
        foreach ($DB->request(self::$tablename, $crit) as $data) {
            $rows[] = $data;
        }
        return $rows;
    }

    public static function countSurveyTranslationByCrit($crit = [])
    {
        global $DB;

        $result = $DB->request([
            'COUNT' => 'nb',
            'FROM'  => self::$tablename,
            'WHERE' => $crit,
        ]);
        $row = $result->current();
        return $row ? (int) $row['nb'] : 0;
    }

    public static function getSurveyTranslationByID($ID)
    {
        global $DB;

        $result = $DB->request([
            'FROM'  => self::$tablename,
            'WHERE' => ['id' => (int) $ID],
            'LIMIT' => 1,
        ]);
        return $result->current() ?: null;
    }

    public static function newSurveyTranslation($surveyId, $questionId, $language, $value)
    {
        global $DB;

        $result = $DB->insert(self::$tablename, [
            'plugin_satisfaction_surveys_id'                  => (int) $surveyId,
            'glpi_plugin_satisfaction_surveyquestions_id'     => (int) $questionId,
            'language'                                        => $language,
            'value'                                           => $value,
        ]);

        return $result ? $DB->insertId() : null;
    }

    public static function editSurveyTranslation($id, $value)
    {
        global $DB;

        return $DB->update(self::$tablename, ['value' => $value], ['id' => (int) $id]);
    }
}
