<?php

/*
Plugin Name: 用户中心与商城
Plugin URI: http://www.zhiyanblog.com/wordpress-plugin-ucenter-and-market.html
Description: 为你的网站添加前端登录/注册(包含新浪微博/QQ第三方一键登录)、用户中心(文章、收藏、评论、站内信、积分、关注/粉丝)、商城(文章嵌入式或单独模板式、订单及优惠码管理)、会员(月付/季付/年付/终身多个等级，可设置不同商品资源优惠)功能，集成用户积分+支付宝支付以及用户推广提成系统
Version: 1.2
Author: Zhiyan
Author URI: http://www.zhiyanblog.com/
*/

?>
<?php
if ( !defined('ABSPATH') ) {exit;}
/* Set plugin path */
if ( !defined( 'UM_DIR' ) ) {
	define( 'UM_DIR', plugin_dir_path(__FILE__) );
}
/* Set plugin url */
if ( !defined( 'UM_URI' ) ) {
	define( 'UM_URI', plugin_dir_url(__FILE__) );
}
/* Set plugin version */
if ( !defined( 'UM_VER' ) ) {
	define( 'UM_VER', '1.2' );
}
/* Set plugin type */
if ( !defined( 'UM_TYPE' ) ) {
	define( 'UM_TYPE', 'sale' );
}
if ( !defined( 'UM' ) ) {
	define( 'UM', 'ucenter&Market' );
}
/* Including functions */
require_once('func/functions.php');
require_once('func/setting-api.php');
require_once('func/affiliate.php');
require_once('func/follow.php');
require_once('func/membership.php');
//require_once('func/shop.php');
get_the_template('shop.php');
require_once('func/credit.php');
require_once('func/message.php');
require_once('func/mail.php');
require_once('func/meta-box.php');
require_once('func/open-social.php');
require_once('func/extension.php');
require_once('template/loginbox.php');
require_once('template/order.php');
require_once('widgets/ucenter.php');
require_once('widgets/credits-rank.php');


/* Add admin menu */
if( is_admin() ) {
    add_action('admin_menu', 'display_um_menu');
}
function display_um_menu() {
    add_menu_page('用户中心与商城', '用户中心与商城', 'administrator','ucenter_market', 'um_setting_page','dashicons-groups');
    add_submenu_page('ucenter_market', '用户中心与商城 &gt; 设置', '插件设置', 'administrator','ucenter_market', 'um_setting_page');
    add_submenu_page('ucenter_market', '用户中心与商城 &gt; 激活', '激活授权', 'administrator','ucenter_market_active', 'um_setting_active_page');
}

/* Setting page html */
function um_setting_page(){
	settings_errors();
	?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
	        <a class="nav-tab nav-tab-active" href="javascript:;" id="tab-title-membership">会员设置</a>
	        <a class="nav-tab" href="javascript:;" id="tab-title-ucenter">用户中心设置</a>
	        <a class="nav-tab" href="javascript:;" id="tab-title-store">商城设置</a>
	        <a class="nav-tab" href="javascript:;" id="tab-title-payment">支付宝设置</a>
	        <a class="nav-tab" href="javascript:;" id="tab-title-credit">积分设置</a>
	        <a class="nav-tab" href="javascript:;" id="tab-title-mail">邮件设置</a>
	        <a class="nav-tab" href="javascript:;" id="tab-title-social">社会化登录设置</a>
	        <a class="nav-tab" href="javascript:;" id="tab-title-other">其他设置</a>      
	    </h2>
		<form action="options.php" method="POST">
			<?php settings_fields( 'ucenter_market_group' ); ?>
			<?php
				settings_errors();
				$labels = um_get_option_labels();
				extract($labels);
			?>
			<?php foreach ( $sections as $section_name => $section ) { ?>
	            <div id="tab-<?php echo $section_name; ?>" class="div-tab hidden">
	                <?php um_option_do_settings_section($option_page, $section_name); ?>
	            </div>                      
	        <?php } ?>
			<input type="hidden" name="<?php echo $option_name;?>[current_tab]" id="current_tab" value="" />
			<?php submit_button(); ?>
		</form>
		<?php um_option_tab_script(); ?>
	</div>
<?php
}

/* Active page html */
function um_setting_active_page(){
	settings_errors();
	$order = um_get_setting('order');
	$sn = um_get_setting('sn');
	?>
	<div class="wrap">
		<form action="options.php" method="POST">
			<?php settings_fields( 'ucenter_market_group' ); ?>
			<?php
				settings_errors();
				$labels = um_get_option_labels();
				extract($labels);
			?>
			<?php foreach ( $sections as $section_name => $section ) { ?>
	            <div id="tab-<?php echo $section_name; ?>" class="div-tab <?php if($section_name!='auth') echo 'hidden'; ?>">
	                <?php um_option_do_settings_section($option_page, $section_name); ?>
	            </div>                      
	        <?php } ?>
			<input type="hidden" name="<?php echo $option_name;?>[current_tab]" id="current_tab" value="" />
			<?php submit_button(); ?>
		</form>
	</div>

<?php	
}