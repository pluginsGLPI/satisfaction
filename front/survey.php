<?php

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

Html::header($LANG['plugin_satisfaction']['survey']['name'],
             $_SERVER['PHP_SELF'],"plugins","satisfaction","survey");
Search::show('PluginSatisfactionSurvey');
Html::footer();
?>