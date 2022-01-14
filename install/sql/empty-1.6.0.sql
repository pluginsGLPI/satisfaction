# noinspection SqlNoDataSourceInspectionForFile

CREATE TABLE `glpi_plugin_satisfaction_surveys` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint NOT NULL default '0',
  `is_active` tinyint NOT NULL default '0',
  `name` varchar(255) collate utf8mb4_unicode_ci default NULL,
  `comment` text collate utf8mb4_unicode_ci default NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
  `reminders_days` int unsigned NOT NULL default '30',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_satisfaction_surveyquestions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `plugin_satisfaction_surveys_id` int unsigned NOT NULL,
  `name` text collate utf8mb4_unicode_ci default NULL,
  `type` varchar(255) collate utf8mb4_unicode_ci default NULL,
  `comment` text collate utf8mb4_unicode_ci default NULL,
  `number` int unsigned NOT NULL DEFAULT 0,
  `default_value` int unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_satisfaction_surveyanswers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `answer` text collate utf8mb4_unicode_ci default NULL,
  `comment` text collate utf8mb4_unicode_ci default NULL,
  `plugin_satisfaction_surveys_id` int unsigned NOT NULL,
  `ticketsatisfactions_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `glpi_plugin_satisfaction_surveytranslations`;
CREATE TABLE `glpi_plugin_satisfaction_surveytranslations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `plugin_satisfaction_surveys_id` int unsigned NOT NULL DEFAULT '0',
  `glpi_plugin_satisfaction_surveyquestions_id` int unsigned NOT NULL DEFAULT '0',
  `language` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_satisfaction_surveys_id`,`glpi_plugin_satisfaction_surveyquestions_id`,`language`),
  KEY `typeid` (`plugin_satisfaction_surveys_id`,`glpi_plugin_satisfaction_surveyquestions_id`),
  KEY `language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `glpi_plugin_satisfaction_surveyreminders`;
CREATE TABLE `glpi_plugin_satisfaction_surveyreminders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `plugin_satisfaction_surveys_id` int unsigned NOT NULL,
  `name` text collate utf8mb4_unicode_ci default NULL,
  `duration_type` int unsigned NOT NULL,
  `duration` int unsigned NOT NULL,
  `is_active` tinyint NOT NULL default '0',
  `comment` text collate utf8mb4_unicode_ci default NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `glpi_plugin_satisfaction_reminders`;
CREATE TABLE `glpi_plugin_satisfaction_reminders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` int unsigned NOT NULL DEFAULT '0',
  `tickets_id` int unsigned NOT NULL,
  `date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`tickets_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
