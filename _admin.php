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

l10n::set(dirname(__FILE__).'/locales/'.dcCore::app()->lang.'/admin');

dcCore::app()->addBehavior('adminAfterCommentDesc',
	array('AtReplyAdmin','adminAfterCommentDesc'));

# from hum plugin
# admin/comments.php Add actions on comments combo
dcCore::app()->addBehavior('adminCommentsActionsCombo',
	array('AtReplyAdmin','adminCommentsActionsCombo'));
# admin/comments_actions.php Save actions on comments
dcCore::app()->addBehavior('adminCommentsActionsV2',
	array('AtReplyAdmin','adminCommentsActions'));
# /from hum plugin

dcCore::app()->addBehavior('adminBeforeCommentCreate',
	array('AtReplyAdmin','adminBeforeCommentCreate'));

dcCore::app()->addBehavior('adminAfterCommentCreate',
	array('AtReplyAdmin','adminAfterCommentCreate'));

dcCore::app()->menu['Blog']->addItem(__('@ Reply'),'plugin.php?p=atReply',
	'index.php?pf=atReply/icon.png',preg_match('/plugin.php\?p=atReply(&.*)?$/',
		$_SERVER['REQUEST_URI']),dcCore::app()->auth->check('admin',dcCore::app()->blog->id));	

class AtReplyAdmin
{
	/**
	adminAfterCommentDesc behavior
	display information on the admin comment form
	@param	rs	<b>recordset</b>	Recordset
	@return	<b>string</b>	String
	*/
	public static function adminAfterCommentDesc($rs)
	{
		# ignore trackbacks
		if ($rs->comment_trackback == 1) {return;}
		
		# on comment.php, tell the user what to do
		if (strpos($_SERVER['REQUEST_URI'],'comment.php') !== false)
		{
			if (isset($_GET['at_reply_creaco']))
			{
				return('<p class="message">'.__('@ Reply:').' '.
				__('Comment has been successfully created.').' '.
				__('Please edit and publish it.').
				'</p>');
			}
		}
	}

	# from hum plugin
	public static function adminCommentsActionsCombo($args)
	{
		if (dcCore::app()->auth->check('publish,contentadmin',
			dcCore::app()->blog->id))
		{
			$args[0][__('Create a new comment in reply to this comment')] = 'atreply_reply';
		}
	}

	public static function adminCommentsActions($action,$redir)
	{
		# ignore trackbacks
		if ($co->comment_trackback == 1) {return;}
		
		if ($action == 'atreply_reply')
		{
			# reply to one comment, everything is alright
			# Adding comment
			if ($co->count() == 1)
			{
				$post_id = $co->post_id;
				
				try
				{
					$rs = dcCore::app()->blog->getPosts(
						array('post_id' => $post_id,
							'post_type' => ''));
					
					if ($rs->isEmpty()) {
						throw new Exception(__('Entry does not exist.'));
					}
					
					$cur = dcCore::app()->con->openCursor(dcCore::app()->prefix.'comment');
					
					$cur->comment_author = dcCore::app()->auth->getInfo('user_cn');
					$cur->comment_email = html::clean(dcCore::app()->auth->getInfo('user_email'));
					$cur->comment_site = html::clean(dcCore::app()->auth->getInfo('user_url'));
					$cur->comment_content = dcCore::app()->HTMLfilter(
						'<p>'.
							sprintf(__('@%s:'),'<a href="'.
							$rs->getURL().'#c'.
							html::escapeHTML($co->comment_id).'">'.
							html::escapeHTML($co->comment_author).'</a>').
						' </p>'
					);
					
					$cur->comment_status = -1;
					
					$cur->post_id = (integer) $post_id;
					
					# --BEHAVIOR-- adminBeforeCommentCreate
					dcCore::app()->callBehavior('adminBeforeCommentCreate',$cur);
					
					$comment_id = dcCore::app()->blog->addComment($cur);
					
					# --BEHAVIOR-- adminAfterCommentCreate
					dcCore::app()->callBehavior('adminAfterCommentCreate',$cur,$comment_id);
					
					if ((dcCore::app()->blog->settings->atreply_subscribe_replied_comment == true)
						&& (dcCore::app()->plugins->moduleExists('subscribeToComments')))
					{
						# subscribe the email address of the replied comment
						$subscriber = new subscriber($co->comment_email);
						$subscriber->subscribe($cur->post_id);
					}
					
					http::redirect('comment.php?id='.$comment_id.'&at_reply_creaco=1');
				} catch (Exception $e) {
					dcCore::app()->error->add($e->getMessage());
				}
			}
			
			if (!dcCore::app()->error->flag()) {
				http::redirect($redir);
			}
		}
	}
	# /from hum plugin
}

// Admin dashbaord favorite
dcCore::app()->addBehavior('adminDashboardFavoritesV2', function ($favs) {
    $favs->register(basename(__DIR__), [
        'title'       => __('@ Reply'),
        'url'         => dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)),
        'small-icon'  => dcPage::getPF(basename(__DIR__) . '/icon.png'),
        'large-icon'  => dcPage::getPF(basename(__DIR__) . '/icon-big.png'),
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_USAGE,
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]),
    ]);
});
