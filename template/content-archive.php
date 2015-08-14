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
<article class="archive clr">
<h3>
	<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
</h3>
<div class="archive-excerpt">
<?php the_excerpt();?>
</div>
<?php if( $post->post_status=='publish' ){ ?>		
<div class="postlist-meta">
		<?php if(isset($_GET['collect'])){ ?>
		<div class="postlist-meta-author"><i class="fa fa-user"></i>&nbsp;<?php the_author();?></div>
		<?php } ?>
		<div class="postlist-meta-time"><i class="fa fa-calendar"></i>&nbsp;<?php echo date(__('Y年m月j日','um'),get_the_time('U'));?></div>
		<div class="postlist-meta-views"><i class="fa fa-fire"></i>&nbsp;<?php echo (int)get_post_meta(get_the_ID(),'um_post_views',true); ?></div>
		<div class="postlist-meta-category"><i class="fa fa-folder-open"></i>&nbsp;<?php the_category(' '); ?></div>
		<div class="postlist-meta-comments"><?php if ( comments_open() ): ?><i class="fa fa-comments"></i>&nbsp;<a href="<?php comments_link(); ?>"><?php comments_number( '0', '1', '%' ); ?></a><?php  endif; ?></div>
		<?php include(UM_DIR.'template/action-meta.php'); ?>
</div>
<?php } ?>
<?php if( $post->post_status!='publish' ){ 
	$meta_output = '<div class="entry-meta">';
		if( $post->post_status==='pending' ) $meta_output .= sprintf(__('正在等待审核，你可以 <a href="%1$s">预览</a> 或 <a href="%2$s">重新编辑</a> 。','tinection'), get_permalink(), get_edit_post_link() );
		if( $post->post_status==='draft' ) $meta_output .= sprintf(__('这是一篇草稿，你可以 <a href="%1$s">预览</a> 或 <a href="%2$s">继续编辑</a> 。','tinection'), get_permalink(), get_edit_post_link() );
		$meta_output .= '</div>';
		echo $meta_output;
} ?>
</article>