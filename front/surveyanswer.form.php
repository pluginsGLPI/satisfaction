<?php

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkLoginUser();

$answer = new PluginSatisfactionSurveyAnswer();

if (isset($_POST["add"])) {
   $answer->check(-1,'w',$_POST);
   $answer->add($_POST);
   Html::back();

} else if (isset($_POST["update"])) {
   $answer->check($_POST['id'], 'w');
   $answer->update($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $answer->check($_POST['id'], 'w');
   $answer->delete($_POST);
   Html::back();

}

Html::displayErrorAndDie('Lost');
?>