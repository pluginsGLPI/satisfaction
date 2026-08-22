/**
 * -------------------------------------------------------------------------
 * satisfaction plugin for GLPI
 * Copyright (C) 2018-2026 by the satisfaction Development Team.
 *
 * https://github.com/pluginsGLPI/satisfaction
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of satisfaction.
 *
 * satisfaction is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * satisfaction is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with satisfaction. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

/**
 *
 * @param root_doc
 * @param default_value
 */
function plugin_satisfaction_load_defaultvalue(root_doc, default_value)
{
    var value = $('input[name="default_value"]').val();

    if (value > default_value) {
        value = default_value;
    }

    $.ajax({
        url: root_doc+'/ajax/satisfaction.php',
        type: 'POST',
        data: '&action_default_value&default_value='+ default_value + '&value=' + value,
        dataType: 'html',
        success: function (code_html, statut) {
            $('#default_value').html(code_html);
        },

    });
}