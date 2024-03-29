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

if (!dcCore::app()->auth->check('admin',dcCore::app()->blog->id))
{
	echo('<html><head><title>Error</title></head>'.
		'<body><p class="error">'.
		__('Invalid permission.').
		'</p></body></html>');
	return;
}

$page_title = __('@ Reply');

dcCore::app()->blog->settings->addNameSpace('atreply');

$settings =& dcCore::app()->blog->settings->atreply;

try
{
	if (!empty($_POST['saveconfig']))
	{
		$active = $settings->atreply_active;
		
		$color = trim($_POST['atreply_color']);
		
		$settings->put('atreply_active',!empty($_POST['atreply_active']),
			'boolean','Enable @ Reply');
		if (!empty($_POST['atreply_active']))
		{
			# from commentsWikibar/index.php
			dcCore::app()->blog->settings->system->put('wiki_comments',true,'boolean');
		}
		
		$settings->put('atreply_display_title',!empty($_POST['atreply_display_title']),
			'boolean','Display a text when the cursor is hovering the arrow');
		$settings->put('atreply_color',$color,
			'string','@ Reply arrow\'s color');
		$settings->put('atreply_append',!empty($_POST['atreply_append']),
			'boolean','Append replies to appropriate comments');
		$settings->put('atreply_show_switch',!empty($_POST['atreply_show_switch']),
			'boolean','Display a switch to toggle threading');

		$settings->put('atreply_subscribe_replied_comment',
			!empty($_POST['atreply_subscribe_replied_comment']),
			'boolean','Subscribe replied comments to "Subscribe to comments"');
		
		# if there is a color
		if (!empty($color))
		{
			# create the image
			
			# inspired by blowupConfig/lib/class.blowup.config.php
			$color = sscanf($color,'#%2X%2X%2X');
	
			$red = $color[0];
			$green = $color[1];
			$blue = $color[2];	
	
			$dir = path::real(dcCore::app()->blog->public_path.'/atReply',false);
			files::makeDir($dir,true);
			$file_path = $dir.'/reply.png';
	
			# create the image
			$img = imagecreatefrompng(dirname(__FILE__).'/img/transparent_16x16.png');
	
			$source = imagecreatefrompng(dirname(__FILE__).'/img/reply.png');
			imagealphablending($source,true);
	
			# copy image pixel per pixel, changing color but not the alpha channel
			for ($x=0;$x <= 15;$x++)
			{
				for ($y=0;$y <= 15;$y++)
				{
					$rgb = imagecolorat($source,$x,$y);
					$rgba = $rgb;
					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;
	
					# alpha is an undocumented feature, see
					# http://php.net/manual/en/function.imagecolorat.php#79116
					$alpha = ($rgba & 0x7F000000) >> 24;
	
					if ($r > 0)
					{
						imageline($img,$x,$y,$x,$y,
							imagecolorallocatealpha($img,$red,$green,$blue,$alpha));
					}
				}
			}
			
			imagedestroy($source);
	
			imagesavealpha($img,true);
			if (is_writable($dir))
			{
				imagepng($img,$file_path);
			}
			else
			{
				throw new Exception(sprintf(__('%s is not writable'),$dir));
			}
			imagedestroy($img);
		}
		
		# only update the blog if the setting have changed
		if ($active == empty($_POST['atreply_active']))
		{
			dcCore::app()->blog->triggerBlog();
			
			# delete the cache directory
			dcCore::app()->emptyTemplatesCache();
		}
		
		http::redirect(dcCore::app()->admin->getPageURL().'&saveconfig=1');
	}
}
catch (Exception $e)
{
	dcCore::app()->error->add($e->getMessage());
}

if (isset($_GET['saveconfig']))
{
	$msg = __('Configuration successfully updated.');
}

$image_url = dcCore::app()->blog->getQmarkURL().'pf=atReply/img/reply.png';

$system = dcCore::app()->blog->settings->system;

# personalized image
if (strlen($settings->atreply_color) > 1)
{
	$personalized_image = $system->public_url.
		'/atReply/reply.png'.'?time='.time();
	
	if (file_exists(path::fullFromRoot($system->public_path,
		DC_ROOT).'/atReply/reply.png'))
	{
		$image_url = $personalized_image;
		
		if (substr($system->public_url,0,1) == '/')
		{
			# public_path is located at the root of the website
			$image_url = dcCore::app()->blog->host.'/'.$personalized_image;
		}
		else if (substr($system->public_url,0,4) == 'http')
		{
			$image_url = $personalized_image;
		}
		else
		{
			$image_url = dcCore::app()->blog->url.$personalized_image;
		}
	}
}

