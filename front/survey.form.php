<?php

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkLoginUser();

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$item = new PluginSatisfactionSurvey();

if (isset($_POST["add"])) {
   $item->check(-1, 'w', $_POST);
   $item->add($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $item->check(-1, 'w', $_POST);
   $item->delete($_POST);
   $item->redirectToList();

} else if (isset($_POST["update"])) {
   $item->check($_POST["id"],'w');
   $item->update($_POST);
   Html::back();

} else {
   Html::header($LANG['plugin_satisfaction']['survey']['name'],
                $_SERVER['PHP_SELF'],"plugins","satisfaction","survey");
   $item->showForm($_GET["id"]);
   Html::footer();
}
?>