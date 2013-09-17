<?php
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

//change mimetype
header("Content-type: application/javascript");


if (PluginSatisfactionSurvey::getObjectForEntity($_SESSION['glpiactive_entity']) === false) {
   exit;
}

// REMOVE NATIVE SATISFACTION TAB

$JS = <<<JAVASCRIPT
Ext.onReady(function() {
   // only in ticket form
   if (location.pathname.indexOf('ticket.form.php') > 0) {
      Ext.select('#'+tabpanel.id+'__Ticket\\\\$3').remove();
   }
});
JAVASCRIPT;
echo $JS;