?><html>
<head>
	<title><?php echo($page_title); ?></title>
	<?php echo(dcPage::jsColorPicker()); ?>
</head>
<body>
	
	<?php
	if (is_callable(array('dcPage', 'breadcrumb')))
	{
		echo dcPage::breadcrumb(
			array(
				html::escapeHTML(dcCore::app()->blog->name) => '',
				'<span class="page-title">'.$page_title.'</span>' => ''
			));
	}
	else
	{
		echo('<h2>'.html::escapeHTML(dcCore::app()->blog->name).' &rsaquo; '.
			$page_title.'</h2>');
	}
	?>
	
	<?php 
		if (!empty($msg))
		{
			if (is_callable(array('dcPage', 'success')))
			{
				dcPage::success($msg);
			}
			else
			{
				dcPage::message($msg);
			}
		}
	?>
	
	<form method="post" action="<?php echo dcCore::app()->admin->getPageURL(); ?>">
		<div class="fieldset"><h4><?php echo(__('Activation')); ?></h4>
    <p><?php echo(form::checkbox('atreply_active',1,
			$settings->atreply_active)); ?>
			<label class="classic" for="atreply_active">
				<?php echo(__('Add arrows to easily reply to comments on the blog')); ?>
			</label>
		</p>
		<p class="info">
			<?php
				# from commentsWikibar/index.php
				echo(' '.__('Activating this plugin also enforces wiki syntax in blog comments.')); ?>
		</p>
		</div>
		
		<div class="fieldset"><h4><?php echo(__('Settings')); ?></h4>
		<p><?php echo(form::checkbox('atreply_display_title',1,
			$settings->atreply_display_title)); ?>
			<label class="classic" for="atreply_display_title">
				<?php echo(__('Display a text when the cursor is hovering the arrow')); ?>
			</label>
		</p>
		
		<p><?php echo(form::checkbox('atreply_append',1,
			$settings->atreply_append)); ?>
			<label class="classic" for="atreply_append">
				<?php echo(__('Append replies to appropriate comments')); ?>
			</label>
		</p>
		
		<p><?php echo(form::checkbox('atreply_show_switch',1,
			$settings->atreply_show_switch)); ?>
			<label class="classic" for="atreply_show_switch">
				<?php echo(__('Display a switch to toggle threading')); ?>
			</label>
		</p>
		<p class="info">
			<?php printf(__('Requires "%s".'),
				__('Append replies to appropriate comments')); ?>
		</p>

		<p><?php echo(form::checkbox('atreply_subscribe_replied_comment',1,
			$settings->atreply_subscribe_replied_comment)); ?>
			<label class="classic" for="atreply_subscribe_replied_comment">
				<?php printf(__('When clicking on the "%s" button in a comment list of the administration, subscribe to comments the email address of the replied comment with the %s plugin'),
					__('Reply to this comment'),__('Subscribe to comments')); ?>
			</label>
		</p>
		<p class="info">
			<?php printf(__('Requires the %s plugin.'),
				__('Subscribe to comments')); ?>
		</p>
		
		<p>
			<label class="classic" for="atreply_color">
				<?php echo(__('Create an image with another color')); ?>
			</label>
			<?php echo(form::field('atreply_color',7,7,
				$settings->atreply_color,'colorpicker')); ?>
		</p>
		<p class="info">
			<?php echo(__('Type the color code in hexadecimal, for example for red: #ff0000.').'<br />'.
				__('Leave blank to disable this feature.').' '.
				__('The default image will be used.')); ?>
		</p>
		
		<?php echo('<p>'.__('Preview:').' <img src="'.$image_url.
			'" alt="reply.png" /></p>'); ?>
		
		<p class="info"><?php echo(__('Visitors may see the old image if their browser still use it.')); ?></p>
		</div>
		
		<p><?php echo dcCore::app()->formNonce(); ?></p>
		<p><input type="submit" name="saveconfig" value="<?php echo __('Save'); ?>" /></p>
	</form>

</body>
</html>
