<?php
/**
 * @brief atReply, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Moe, Pierre Van Glabeke and contributors
 *
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Icon (icon.png) and images are from Silk Icons :
 * <http://www.famfamfam.com/lab/icons/silk/>
 *
 * Big icon (icon-big.png) come from Dropline Neu! :
 * <http://art.gnome.org/themes/icon?sort=popularity>
 *
 * Inspired by atReply for WordPress :
 * <http://iyus.info/at-reply-petit-plugin-wordpress-inspire-par-twitter/>
 */

if (!defined('DC_CONTEXT_ADMIN')) {return;}

# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (!dcCore::app()->newVersion(basename(__DIR__), dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version'))) {
    return;
}

# delete this setting which was saved in the wrong namespace
dcCore::app()->con->execute('DELETE FROM '.dcCore::app()->prefix.'setting '.
		'WHERE (setting_ns = \'atreply\') '.
		'AND (setting_id = \'wiki_comments\');');

return true;
