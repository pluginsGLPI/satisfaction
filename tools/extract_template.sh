#!/bin/bash

#
# -------------------------------------------------------------------------
# satisfaction plugin for GLPI
# Copyright (C) 2018-2026 by the satisfaction Development Team.
#
# https://github.com/pluginsGLPI/satisfaction
# -------------------------------------------------------------------------
#
# LICENSE
#
# This file is part of satisfaction.
#
# satisfaction is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# satisfaction is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with satisfaction. If not, see <http://www.gnu.org/licenses/>.
# --------------------------------------------------------------------------
#

find . -name '*.php' > php_files.list

# Extraction avec xgettext
xgettext --files-from=php_files.list \
  --copyright-holder='Satisfaction Development Team' \
  --package-name='Satisfaction plugin' \
  -o locales/glpi.pot \
  -L PHP \
  --add-comments=TRANS \
  --from-code=UTF-8 \
  --force-po \
  --keyword=_n:1,2,4t \
  --keyword=__s:1,2t \
  --keyword=__:1,2t \
  --keyword=_e:1,2t \
  --keyword=_x:1c,2,3t \
  --keyword=_ex:1c,2,3t \
  --keyword=_nx:1c,2,3,5t \
  --keyword=_sx:1c,2,3t

# Nettoyage
rm php_files.list

