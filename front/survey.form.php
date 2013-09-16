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

   if (isset($_POST["item"]) && count($_POST["item"])) {
      foreach ($_POST["item"] as $key => $val) {
         if ($val == 1) {
            if ($item->can($key, 'w')) {
               $item->delete(array('id' => $key));
            }
         }
      }
   }
   Html::back();

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