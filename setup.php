<?php

/**
 * Init the hooks of the plugins -Needed
 */
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

            $PLUGIN_HOOKS["menu_toadd"]['satisfaction'] = ['plugins' => 'PluginSatisfactionMenu'];
         }
      }
   }
}

/**
 * Get the name and the version of the plugin - Needed
 *
 * @return array
 */
function plugin_version_satisfaction() {

   $author = "<a href='www.teclib.com'>TECLIB'</a><a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>";
   return ['name'           => __("I can get more ... Satisfaction", 'satisfaction'),
                'version'        => '1.1.0',
                'author'         => $author,
                'license'        => 'GPLv2+',
                'homepage'       => 'https://github.com/pluginsGLPI/satisfaction',
                'minGlpiVersion' => '9.1'];
}

/**
 * Optional : check prerequisites before install : may print errors or add to message after redirect
 *
 * @return bool
 */
function plugin_satisfaction_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.1', 'lt') || version_compare(GLPI_VERSION, '9.2', 'ge')) {
      echo __('This plugin requires GLPI >= 9.1', 'satisfaction');
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
