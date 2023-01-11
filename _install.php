<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of @ Reply, a plugin for Dotclear 2
# Copyright (C) 2008,2009,2010,2011 Moe (http://gniark.net/) and buns
#
# @ Reply is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# @ Reply is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# Big icon (icon-big.png) come from Dropline Neu! :
# <http://art.gnome.org/themes/icon?sort=popularity>
#
# Inspired by @ Reply for WordPress :
# <http://iyus.info/at-reply-petit-plugin-wordpress-inspire-par-twitter/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (!dcCore::app()->newVersion(basename(__DIR__), dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version'))) {
    return;
}

# delete this setting which was saved in the wrong namespace
dcCore::app()->con->execute('DELETE FROM '.$core->prefix.'setting '.
		'WHERE (setting_ns = \'atreply\') '.
		'AND (setting_id = \'wiki_comments\');');

return true;
