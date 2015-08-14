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
	<div id="single-blog-wrap" class="container shop">
		<div class="area">
		<!-- Content -->
		<div class="product-content">
			<div class="breadcrumb">
				<a href="<?php echo get_bloginfo('url').'/'.um_get_setting('store_archive_slug','store'); ?>"><?php _e('商店','um'); ?></a>&nbsp;<i class="fa fa-angle-right"></i>&nbsp;<span><?php echo get_the_term_list($post,'products_category','','|'); ?></span>
			</div>
			<?php while ( have_posts() ) : the_post(); ?>
			<article id="<?php echo 'product-'.$post->ID; ?>" class="product">
				<div class="preview">
					<?php if(has_post_thumbnail()){$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large');$imgsrc = $large_image_url[0];}else{$imgsrc = um_catch_first_image();} ?>
					<img src="<?php echo um_timthumb($imgsrc,360,300); ?>" alt="<?php the_title(); ?>">
					<div class="view-share">
						<p class="view"><?php _e('人气：','um'); ?><?php echo (int)get_post_meta($post->ID,'um_post_views',true); ?></p>
						<div class="share">
							<div id="bdshare" class="bdsharebuttonbox baidu-share"><span><?php _e('分享：','um'); ?></span>
								<a href="#" class="bds_qzone" data-cmd="qzone" title="<?php _e('分享到QQ空间','um'); ?>"></a>
								<a href="#" class="bds_tsina" data-cmd="tsina" title="<?php _e('分享到新浪微博','um'); ?>"></a>
								<a href="#" class="bds_tqq" data-cmd="tqq" title="<?php _e('分享到腾讯微博','um'); ?>"></a>
								<a href="#" class="bds_weixin weixin-btn" data-cmd="weixin" title="<?php _e('分享到微信','um'); ?>">
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="property">
					<div class="title row">
						<h1><?php the_title(); ?></h1>
						<p><?php $contents = get_the_excerpt(); $excerpt = wp_trim_words($contents,50,''); echo $excerpt;?></p>
					</div>
					<div class="summary row">
						<ul>
							<?php $currency = get_post_meta($post->ID,'pay_currency',true); ?>
							<?php $discount_arr = product_smallest_price($post->ID);if($discount_arr[3]==0&&$discount_arr[4]==0){?>
							<li class="summary-price"><span class="dt"><?php _e('商品售价','um'); ?></span><strong><?php if($currency==1)echo '<em>¥</em>'.sprintf('%0.2f',$discount_arr[0]).'<em>(元)</em>'; else echo '<em><i class="fa fa-gift"></i></em>'.sprintf('%0.2f',$discount_arr[0]).'<em>(积分)</em>';?></strong></li>
							<?php }else{ ?>
							<li class="summary-price"><span class="dt"><?php _e('商品售价','um'); ?></span><strong><?php if($currency==1)echo '<em>¥</em><del>'.sprintf('%0.2f',$discount_arr[0]).'</del><em>(元)</em>'; else echo '<em><i class="fa fa-gift"></i></em><del>'.sprintf('%0.2f',$discount_arr[0]).'</del><em>(积分)</em>';?></strong><?php if($discount_arr[4]!=0){?><strong><?php echo '&nbsp;'.sprintf('%0.2f',$discount_arr[2]); ?></strong><span><?php _e('(限时特惠)','um'); ?></span><?php }?></li>
							<?php if($discount_arr[3]!=0){?>
							<li class="summary-price"><span class="dt"><?php _e('会员特惠','um'); ?></span><?php if(getUserMemberType()) { ?><strong><?php if($currency==1)echo '<em>¥</em>'.sprintf('%0.2f',$discount_arr[1]).'<em>(元)</em>'; else echo '<em><i class="fa fa-gift"></i></em>'.sprintf('%0.2f',$discount_arr[1]).'<em>(积分)</em>';?></strong></li><?php }else if(is_user_logged_in()){echo '<strong>';if($currency==1)echo '<em>¥</em>'.sprintf('%0.2f',$discount_arr[6]).'<em>(元)</em>'; else echo '<em><i class="fa fa-gift"></i></em>'.sprintf('%0.2f',$discount_arr[6]).'<em>(积分)</em>';echo '</strong>';echo sprintf(__('非<a href="%1$s" target="_blank" title="开通会员">会员</a>不能享受该优惠','um'),um_get_user_url('membership'));} else {_e('<a href="javascript:" class="user-login">登录</a> 查看优惠','um');} ?><?php }?>
							<?php }?>
							<li class="summary-amount"><span class="dt"><?php _e('商品数量','um'); ?></span><span class="dt-num"><?php $amount = get_post_meta($post->ID,'product_amount',true) ? (int)get_post_meta($post->ID,'product_amount',true):0; echo $amount; ?></span></li>
							<li class="summary-sales"><span class="dt"><?php _e('商品销量','um'); ?></span><span class="dt-num"><?php $sales = get_post_meta($post->ID,'product_sales',true) ? (int)get_post_meta($post->ID,'product_sales',true):0; echo $sales; ?></span></li>
							<li class="summary-market"><span class="dt"><?php _e('商品编号','um'); ?></span><?php echo $post->ID; ?></li>
                        </ul>
					</div>
					<div class="amount row"><span class="dt"><?php _e('数量','um'); ?></span>
						<div class="amount-number">
							<a href="javascript:" hidefocus="true" field="amountquantity" id="minus" class="control minus"><i class="fa fa-minus"></i></a>
							<input type="text" name="amountquantity" class="amount-input" value="1" maxlength="5" title="<?php _e('请输入购买量','um'); ?>">
							<a href="javascript:" hidefocus="true" field="amountquantity" id="plus" class="control plus"><i class="fa fa-plus"></i></a>
						</div>
					</div>
					<div class="buygroup row">
						<?php if($amount<=0){ ?>
						<a class="buy-btn sold-out"><i class="fa fa-shopping-cart"></i><?php _e('已售完','um'); ?></a>
						<?php }else{ ?>
							<?php if(is_user_logged_in()&&$discount_arr[5]>0){ ?>
							<a class="buy-btn" data-top="true" data-pop="order"><i class="fa fa-shopping-cart"></i><?php _e('立即购买','um'); ?></a>
							<?php }elseif($discount_arr[0]>0&&!is_user_logged_in()){ ?>
							<a data-sign="0" class="user-signin buy-btn user-login"><i class="fa fa-shopping-cart"></i><?php _e('登录购买','um'); ?></a>
							<?php }else{ ?>
							<a class="buy-btn free-buy" data-top="false"><i class="fa fa-shopping-cart"></i><?php _e('立即购买','um'); ?></a>
							<?php } ?>			
                        <?php } ?>
                    </div>
					<div class="tips row">
						<p><?php _e('注意：本站为本商品唯一销售点，请勿在其他途径购买，以免遭受安全损失。','um'); ?></p>
					</div>
				</div>
				<div class="main-content">
					<div class="shop-content">
						<div class="mainwrap">
							<div id="wrapnav">
								<?php $order_records = get_user_order_records($post->ID); $order_num = count($order_records); ?>
								<ul class="nav">
									<div class="intro"></div>
									<li class="active"><a href="#description" rel="nofollow" hidefocus="true"><?php _e('商品详情','um'); ?></a></li>
									<li><a href="#reviews" rel="nofollow" hidefocus="true"><?php _e('商品评价','um'); ?><em><?php $count_comments = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->comments  WHERE comment_approved='1' AND comment_post_ID = %d AND comment_type not in ('trackback','pingback')", $post->ID ) ); echo $count_comments; ?></em></a></li>
                                    <li class="nav-history"><a href="#history" rel="nofollow" hidefocus="true"><i class="fa fa-history"></i><?php _e('我的购买记录','um'); ?><em><?php echo $order_num; ?></em></a></li>
                                    <a class="fixed-buy-btn buy-btn" data-top="true" data-pop="order"><i class="fa fa-shopping-cart"></i><?php _e('立即购买','um'); ?></a>
                                </ul>
							</div>
							<div id="wrapnav-container">
								<div id="description" class="wrapbox single-content single-text">
								<p>
								<?php echo store_pay_content_show(get_the_content()); ?>
								</p>
								</div>
								<div id="reviews" class="wrapbox">
								<?php if (comments_open()) comments_template( '', true ); ?>
								</div>
								<div id="history" class="wrapbox">
									<?php if(!is_user_logged_in()){ ?>
									<p class="history-tip"><?php _e('我的购买记录，登陆后可见，','um'); ?><a class="user-signin user-login" href="#" title="<?php _e('点击登录','um'); ?>"><?php _e('立即登录','um'); ?></a>。</p>
									<?php }else{ ?>
                         	    	<div class="pay-history">
										<div class="greytip"><?php _e('Tips：若商品可循环使用则无须多次购买','um'); ?></div>
										<table width="100%" border="0" cellspacing="0">
										<thead>
											<tr>
												<th scope="col"><?php _e('订单号','um'); ?></th>
												<th scope="col"><?php _e('购买时间','um'); ?></th>
												<th scope="col"><?php _e('数量','um'); ?></th>
												<th scope="col"><?php _e('价格','um'); ?></th>
												<th scope="col"><?php _e('金额','um'); ?></th>
												<th scope="col"><?php _e('交易状态','um'); ?></th>
											</tr>
										</thead>
										<tbody class="the-list">
											<?php foreach($order_records as $order_record){ ?>
                                            <tr>
												<td><?php echo $order_record['order_id']; ?></td>
												<td><?php echo $order_record['order_time']; ?></td>
												<td><?php echo $order_record['order_quantity']; ?></td>
												<td><?php echo $order_record['order_price']; ?></td>
												<td><?php echo $order_record['order_total_price']; ?></td>
												<td><?php if($order_record['order_status']){echo output_order_status($order_record['order_status']);}; ?></td>
											</tr>
											<?php } ?>
                                        </tbody>
										</table>
									</div>
									<?php } ?>
                            	</div>
                            </div>
						</div>
					</div>
					<div class="shop-sidebar">
						<h3><i class="fa fa-gavel"></i><?php _e('相关推荐','um'); ?></h3>
						<ul>
						<?php $tags = get_the_terms($post->ID,'products_tag');$tagcount = $tags ? count($tags):0;$tagIDs=array();for ($i = 0;$i <$tagcount;$i++) {$tagIDs[] = $tags[$i]->term_id;};$args=array('term__in'=>$tagIDs,'post_type'=>'store','post__not_in'=>array($post->ID),'showposts'=>4,'orderby'=>'rand','ignore_sticky_posts'=>1);$my_query = new WP_Query($args);if( $my_query->have_posts() ){while ($my_query->have_posts()) : $my_query->the_post();if ( has_post_thumbnail() ){$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large');$imgsrc = $large_image_url[0];}else{$imgsrc = um_catch_first_image();}
						?>
							<li>
								<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" rel="bookmark" class="fancyimg">
									<div class="thumb-img">
										<img src="<?php echo um_timthumb($imgsrc,280,180); ?>" alt="<?php the_title(); ?>">
										<span><i class="fa fa-shopping-cart"></i></span>
									</div>
								</a>
								<p><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></p>
							</li>
						<?php endwhile;} wp_reset_query(); ?>
				        </ul>
					</div>
				</div>	
			</article>
			<div id="order" class="popupbox">
				<form id="alipayment" name="alipayment" action="<?php echo UM_URI.'alipay/alipayapi.php'; ?>" method="post">
					<div id="pay">
						<div class="part-order">
							<ul>
								<h3><?php _e('订单信息','um'); ?><span><?php _e('（价格单位：','um'); ?><?php if($currency==1)echo '元'; else echo '积分';?><?php _e('）','um'); ?></span></h3>
								<input type="hidden" name="order_nonce" value="<?php echo wp_create_nonce( 'order-nonce' );?>" >
								<input type = "hidden" id="product_id" name="product_id" readonly="" value="<?php echo $post->ID; ?>">
								<input type = "hidden" id="order_id" name="order_id" readonly="" value="0">
								<li><label for="order_name"><small>*</small><?php _e('商品名称：','um'); ?></label><input id="order_name" name="order_name" readonly="" value="<?php the_title();?>"></li>
								<li><label for="order_price"><small>*</small><?php _e('商品单价：','um'); ?></label><input id="order_price" readonly="" value="<?php echo $discount_arr[5]; ?>"></li>
								<li><label for="order_quantity"><small>*</small><?php _e('商品数量：','um'); ?></label><input id="order_quantity" name="order_quantity" value="1" maxlength="8" title="<?php _e('请输入购买量','um'); ?>" onkeydown="if(event.keyCode==13)return false;"></li>
							</ul>
							<ul>
								<h3><?php _e('收货信息','um'); ?><span><?php _e('商店','um'); ?><?php _e('（虚拟商品除邮箱外可不填）','um'); ?></span></h3>
								<?php $autofill = get_user_autofill_info();?>
								<li><label for="receive_name"><?php _e('收货姓名：','um'); ?></label><input id="receive_name" name="order_receive_name" value="<?php echo $autofill['user_name']; ?>" onkeydown="if(event.keyCode==13)return false;"></li>
								<li><label for="receive_address"><?php _e('收货地址：','um'); ?></label><input id="receive_address" name="order_receive_address" value="<?php echo isset($autofill['user_address'])?$autofill['user_address']:''; ?>" onkeydown="if(event.keyCode==13)return false;"></li>
								<li><label for="receive_zip"><?php _e('收货邮编：','um'); ?></label><input id="receive_zip" name="order_receive_zip" value="<?php echo isset($autofill['user_zip'])?$autofill['user_zip']:''; ?>" onkeydown="if(event.keyCode==13)return false;"></li>
								<li><label for="receive_email"><?php _e('用户邮箱：','um'); ?></label><input id="receive_email" name="order_receive_email" value="<?php echo $autofill['user_email']; ?>" onkeydown="if(event.keyCode==13)return false;"></li>
								<li><label for="receive_phone"><?php _e('电话号码：','um'); ?></label><input id="receive_phone" name="order_receive_phone" value="<?php echo isset($autofill['user_phone'])?$autofill['user_phone']:''; ?>" onkeydown="if(event.keyCode==13)return false;"></li>
								<li><label for="receive_mobile"><?php _e('手机号码：','um'); ?></label><input id="receive_mobile" name="order_receive_mobile" value="<?php echo isset($autofill['user_cellphone'])?$autofill['user_cellphone']:''; ?>" onkeydown="if(event.keyCode==13)return false;"></li>
								<li><label for="body"><?php _e('留言备注：','um'); ?></label><input name="order_body" value="" onkeydown="if(event.keyCode==13)return false;"></li>
							</ul>
						</div>
						<div class="checkout">
						<?php if($currency==1&&get_post_meta($post->ID,'product_coupon_code_support',true)==1){ ?>
							<div id="coupon">
								<input id="coupon_code" value="" onkeydown="if(event.keyCode==13)return false;">
								<span id="coupon_code_apply"><?php _e('使用优惠码','um'); ?></span>
							</div>
						<?php } ?>
							<button id="pay-submit" type="submit"><?php _e('立即付款','um'); ?></button>
							<div id="total-price"><?php _e('总金额：','um'); ?><strong>￥1.00</strong><?php if($currency==1)echo '元'; else echo '积分';?></div>
						</div>
						<div>
						</div>
						<a class="popup-close"><i class="fa fa-times"></i></a>
					</div>
				</form>
			</div>
			<?php endwhile; ?>
		</div>
		<!-- /.Content -->
		</div>
	</div>
</div>
<!--/.Main Wrap -->
<!-- Login box -->
<!-- /.Login box -->
<?php get_footer(); ?>