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

use Glpi\Exception\Http\NotFoundHttpException;
use GlpiPlugin\Satisfaction\Survey;
use GlpiPlugin\Satisfaction\SurveyTranslation;

Session::checkLoginUser();

if (!isset($_POST['survey_id']) || !isset($_POST['action'])) {
    throw new NotFoundHttpException();
}

// Enforce existence, entity scoping and UPDATE right on the targeted survey (anti-IDOR)
$survey = new Survey();
$survey->check((int) $_POST['survey_id'], UPDATE);

$redirection = PLUGINSATISFACTION_WEBDIR."/front/survey.form.php?id=";
$translation = new SurveyTranslation();
switch ($_POST['action']) {
    case 'NEW':
        $translation->newSurveyTranslation($_POST);
        Html::redirect($redirection.$_POST['survey_id']);
        break;

    case 'EDIT':
        $translation->editSurveyTranslation($_POST);
        Html::redirect($redirection.$_POST['survey_id']);
        break;
}
