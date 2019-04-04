<?php

/**
 * Init the hooks of the plugins -Needed
 */

define('PLUGIN_SATISFACTION_VERSION', '1.4.1');

function plugin_init_satisfaction() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['satisfaction'] = true;
   $PLUGIN_HOOKS['change_profile']['satisfaction'] = ['PluginSatisfactionProfile', 'initProfile'];

   $plugin = new Plugin();
   if ($plugin->isInstalled('satisfaction') && $plugin->isActivated('satisfaction')) {

      $PLUGIN_HOOKS['item_get_datas']['satisfaction'] = ['NotificationTargetTicket' => ['PluginSatisfactionSurveyAnswer',
                                                                                        'addNotificationDatas']];

      //if glpi is loaded
      if (Session::getLoginUserID()) {

         Plugin::registerClass('PluginSatisfactionProfile',
                               ['addtabon' => 'Profile']);

         $PLUGIN_HOOKS['pre_item_form']['satisfaction'] = ['PluginSatisfactionSurveyAnswer', 'displaySatisfaction'];

         $PLUGIN_HOOKS['pre_item_update']['satisfaction']['TicketSatisfaction'] = ['PluginSatisfactionSurveyAnswer',
                                                                                        'preUpdateSatisfaction'];

         //current user must have config rights
         if (Session::haveRight('plugin_satisfaction', READ)) {
            $config_page = 'front/survey.php';
            $PLUGIN_HOOKS['config_page']['satisfaction'] = $config_page;

            $PLUGIN_HOOKS["menu_toadd"]['satisfaction'] = ['admin' => 'PluginSatisfactionMenu'];
         }

         $PLUGIN_HOOKS['add_javascript']['satisfaction'] = ["satisfaction.js"];
      }
   }
}

/**
 * Get the name and the version of the plugin - Needed
 *
 * @return array
 */
function plugin_version_satisfaction() {

   $author = "<a href='www.teclib.com'>TECLIB'</a>";
   $author.= ", <a href='http://blogglpi.infotel.com/'>Infotel</a>";
   return ['name'           => __("More satisfaction", 'satisfaction'),
           'version'        => PLUGIN_SATISFACTION_VERSION,
           'author'         => $author,
           'license'        => 'GPLv2+',
           'homepage'       => 'https://github.com/pluginsGLPI/satisfaction',
           'requirements'   => [
              'glpi' => [
                 'min' => '9.4',
                 'dev' => false
              ]
           ]
   ];
}

/**
 * Optional : check prerequisites before install : may print errors or add to message after redirect
 *
 * @return bool
 */
function plugin_satisfaction_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.4', 'lt')
       || version_compare(GLPI_VERSION, '9.5', 'ge')) {
      if (method_exists('Plugin', 'messageIncompatible')) {
         echo Plugin::messageIncompatible('core', '9.4');
      }
      return false;
   }
   return true;
}

/**
 * Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
 *
 * @return bool
 */
function plugin_satisfaction_check_config() {
   return true;
}
