<?php

include('../../../inc/includes.php');

if (!isset($_POST['survey_id']) || !isset($_POST['action'])) {
    exit();
}

$redirection = Plugin::getWebDir('satisfaction')."/front/survey.form.php?id=";
$translation = new PluginSatisfactionSurveyTranslation();
switch($_POST['action']){
    case 'NEW':
       $translation->newSurveyTranslation($_POST);
       Html::redirect($redirection.$_POST['survey_id']);
       break;

    case 'EDIT':
       $translation->editSurveyTranslation($_POST);
       Html::redirect($redirection.$_POST['survey_id']);
       break;
}
