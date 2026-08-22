--
-- -------------------------------------------------------------------------
-- satisfaction plugin for GLPI
-- Copyright (C) 2018-2026 by the satisfaction Development Team.
--
-- https://github.com/pluginsGLPI/satisfaction
-- -------------------------------------------------------------------------
--
-- LICENSE
--
-- This file is part of satisfaction.
--
-- satisfaction is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- satisfaction is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with satisfaction. If not, see <http://www.gnu.org/licenses/>.
-- --------------------------------------------------------------------------
--

CREATE TABLE `glpi_plugin_satisfaction_surveys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL default '0',
  `is_active` tinyint(1) NOT NULL default '0',
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `comment` text collate utf8_unicode_ci default NULL,
  `date_creation` date default NULL,
  `date_mod` datetime default NULL,
  PRIMARY KEY (`id`)
) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `glpi_plugin_satisfaction_surveyquestions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_satisfaction_surveys_id` int(11) NOT NULL,
  `name` text collate utf8_unicode_ci default NULL,
  `type` varchar(255) collate utf8_unicode_ci default NULL,
  `comment` text collate utf8_unicode_ci default NULL,
  `number` int(11) NOT NULL DEFAULT 0,
  `default_value` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `glpi_plugin_satisfaction_surveyanswers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `answer` text collate utf8_unicode_ci default NULL,
  `comment` text collate utf8_unicode_ci default NULL,
  `plugin_satisfaction_surveys_id` int(11) NOT NULL,
  `ticketsatisfactions_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;