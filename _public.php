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

dcCore::app()->addBehavior('templateBeforeValueV2',array('AtReplyTpl','templateBeforeValue'));
dcCore::app()->addBehavior('templateAfterValueV2',array('AtReplyTpl','templateAfterValue'));

dcCore::app()->blog->settings->addNameSpace('atreply');

if (dcCore::app()->blog->settings->atreply->atreply_active)
{
	dcCore::app()->addBehavior('publicHeadContent',array('AtReplyTpl','publicHeadContent'));
	dcCore::app()->addBehavior('publicCommentBeforeContent',array('AtReplyTpl','publicCommentBeforeContent'));
}

class AtReplyTpl
{
	public static function templateBeforeValue($v,$attr)
	{
		if ($v == 'CommentAuthorLink')
		{
			return('<span class="commentAuthor" '.
				'id="atreply_<?php echo dcCore::app()->ctx->comments->comment_id; ?>" '.
				'title="<?php echo(html::escapeHTML(dcCore::app()->ctx->comments->comment_author)); ?>">');
		}
	}

	public static function templateAfterValue($v,$attr)
	{
		if ($v == 'CommentAuthorLink')
		{
			return('</span>');
		}
	}
	
	public static function publicHeadContent()
	{
		$set = dcCore::app()->blog->settings->atreply;
		
		$QmarkURL = dcCore::app()->blog->getQmarkURL();
		
		# personalized image
		if ((strlen($set->atreply_color) > 1)
			&& (file_exists(dcCore::app()->blog->public_path.'/atReply/reply.png')))
		{
			$image_url = dcCore::app()->blog->settings->system->public_url.
				'/atReply/reply.png';
		}
		else
		{
			# default image
			$image_url = $QmarkURL.'pf=atReply/img/reply.png';
		}
		
		$title = (($set->atreply_display_title) ? '1' : '0');
		
		# Javascript
		echo(
			'<script type="text/javascript">'."\n".
			'//<![CDATA['."\n".
			'var atReplyDisplayTitle = '.$title.';'."\n".
			'var atReplyTitle = \''.
				html::escapeHTML(__('Reply to comment {author}')).'\';'."\n".
			'var atReplyImage = \''.$image_url.'\';'."\n".
			'var atReply_switch_text = \''.
				html::escapeHTML(__('Threaded comments')).'\';'."\n".
			'var atreply_append = '.($set->atreply_append ? '1' : '0').';'."\n".
			'var atreply_show_switch = '.($set->atreply_show_switch ? '1' : '0').';'."\n".
			'//]]>'."\n".
			'</script>'."\n"
		);
		
		if ($set->atreply_append)
		{
			echo ( 
				'<script type="text/javascript" src="'.$QmarkURL.
				'pf=atReply/js/atReplyThread.js'.'"></script>'."\n".
				'<style type="text/css">
				<!--
				#atReplySwitch {
					margin:20px 10px 0 0;
					padding:0;
					float:right;	
					color:#999999;
					font-style:italic;
				}
				.repliedCmt, .replyCmt {
					border-left: 1px solid #666; 
				}
				dd.repliedCmt, dd.replyCmt  {
					border-bottom: 1px solid #666;
				}
				-->
				</style>'."\n"
			);
		}
		
		echo('<script type="text/javascript" src="'.$QmarkURL.
			'pf=atReply/js/atReply.js'.'"></script>'."\n");
	}
	
	public static function publicCommentBeforeContent()
	{
			echo '<span id="atReplyComment'.dcCore::app()->ctx->comments->f('comment_id').
				'" style="display:none;"></span>';
	}
}
