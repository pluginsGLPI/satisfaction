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

ALTER TABLE `glpi_plugin_satisfaction_surveys` ADD `date_creation` date default NULL;
ALTER TABLE `glpi_plugin_satisfaction_surveys` ADD `date_mod` datetime default NULL;

ALTER TABLE `glpi_plugin_satisfaction_surveyquestions` ADD `type` varchar(255) collate utf8_unicode_ci default NULL;
ALTER TABLE `glpi_plugin_satisfaction_surveyquestions` ADD `comment` text collate utf8_unicode_ci default NULL;
ALTER TABLE `glpi_plugin_satisfaction_surveyquestions` ADD `number` int(11) NOT NULL DEFAULT 0;


ALTER TABLE `glpi_plugin_satisfaction_surveyanswers` ADD `ticketsatisfactions` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `glpi_plugin_satisfaction_surveyanswers` DROP `tickets_id`;
