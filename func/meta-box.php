<?php

/**
 * Main Functions of Ucenter & Market WordPress Plugin
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
<?php
/**
 *  添加面板到文章（页面）编辑页
 */
function um_add_metabox() {

	$screens = array( 'post', 'store' );

	foreach ( $screens as $screen ) {
		add_meta_box(
			'um_product_info',
			__( '商品信息', 'um' ),
			'um_product_info_callback',
			$screen,
			'normal','high'
		);
	}
		add_meta_box(
			'um_keywords_description',
			__( '商品页面关键词与描述', 'um' ),
			'um_keywords_description_callback',
			'store',
			'normal','high'
		);		
}
add_action( 'add_meta_boxes', 'um_add_metabox' );

/**
 * 输出面板
 * 
 * @param WP_Post $post 当前文章（页面）对象
 */

function um_keywords_description_callback($post){
	$keywords = get_post_meta( $post->ID, 'keywords', true );
	$description = get_post_meta($post->ID, "description", true);
?>
<p><?php _e( '商品页面关键词,英文逗号分隔不同关键词', 'um' );?></p>
<textarea name="um_keywords" rows="2" class="large-text code"><?php echo stripcslashes(htmlspecialchars_decode($keywords));?></textarea>
<p><?php _e( '商品页面描述', 'um' );?></p>
<textarea name="um_description" rows="5" class="large-text code"><?php echo stripcslashes(htmlspecialchars_decode($description));?></textarea>

<?php
}

