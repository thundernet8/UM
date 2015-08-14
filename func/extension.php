<?php

/**
 * Main Functions of Ucenter & Market WordPress Plugin
 *
 * @package   Ucenter & Market
 * @version   1.0
 * @date      2015.4.6
 * @author    Zhiyan <chinash2010@gmail.com>
 * @site      Zhiyanblog <www.zhiyanblog.com> | Dmeng <www.dmeng.net>
 * @copyright Copyright (c) 2015-2015, Zhiyan&Dmeng
 * @license   http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link      http://www.zhiyanblog.com/wordpress-plugin-ucenter-and-market.html
**/

?>
<?php
// About author module
function um_author_info_module(){
	global $post;
	$pid = get_the_ID();
	$author = get_post_field('post_author',$pid);
	$author_info = get_userdata($author);
	$sinawb = $author_info->um_sina_weibo;
	$qqwb	= $author_info->um_qq_weibo;
	$weixin = $author_info->um_weixin;
	$qq	 	= $author_info->um_qq;
	$twitter	 	= $author_info->um_twitter;
	$googleplus	 	= $author_info->um_googleplus;
	$donate = $author_info->um_donate;
	$alipay_email = $author_info->um_alipay_email;
	$author_home = $author_info->user_url;
?>
<div class="sg-author clr">
	<div class="img"><?php echo um_get_avatar( $author , '100' , um_get_avatar_type($author) ); ?></div>
	<div class="sg-author-info">
		<div class="word">
			<div class="wordname"><?php _e('关于','um');the_author_posts_link(); ?></div>
			<div class="authordes"><?php the_author_meta('description'); ?></div>
			<div class="authorsocial">
			<?php if(!empty($author_home)){ ?>
			<span class="social-icon-wrap"><a class="as-img as-home" href="<?php echo $author_home; ?>" title="<?php _e('作者主页','um'); ?>"><i class="fa fa-home"></i><?php _e('作者主页','tinection'); ?></a></span>
			<?php } ?>
			<?php if(!empty($donate)){ ?>
			<span class="social-icon-wrap"><a class="as-img as-donate" href="#" title="<?php _e('赞助作者','um'); ?>"><i class="fa fa-coffee"></i><?php _e('赞助作者','um'); ?>
				<div id="as-donate-qr" class="as-qr"><img src="<?php echo $donate; ?>" title="<?php _e('手机支付宝扫一扫赞助作者','um'); ?>" /></div>
			</a><?php echo um_alipay_post_gather($alipay_email,1,1); ?></span>
			<?php } ?>
			<?php if(!empty($sinawb)){ ?>
			<span class="social-icon-wrap"><a class="as-img as-sinawb" href="http://weibo.com/<?php echo $sinawb; ?>" title="<?php _e('微博','um'); ?>"><i class="fa fa-weibo"></i></a></span>
			<?php } ?>
			<?php if(!empty($qqwb)){ ?>
			<span class="social-icon-wrap"><a class="as-img as-qqwb" href="http://t.qq.com/<?php echo $qqwb; ?>" title="<?php _e('腾讯微博','um'); ?>"><i class="fa fa-tencent-weibo"></i></a></span>
			<?php } ?>
			<?php if(!empty($twitter)){ ?>
			<span class="social-icon-wrap"><a class="as-img as-twitter" href="https://twitter.com/<?php echo $twitter; ?>" title="Twitter"><i class="fa fa-twitter"></i></a></span>
			<?php } ?>
			<?php if(!empty($googleplus)){ ?>
			<span class="social-icon-wrap"><a class="as-img as-googleplus" href="<?php echo $googleplus; ?>" title="Google+"><i class="fa fa-google-plus"></i></a></span>
			<?php } ?>
			<?php if(!empty($weixin)){ ?>
			<span class="social-icon-wrap"><a class="as-img as-weixin" href="#" id="as-weixin-a" title="<?php _e('微信','um'); ?>"><i class="fa fa-weixin"></i>
				<div id="as-weixin-qr" class="as-qr"><img src="<?php echo $weixin; ?>" title="<?php _e('微信扫描二维码加我为好友并交谈','um'); ?>" /></div>
			</a></span>		
			<?php } ?>
			<?php if(!empty($qq)){ ?>
			<span class="social-icon-wrap"><a class="as-img as-qq" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $qq; ?>&site=qq&menu=yes" title="<?php _e('QQ交谈','um'); ?>"><i class="fa fa-qq"></i></a></span>
			<?php } ?>
			<span class="social-icon-wrap"><a class="as-img as-email" href="mailto:<?php the_author_meta('email'); ?>" title="<?php _e('给我写信','um'); ?>"><i class="fa fa-envelope"></i></a></span>					
			</div>
			</div>
	</div>
</div>
<div class="clear"></div>


<?php
}