<?php
/**
 * Main Template of Ucenter & Market WordPress Plugin
 *
 * @package   Ucenter & Market
 * @version   1.0
 * @date      2015.4.6
 * @author    Zhiyan <chinash2010@gmail.com>
 * @site      Zhiyanblog <www.zhiyanblog.com>
 * @copyright Copyright (c) 2015-2015, Zhiyan
 * @license   http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link      http://www.zhiyanblog.com/wordpress-plugin-ucenter-and-market.html
**/

?>
<?php $umlikes=get_post_meta($post->ID,'um_post_likes',true); $umcollects=get_post_meta($post->ID,'um_post_collects',true); if(empty($umlikes)):$umlikes=0; endif;if(empty($umcollects)):$umcollects=0; endif;$like_cookie = 'um_post_like_'.$post->ID;?>
	<div class="postlist-meta-like like-btn<?php if(isset($_COOKIE[$like_cookie])) echo ' love-yes'; ?>" style="float:right;" pid="<?php echo $post->ID ; ?>" title="<?php _e('点击喜欢','um'); ?>"><i class="fa fa-heart"></i>&nbsp;<span><?php echo $umlikes; ?></span>&nbsp;</div>
	<?php $uid = get_current_user_id(); if(!empty($uid)&&$uid!=0){ ?>		
		<?php $mycollects = get_user_meta($uid,'um_collect',true);
			$mycollects = explode(',',$mycollects);
		?>
		<?php global $curauth; ?>
		<?php if (!in_array($post->ID,$mycollects)){ ?>
		<div class="postlist-meta-collect collect-btn collect-no" style="float:right;" pid="<?php echo $post->ID ; ?>" uid="<?php echo get_current_user_id(); ?>" title="<?php _e('点击收藏','um'); ?>"><i class="fa fa-star"></i>&nbsp;<span><?php echo $umcollects; ?></span>&nbsp;</div>
		<?php }elseif(isset($curauth->ID)&&$curauth->ID==$uid){ ?>
		<div class="postlist-meta-collect collect-btn collect-yes remove-collect" style="float:right;cursor:pointer;" pid="<?php echo $post->ID ; ?>" uid="<?php echo get_current_user_id(); ?>" title="<?php _e('取消收藏','um'); ?>"><i class="fa fa-star"></i>&nbsp;<span><?php echo $umcollects; ?></span>&nbsp;</div>
		<?php }else{ ?>
		<div class="postlist-meta-collect collect-btn collect-yes" style="float:right;cursor:default;" uid="<?php echo get_current_user_id(); ?>" title="<?php _e('你已收藏','um'); ?>"><i class="fa fa-star"></i>&nbsp;<span><?php _e('已收藏','um'); ?></span>&nbsp;</div>
		<?php } ?>
		<?php }else{ ?>
		<div class="postlist-meta-collect collect-btn collect-no" style="float:right;cursor:default;" title="<?php _e('必须登录才能收藏','um'); ?>"><i class="fa fa-star"></i>&nbsp;<span><?php echo $umcollects; ?></span>&nbsp;</div>
<?php } ?>