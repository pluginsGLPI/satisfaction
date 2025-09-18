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

Session::checkRight('plugin_satisfaction', UPDATE);

if (!isset($_POST['survey_id']) || !isset($_POST['action'])) {
    	throw new \Glpi\Exception\Http\NotFoundHttpException();
}

global $CFG_GLPI;
$redirection = PLUGINSATISFACTION_WEBDIR."/front/survey.form.php?id=";

$translation = new PluginSatisfactionSurveyTranslation();

switch($_POST['action']){
   case 'GET':
      header("Content-Type: text/html; charset=UTF-8");
      Html::header_nocache();
      Session::checkLoginUser();
      $translation->showSurveyTranslationForm($_POST);
      break;
   case 'NEW':
      $translation->newSurveyTranslation($_POST);
      Html::redirect($redirection.$_POST['survey_id']);
      break;
   case 'EDIT':
      $translation->editSurveyTranslation($_POST);
      Html::redirect($redirection.$_POST['survey_id']);
      break;
}
