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

if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
    'atReply',
    'Easily reply to comments',
    'Moe (http://gniark.net/) append extension by buns.fr, Pierre Van Glabeke and contributors',
    '2.0-dev',
    [
        'requires'    => [['core', '2.24']],
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_USAGE,
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]),
        'type'       => 'plugin',
        'support'    => 'http://forum.dotclear.org/viewtopic.php?id=16',
        'details'    => 'https://plugins.dotaddict.org/dc2/details/' . basename(__DIR__),
    ]
);