function um_product_info_callback($post){
	// 添加安全字段验证
	wp_nonce_field( 'um_meta_box', 'um_meta_box_nonce' );
	$switch = get_post_meta($post->ID,'pay_switch',true);
	$currency = get_post_meta($post->ID,'pay_currency',true);
	$price = get_post_meta($post->ID,'product_price',true);
	$amount = get_post_meta($post->ID,'product_amount',true);
	$vip_discount = json_decode(get_post_meta($post->ID,'product_vip_discount',true),true);
	$vip_discount = empty($vip_discount)?1:$vip_discount;
	$vip_discount1 = isset($vip_discount['product_vip1_discount'])?$vip_discount['product_vip1_discount']:um_get_setting('monthly_mb_disc',0.95);
	$vip_discount2 = isset($vip_discount['product_vip2_discount'])?$vip_discount['product_vip2_discount']:um_get_setting('quarterly_mb_disc',0.90);
	$vip_discount3 = isset($vip_discount['product_vip3_discount'])?$vip_discount['product_vip3_discount']:um_get_setting('annual_mb_disc',0.85);
	$vip_discount4 = isset($vip_discount['product_vip4_discount'])?$vip_discount['product_vip4_discount']:um_get_setting('life_mb_disc',0.75);
	$coupon_code_support = get_post_meta($post->ID,'product_coupon_code_support',true) ? (int)get_post_meta($post->ID,'product_coupon_code_support',true) : 0;
	$coupon_discount = get_post_meta($post->ID,'product_coupon_discount',true);
	$coupon_discount = empty($coupon_discount) ? 1 : $coupon_discount;;
	$discount_begin_date = get_post_meta($post->ID,'product_discount_begin_date',true);
	$discount_period = get_post_meta($post->ID,'product_discount_period',true);
	$download_links = get_post_meta($post->ID,'product_download_links',true);
	$pay_content = get_post_meta($post->ID,'product_pay_content',true);
?>
<?php if(get_post_type()=='store'){ ?>
<p style="clear:both;font-weight:bold;">
<?php echo sprintf(__('此商品购买按钮快捷插入短代码为[product id="%1$s"][/product]','um'),$post->ID); ?>
</p>
<?php } ?>
<?php if(get_post_type()=='post'){ ?>
<p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;">
<?php _e('开关','um'); ?>
</p>
<p style="width:20%;float:left;"><?php _e( '是否开启本文章付费资源支持', 'um' );?>
	<select name="pay_switch">
		<option value="0" <?php if( $switch!=1) echo 'selected="selected"';?>><?php _e( '关闭', 'um' );?></option>
		<option value="1" <?php if( $switch==1) echo 'selected="selected"';?>><?php _e( '开启', 'um' );?></option>
	</select>
</p>
<?php } ?>
<p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;">
<?php _e('基本信息','um'); ?>
</p>
<p style="width:20%;float:left;"><?php _e( '选择支付币种', 'um' );?>
	<select name="pay_currency">
		<option value="0" <?php if( $currency!=1) echo 'selected="selected"';?>><?php _e( '积分', 'um' );?></option>
		<option value="1" <?php if( $currency==1) echo 'selected="selected"';?>><?php _e( '人民币', 'um' );?></option>
	</select>
</p>
<p style="width:20%;float:left;"><?php _e( '商品售价 ', 'um' );?>
<input name="product_price" class="small-text code" value="<?php echo sprintf('%0.2f',$price);?>" style="width:80px;height: 28px;">
</p>
<p style="width:20%;float:left;"><?php _e( '商品数量 ', 'um' );?>
<input name="product_amount" class="small-text code" value="<?php echo (int)$amount;?>" style="width:80px;height: 28px;">
</p>
<p style="width:40%;float:left;"><?php _e( '是否支持优惠码,仅限现金商品 ', 'um' );?>
	<select name="product_coupon_code_support">
		<option value="0" <?php if( $coupon_code_support!==1) echo 'selected="selected"';?>><?php _e( '不支持', 'um' );?></option>
		<option value="1" <?php if( $coupon_code_support===1) echo 'selected="selected"';?>><?php _e( '支持', 'um' );?></option>
	</select>
</p>
<p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;">
<?php _e('VIP会员折扣(1.00代表原价)','um'); ?>
</p>
<p style="width:33%;float:left;clear:left;"><?php _e( 'VIP月费会员折扣 ', 'um' );?>
<input name="product_vip1_discount" class="small-text code" value="<?php echo sprintf('%0.2f',$vip_discount1);?>" style="width:80px;height: 28px;">
</p>
<p style="width:33%;float:left;"><?php _e( 'VIP季费会员折扣 ', 'um' );?>
<input name="product_vip2_discount" class="small-text code" value="<?php echo sprintf('%0.2f',$vip_discount2);?>" style="width:80px;height: 28px;">
</p>
<p style="width:33%;float:left;"><?php _e( 'VIP年费会员折扣 ', 'um' );?>
<input name="product_vip3_discount" class="small-text code" value="<?php echo sprintf('%0.2f',$vip_discount3);?>" style="width:80px;height: 28px;">
</p>
<p style="width:33%;float:left;"><?php _e( 'VIP终身会员折扣 ', 'um' );?>
<input name="product_vip4_discount" class="small-text code" value="<?php echo sprintf('%0.2f',$vip_discount4);?>" style="width:80px;height: 28px;">
</p>
<p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;">
<?php _e('促销信息','um'); ?>
</p>
<p style="width:20%;float:left;clear:left;"><?php _e( '优惠促销折扣 ', 'um' );?>
<input name="product_coupon_discount" class="small-text code" value="<?php echo sprintf('%0.2f',$coupon_discount);?>" style="width:80px;height: 28px;">
</p>
<p style="width:35%;float:left;"><?php _e( '优惠开始日期(格式2015-01-01) ', 'um' );?>
<input name="product_discount_begin_date" class="small-text code" value="<?php echo $discount_begin_date;?>" style="width:100px;height:28px;">
</p>
<p style="width:40%;float:left;"><?php _e( '优惠期,为0或为空则不启用优惠 ', 'um' );?>
<input name="product_discount_period" class="small-text code" value="<?php echo (int)$discount_period;?>" style="width:60px;height: 28px;"><?php _e( ' 天', 'um' );?>
</p>
<p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;">
<?php _e('付费内容','um'); ?>
</p>
<p style="clear:both;"><?php _e( '付费查看下载链接,一行一个,每个资源格式为资源名|资源下载链接|密码', 'um' );?></p>
<textarea name="product_download_links" rows="5" class="large-text code"><?php echo $download_links;?></textarea>
<p style="clear:both;"><?php _e( '付费查看的内容信息', 'um' );?></p>
<textarea name="product_pay_content" rows="5" class="large-text code"><?php echo $pay_content;?></textarea>

<?php	
}
/**
 * 保存文章时页，保存自定义内容
 *
 * @param int $post_id 这是即将保存的文章ID
 */
