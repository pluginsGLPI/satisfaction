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

use Glpi\Exception\Http\BadRequestHttpException;
use GlpiPlugin\Satisfaction\SurveyReminder;

Session::checkLoginUser();

$reminder = new SurveyReminder();

if (isset($_POST["add"])) {
    $input = $_POST;

    if (isset($input[$reminder::PREDEFINED_REMINDER_OPTION_NAME])) {
        $input = $reminder->generatePredefinedReminderForAdd($input);
    }

    $reminder->check(-1, CREATE, $input);
    $reminder->add($input);
    Html::back();
} elseif (isset($_POST["update"])) {
    $reminder->check($_POST['id'], UPDATE);
    $reminder->update($_POST);
    Html::back();
} elseif (isset($_POST["delete"])) {
    $reminder->check($_POST['id'], PURGE);
    $reminder->delete($_POST);
    Html::back();
}

throw new BadRequestHttpException('Lost');
