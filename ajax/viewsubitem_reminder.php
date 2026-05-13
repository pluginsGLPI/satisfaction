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


use Glpi\Exception\Http\NotFoundHttpException;
use GlpiPlugin\Satisfaction\Survey;
use GlpiPlugin\Satisfaction\SurveyReminder;

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();
Session::checkRight('plugin_satisfaction', UPDATE);

if (!isset($_POST['type'])) {
    throw new NotFoundHttpException();
}
if (!isset($_POST['parenttype'])) {
    throw new NotFoundHttpException();
}

$allowed_types = [SurveyReminder::class, Survey::class];
if (!in_array($_POST['type'], $allowed_types, true) || !in_array($_POST['parenttype'], $allowed_types, true)) {
    throw new NotFoundHttpException();
}

if (($item = getItemForItemtype($_POST['type']))
   && ($parent = getItemForItemtype($_POST['parenttype']))) {
    if (isset($_POST[$parent->getForeignKeyField()])
      && isset($_POST["id"])
      && $parent->getFromDB($_POST[$parent->getForeignKeyField()])) {
        $reminderName = SurveyReminder::PREDEFINED_REMINDER_OPTION_NAME;

        $options = [
         'parent' => $parent
        ];

        if (isset($_POST[$reminderName])) {
            $options[$reminderName] = intval($_POST[$reminderName]);
        }

        $item->showForm($_POST["id"], $options);
    } else {
        echo __('Access denied');
    }
}
