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

CREATE TABLE `glpi_plugin_satisfaction_surveyreminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_satisfaction_surveys_id` int(11) NOT NULL,
  `name` text collate utf8_unicode_ci default NULL,
  `duration_type` int(11) NOT NULL,
  `duration` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL default '0',
  `comment` text collate utf8_unicode_ci default NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `glpi_plugin_satisfaction_reminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL DEFAULT '0',
  `tickets_id` int(11) NOT NULL,
  `date` date default NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;