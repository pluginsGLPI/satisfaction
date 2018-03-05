<?php

include('../../../inc/includes.php');

Html::header(PluginSatisfactionSurvey::getTypeName(2), '', "plugins", "pluginsatisfactionmenu");

$satisfaction = new PluginSatisfactionSurvey();
$satisfaction->checkGlobal(READ);

if ($satisfaction->canView()) {
   Search::show('PluginSatisfactionSurvey');

} else {
   Html::displayRightError();
}

Html::footer();