function um_save_meta_box_data( $post_id ) {
	// 检查安全字段验证
	if ( ! isset( $_POST['um_meta_box_nonce'] ) ) {
		return;
	}
	// 检查安全字段的值
	if ( ! wp_verify_nonce( $_POST['um_meta_box_nonce'], 'um_meta_box' ) ) {
		return;
	}
	// 检查是否自动保存，自动保存则跳出
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// 检查用户权限
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}
	// 检查和更新字段
	
	if ( isset( $_POST['um_keywords'] ) ) update_post_meta( $post_id, 'keywords', htmlspecialchars($_POST['um_keywords']) );

	if ( isset( $_POST['um_description'] ) ) update_post_meta( $post_id, 'description', htmlspecialchars($_POST['um_description']) );
	
	if ( isset( $_POST['pay_switch'] ) ) update_post_meta( $post_id, 'pay_switch', $_POST['pay_switch'] );

	if ( isset( $_POST['pay_currency'] ) ) update_post_meta( $post_id, 'pay_currency', $_POST['pay_currency'] );

	if ( isset( $_POST['product_price'] ) ) update_post_meta( $post_id, 'product_price', $_POST['product_price'] );
	
	if ( isset( $_POST['product_amount'] ) ) update_post_meta( $post_id, 'product_amount', $_POST['product_amount'] );
	
	if ( isset( $_POST['product_vip1_discount'] )||isset( $_POST['product_vip2_discount'] )||isset( $_POST['product_vip3_discount'] )||isset( $_POST['product_vip4_discount'] ) ) {$vip1_discount=isset( $_POST['product_vip1_discount'] )?$_POST['product_vip1_discount']:1;$vip2_discount=isset( $_POST['product_vip2_discount'] )?$_POST['product_vip2_discount']:1;$vip3_discount=isset( $_POST['product_vip3_discount'] )?$_POST['product_vip3_discount']:1;$vip4_discount=isset( $_POST['product_vip4_discount'] )?$_POST['product_vip4_discount']:1;$vip_discount=json_encode(array('product_vip1_discount'=>$vip1_discount,'product_vip2_discount'=>$vip2_discount,'product_vip3_discount'=>$vip3_discount,'product_vip4_discount'=>$vip4_discount));update_post_meta( $post_id, 'product_vip_discount', $vip_discount );}
	
	if ( isset( $_POST['product_coupon_code_support'] ) ) update_post_meta( $post_id, 'product_coupon_code_support', $_POST['product_coupon_code_support'] );
	
	if ( isset( $_POST['product_coupon_discount'] ) ) update_post_meta( $post_id, 'product_coupon_discount', $_POST['product_coupon_discount'] );
	
	if ( isset( $_POST['product_discount_begin_date'] ) ) update_post_meta( $post_id, 'product_discount_begin_date', $_POST['product_discount_begin_date'] );
	
	if ( isset( $_POST['product_discount_period'] ) ) update_post_meta( $post_id, 'product_discount_period', $_POST['product_discount_period'] );
	
	if ( isset( $_POST['product_download_links'] ) ) update_post_meta( $post_id, 'product_download_links', $_POST['product_download_links'] );
	
	if ( isset( $_POST['product_pay_content'] ) ) update_post_meta( $post_id, 'product_pay_content', $_POST['product_pay_content'] );
	
}
add_action( 'save_post', 'um_save_meta_box_data' );

?>