<?php

/**
 * Main Template of Ucenter & Market WordPress Plugin
 *
 * @package   Ucenter & Market
 * @version   1.0
 * @date      2015.4.1
 * @author    Zhiyan <chinash2010@gmail.com>
 * @site      Zhiyanblog <www.zhiyanblog.com>
 * @copyright Copyright (c) 2015-2015, Zhiyan
 * @license   http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link      http://www.zhiyanblog.com/wordpress-plugin-ucenter-and-market.html
**/

?>
<?php get_header(); ?>
<!-- Main Wrap -->
<div id="main-wrap">
<div class="sub-billboard billboard shopping">
  <div class="wrapper">
    <div class="inner">
    <h1><?php echo um_get_setting('store_archive_title','WordPress商店'); ?></h1>
    <p><?php echo um_get_setting('store_archive_subtitle','Theme - Service - Resource'); ?></p>
    </div>
  </div>
</div>
<div class="container shop centralnav">
	<div id="guide" class="navcaret">
        <div class="group">
            <?php wp_nav_menu( array( 'theme_location' => 'shopcatbar', 'container' => '', 'menu_id' => '', 'menu_class' => 'clr', 'depth' => '1', 'fallback_cb' => ''  ) ); ?>
        </div>
	</div>
	<div id="goodslist" class="goodlist" role="main">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <div class="col span_1_of_4" role="main">
			<div class="shop-item">
				<?php if ( has_post_thumbnail() ){
						$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large');
						$imgsrc = $large_image_url[0];
					}else{$imgsrc = um_catch_first_image();}
				?>
				<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" rel="bookmark" class="fancyimg">
					<div class="thumb-img">
						<img src="<?php echo um_timthumb($imgsrc,375,250); ?>" alt="<?php the_title(); ?>">
						<span><i class="fa fa-shopping-cart"></i></span>
					</div>
				</a>
				<h3>
					<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
				</h3>
				<p>
					<?php $contents = get_the_excerpt(); $excerpt = wp_trim_words($contents,50,'...'); echo $excerpt;?>
				</p>
				<div class="pricebtn"><?php $currency = get_post_meta($post->ID,'pay_currency',true); if($currency==1) echo '￥'; else echo '<i class="fa fa-gift">&nbsp;</i>'; ?><strong><?php echo um_get_product_price($post->ID); ?></strong><a class="buy" href="<?php the_permalink(); ?>">前往购买</a></div>
			</div>
		</div>
	<?php endwhile;endif;?>
    </div>

<!-- pagination -->
<div class="clear">
</div>
<div class="pagination">
<?php um_pagenavi(); ?>
</div>
<!-- /.pagination -->
</div>
</div>
<!--/.Main Wrap -->
<?php get_footer(); ?>