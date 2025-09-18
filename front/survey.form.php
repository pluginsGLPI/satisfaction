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



Session::checkLoginUser();

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

$survey = new PluginSatisfactionSurvey();

if (isset($_POST["add"])) {
    $survey->check(-1, CREATE, $_POST);
    $survey->add($_POST);
    Html::back();
} elseif (isset($_POST["purge"])) {
    $survey->check($_POST['id'], PURGE);
    $survey->delete($_POST);
    $survey->redirectToList();
} elseif (isset($_POST["update"])) {
    $survey->check($_POST['id'], UPDATE);
    $survey->update($_POST);
    Html::back();
} else {
    $survey->checkGlobal(READ);

    Html::header(PluginSatisfactionSurvey::getTypeName(2), '', "admin", "pluginsatisfactionmenu", "survey");

    $survey->display(['id' => $_GET['id']]);

    Html::footer();
}
