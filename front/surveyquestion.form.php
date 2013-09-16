<?php

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkLoginUser();

$question = new PluginSatisfactionSurveyQuestion();

if (isset($_POST["add"])) {
   $question->check(-1,'w',$_POST);
   $question->add($_POST);
   Html::back();

} else if (isset($_POST["update"])) {
   $question->check($_POST['id'], 'w');
   $question->update($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $question->check($_POST['id'], 'w');
   $question->delete($_POST);
   Html::back();

}

Html::displayErrorAndDie('Lost');
?>