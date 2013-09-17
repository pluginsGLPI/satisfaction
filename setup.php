<?php
// Init the hooks of the plugins -Needed
function plugin_init_satisfaction() {
   global $PLUGIN_HOOKS,$CFG_GLPI,$LANG;
    
   $PLUGIN_HOOKS['csrf_compliant']['satisfaction'] = true;
   
   $plugin = new Plugin();
   if ($plugin->isInstalled('satisfaction') && $plugin->isActivated('satisfaction')) {
       
      //if glpi is loaded
      if (Session::getLoginUserID()) {

         Plugin::registerClass('PluginSatisfactionSurveyAnswer',
                         array('addtabon' => array('Ticket')));

         $PLUGIN_HOOKS['add_javascript']['satisfaction'][] = 'scripts/tabs.js.php';

         //current user must have config rights
         if (Session::haveRight('config', 'w')) {
            $config_page = 'front/survey.php';
            $PLUGIN_HOOKS['config_page']['satisfaction'] = $config_page;

            $PLUGIN_HOOKS['menu_entry']['satisfaction'] = 'front/survey.php';
            $PLUGIN_HOOKS['submenu_entry']['satisfaction']['options']['survey']['title'] = "Survey";
            $PLUGIN_HOOKS['submenu_entry']['satisfaction']['options']['survey']['page']  
                           = '/plugins/satisfaction/front/survey.php';
            $PLUGIN_HOOKS['submenu_entry']['satisfaction']['options']['survey']['links']['search'] 
                           = '/plugins/satisfaction/front/survey.php';
            $PLUGIN_HOOKS['submenu_entry']['satisfaction']['options']['survey']['links']['add']    
                           = '/plugins/satisfaction/front/survey.form.php';
         }
      }
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_satisfaction() {
   global $LANG;

   $author = "<a href='www.teclib.com'>TECLIB'</a>";
   return array ('name' => "I can get more ... Satisfaction",
                 'version' => '0.83+1.0',
                 'author' => $author,
                 'homepage' => 'www.teclib.com',
                 'minGlpiVersion' => '0.83.3');
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_satisfaction_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.83.3','lt') || version_compare(GLPI_VERSION,'0.84','ge')) {
      echo "This plugin requires GLPI 0.83.3+";
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_satisfaction_check_config() {
   return true;
}
