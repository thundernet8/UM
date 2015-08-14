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
/* Setting page tabs switch js */
function um_option_tab_script(){
	$current_tab = '';
	$option_name = 'ucenter_market';
	$option = get_option( $option_name );
	if(!empty($_GET['settings-updated'])){
		$current_tab = $option['current_tab'];
	}
	?>
	<script type="text/javascript">
		jQuery('div.div-tab').hide();
	<?php if($current_tab){ ?>
		jQuery('#tab-title-<?php echo $current_tab; ?>').addClass('nav-tab-active');
		jQuery('#tab-<?php echo $current_tab; ?>').show();
		jQuery('#current_tab').val('<?php echo $current_tab; ?>');
	<?php } else{ ?>
		jQuery('h2 a.nav-tab').first().addClass('nav-tab-active');
		jQuery('div.div-tab').first().show();
	<?php } ?>
		jQuery(function($){
			$('h2 a.nav-tab').on('click',function(){
		        $('h2 a.nav-tab').removeClass('nav-tab-active');
		        $(this).addClass('nav-tab-active');
		        $('div.div-tab').hide();
		        $('#'+jQuery(this)[0].id.replace('title-','')).show();
		        $('#current_tab').val($(this)[0].id.replace('tab-title-',''));
		    });
		});
	</script>
<?php
}


/* Options echo specified html */
function um_option_field_callback($field) {

	$field_name		= $field['name'];
	$field['key']	= $field_name;
	$field['name']	= $field['option'].'['.$field_name.']';

	$options	= um_get_option( $field['option'] );
	$field['value'] = (isset($options[$field_name]))?$options[$field_name]:'';

	echo um_admin_get_field_html($field);
}

function um_admin_get_field_html($field){

	$key		= $field['key'];
	$name		= $field['name'];
	$type		= $field['type'];
	$value		= $field['value'];

	$class		= isset($field['class'])?$field['class']:'regular-text';
	$description= (!empty($field['description']))?( ($type == 'checkbox')? ' <label for="'.$key.'">'.$field['description'].'</label>':'<p>'.$field['description'].'</p>'):'';

	$title 	= isset($field['title'])?$field['title']:$field['name'];
	$label 	= '<label for="'.$key.'">'.$title.'</label>';

	switch ($type) {
		case 'text':
		case 'password':
		case 'hidden':
		case 'url':
		case 'color':
		case 'url':
		case 'tel':
		case 'email':
		case 'month':
		case 'date':
		case 'datetime':
		case 'datetime-local':
		case 'week':
			$field_html = '<input name="'.$name.'" id="'. $key.'" type="'.$type.'"  value="'.esc_attr($value).'" class="'.$class.'" />';
			break;
		case 'range':
			$max	= isset($field['max'])?' max="'.$field['max'].'"':'';
			$min	= isset($field['min'])?' min="'.$field['min'].'"':'';
			$step	= isset($field['step'])?' step="'.$field['step'].'"':'';

			$field_html ='<input name="'.$name.'" id="'. $key.'" type="'.$type.'"  value="'.esc_attr($value).'"'.$max.$min.$step.' class="'.$class.'" onchange="jQuery(\'#'.$key.'_span\').html(jQuery(\'#'.$key.'\').val());"  /> <span id="'.$key.'_span">'.$value.'</span>';
			break;

		case 'number':
			$max	= isset($field['max'])?' max="'.$field['max'].'"':'';
			$min	= isset($field['min'])?' min="'.$field['min'].'"':'';
			$step	= isset($field['step'])?' step="'.$field['step'].'"':'';

			$field_html = '<input name="'.$name.'" id="'. $key.'" type="'.$type.'"  value="'.esc_attr($value).'" class="'.$class.'"'.$max.$min.$step.' />';
			break;

		case 'checkbox':
			$field_html = '<input name="'.$name.'" id="'. $key.'" type="checkbox"  value="1" '.checked("1",$value,false).' />';
			break;

		case 'textarea':
			$rows = isset($field['rows'])?$field['rows']:6;
			$field_html = '<textarea name="'.$name.'" id="'. $key.'" rows="'.$rows.'" cols="50"  class="'.$class.' code" >'.esc_attr($value).'</textarea>';
			break;

		case 'select':
			$field_html  = '<select name="'.$name.'" id="'. $key.'">';
			foreach ($field['options'] as $option_title => $option_value){ 
				$field_html .= '<option value="'.$option_value.'" '.selected($option_value,$value,false).'>'.$option_title.'</option>';
			}
			$field_html .= '</select>';
			
			break;

		case 'radio':
			$field_html  = '';
			foreach ($field['options'] as $option_value => $option_title) {
				$field_html  .= '<input name="'.$name.'" type="radio" id="'.$key.'" value="'.$option_value .'" '.checked($option_value,$value,false).' />'.$option_title.'<br />';
			}
			break;

		case 'image':
			$field_html = '<input name="'.$name.'" id="'.$key.'" type="url"  value="'.esc_attr($value).'" class="'.$class.'" /><input type="button" class="um_upload button" style="width:80px;" value="选择图片">';
			$field_html .= '<img src="'.esc_attr($value).'" style="max-width:120px;vertical-align: top;margin-left: 20px;" />';
            break;
        case 'mulit_image':
        case 'multi_image':
        	$field_html  = '';
            if(is_array($value)){
                foreach($value as $image_key=>$image){
                    if(!empty($image)){
                    	$field_html .= '<span><input type="text" name="'.$name.'[]" id="'. $key.'" value="'.esc_attr($image).'"  class="'.$class.'" /><a href="javascript:;" class="button del_image">删除</a></span>';
                    }
                }
            }
            $field_html  = '<span><input type="text" name="'.$name.'[]" id="'.$key.'" value="" class="'.$class.'" /><input type="bu
            tton" class="um_mulit_upload button" style="width:110px;" value="选择图片[多选]" title="按住Ctrl点击鼠标左键可以选择多张图片"></span>';
            break;
        case 'mulit_text':
        case 'multi_text':
        	$field_html  = '';
            if(is_array($value)){
                foreach($value as $text_key=>$item){
                    if(!empty($item)){
                    	$field_html .= '<span><input type="text" name="'.$name.'[]" id="'. $key.'" value="'.esc_attr($item).'"  class="'.$class.'" /><a href="javascript:;" class="button del_image">删除</a></span>';
                    }
                }
            }
            $field_html  = '<span><input type="text" name="'.$name.'[]" id="'.$key.'" value="" class="'.$class.'" /><a class="um_mulit_text button">添加选项</a></span>';
            break;

        case 'file':
        	$field_html  = '<input type="file" name="'.$name.'" id="'. $key.'" />'.'已上传：'.wp_get_attachment_link($value);
            break;
		
		default:
			$field_html = '<input name="'.$name.'" id="'. $key.'" type="text"  value="'.esc_attr($value).'" class="'.$class.'" />';
			break;
	}

	return $field_html.$description;
}

/* Add setting options */
function um_get_option_labels(){
	$option_group               =   'ucenter_market_group';
	$option_name = $option_page =   'ucenter_market';
	$field_validate				=	'ucenter_market_validate';
	$home = home_url();

	$membership_fields = array(
		'monthly_mb_price'	=> array('title'=>'月费会员价格',	'type'=>'text',	'description'=>''),
		'quarterly_mb_price'	=> array('title'=>'季费会员价格',	'type'=>'text',	'description'=>''),
		'annual_mb_price'	=> array('title'=>'年费会员价格',	'type'=>'text',	'description'=>''),
		'life_mb_price'	=> array('title'=>'终身会员价格',	'type'=>'text',	'description'=>''),
		'monthly_mb_disc'	=> array('title'=>'月费会员默认折扣',	'type'=>'text',	'description'=>''),
		'quarterly_mb_disc'	=> array('title'=>'季费会员默认折扣',	'type'=>'text',	'description'=>''),
		'annual_mb_disc'	=> array('title'=>'年费会员默认折扣',	'type'=>'text',	'description'=>''),
		'life_mb_disc'	=> array('title'=>'终身会员默认折扣',	'type'=>'text',	'description'=>'')
	);

	$ucenter_fields = array(
		'open_ucenter'	=> array('title'=>'开启个人中心',	'type'=>'checkbox',	'description'=>'开启个人中心，丰富用户个人页功能')
	);

	$store_fields = array(
		'store_archive_slug'	=> array('title'=>'商品归档页别名',	'type'=>'text',	'description'=>'默认为store，即可以以<a href="'.$home.'/store" target="_blank">'.$home.'/store</a>链接访问商品归档页'),
		'product_link_mode'	=> array('title'=>'商品页固定链接模式',	'type'=>'select', 'options'=>array('Postname'=>'post_name','PostID'=>'post_id')),
		'store_archive_title'	=> array('title'=>'商品归档页横幅标题',	'type'=>'text',	'description'=>'可作为页面的title'),
		'store_archive_subtitle'	=> array('title'=>'商品归档页横幅子标题',	'type'=>'text',	'description'=>'可作页面关键字'),
		'store_archive_des'	=> array('title'=>'商品归档页描述',	'type'=>'text',	'description'=>'页面描述,SEO要素'),
		'store_cat_pre'	=> array('title'=>'商品分类链接前缀',	'type'=>'text',	'description'=>''),
		'store_tag_pre'	=> array('title'=>'商品标签链接前缀',	'type'=>'text',	'description'=>''),
		'aff_ratio'	=> array('title'=>'推广订单返现百分数',	'type'=>'text',	'description'=>'单位%，注意数值为10则代表返现10%'),
		'aff_discharge_lowest'	=> array('title'=>'申请提现最低账户余额',	'type'=>'text',	'description'=>'账户余额达到一定值时方可提现')
	);

	$payment_fields = array(
		'alipay_account'	=> array('title'=>'支付宝收款账号',	'type'=>'text',	'description'=>'支付宝收款帐户邮箱,要收款必填并务必保持正确'),
		'alipay_id'	=> array('title'=>'支付宝商家身份ID',	'type'=>'text',	'description'=>''),
		'alipay_key'	=> array('title'=>'支付宝商家身份Key',	'type'=>'text',	'description'=>'请至<a href="https://b.alipay.com" target="_blank">支付宝商家服务</a>进行申请'),
		'alipay_sign_type'	=> array('title'=>'支付宝商家签约类型',	'type'=>'select', 'options'=>array('即时到账'=>'create_direct_pay_by_user','双功能收款'=>'trade_create_by_buyer','担保交易'=>'create_partner_trade_by_buyer')),
		'alipay_qrcode'	=> array('title'=>'支付宝收款二维码',	'type'=>'image')
	);

	$credit_fields = array(
		'exchange_ratio'	=> array('title'=>'现金积分兑换比率',	'type'=>'text',	'description'=>'1元所能兑换的积分数，用于计算积分充值'),
		'new_reg_credit'	=> array('title'=>'新用户注册奖励',	'type'=>'text',	'description'=>'新用户首次注册奖励积分'),
		'daily_sign_credit'	=> array('title'=>'每日签到积分奖励',	'type'=>'text',	'description'=>''),
		'comment_credit'	=> array('title'=>'评论一次奖励积分',	'type'=>'text',	'description'=>''),
		'comment_credit_times'	=> array('title'=>'每日评论奖励积分次数',	'type'=>'text',	'description'=>'每天通过评论获得积分的最大次数，超出后将不再获得积分'),
		'like_article_credit'	=> array('title'=>'文章互动可得积分,点击喜欢',	'type'=>'text',	'description'=>''),
		'like_article_credit_times'	=> array('title'=>'文章互动可得积分每日次数',	'type'=>'text',	'description'=>''),
		'contribute_credit'	=> array('title'=>'投稿一次奖励积分',	'type'=>'text',	'description'=>''),
		'contribute_credit_times'	=> array('title'=>'每日投稿获得积分次数',	'type'=>'text',	'description'=>'每天通过投稿获得积分的最大次数，超出后将不再获得积分'),
		'aff_visit_credit'	=> array('title'=>'访问推广一次奖励积分',	'type'=>'text',	'description'=>'通过专属推广链接推广用户访问一次奖励积分'),
		'aff_visit_credit_times'	=> array('title'=>'每日访问推广奖励积分次数',	'type'=>'text',	'description'=>'每天通过访问推广获得积分的最大次数，超出后将不再获得积分'),
		'aff_reg_credit'	=> array('title'=>'注册推广一次奖励积分',	'type'=>'text',	'description'=>'通过专属推广链接推广新用户注册一次所奖励积分'),
		'aff_reg_credit_times'	=> array('title'=>'每日注册推广奖励积分次数',	'type'=>'text',	'description'=>'每天通过注册推广获得积分的最大次数，超出后将不再获得积分'),
		'source_download_credit'	=> array('title'=>'发布资源被下载奖励积分',	'type'=>'text',	'description'=>'作者发布的资源被用户下载后获得积分奖励，不限次数')
	);

	$mail_fields = array(
		'smtp_switch'	=> array('title'=>'启用SMTP发信',	'type'=>'checkbox',	'description'=>'如果主机商禁用了PHP Mail()，请使用SMTP发信，仍有任何问题请参考<a target="_blank" href="http://www.zhiyanblog.com/virtual-host-send-mail-via-smtp.html">虚拟主机SMTP发信</a>'),
		'smtp_host'	=> array('title'=>'SMTP发信服务器',	'type'=>'text',	'description'=>'SMTP发信服务器，例如smtp.163.com'),
		'smtp_port'	=> array('title'=>'SMTP发信服务器端口',	'type'=>'text',	'description'=>'SMTP发信服务器端口，不开启SSL时一般默认25，开启SSL一般为465'),
		'smtp_ssl'	=> array('title'=>'SMTP发信服务器SSL连接',	'type'=>'checkbox',	'description'=>'SMTP发信服务器SSL连接，请相应修改端口'),
		'smtp_account'	=> array('title'=>'SMTP发信用户名',	'type'=>'text',	'description'=>'SMTP发信用户名，一般为完整邮箱号'),
		'smtp_pass'	=> array('title'=>'SMTP帐号密码',	'type'=>'password',	'description'=>''),
		'smtp_name'	=> array('title'=>'SMTP发信人昵称',	'type'=>'text',	'description'=>''),
		'comment_reply_mail'	=> array('title'=>'评论邮件提醒',	'type'=>'checkbox',	'description'=>'开启评论回复邮件提醒'),
		'login_mail'	=> array('title'=>'登录成功邮件提醒',	'type'=>'checkbox',	'description'=>'登录成功时邮件提醒管理员邮箱'),
		'login_error_mail'	=> array('title'=>'登录错误邮件提醒',	'type'=>'checkbox',	'description'=>'登陆错误时邮件提醒管理员邮箱'),
		'logo_img'	=> array('title'=>'邮件模板Logo',	'type'=>'image',	'description'=>'邮件模板的站点Logo图像，留空则采用站点标题')
	);

	$social_fields = array(
		'um_open_qq'	=> array('title'=>'QQ快速登录',	'type'=>'checkbox',	'description'=>'在登录弹窗等区域显示QQ快速登录按钮，需要自行申请APP KEY'),
		'um_open_qq_id'	=> array('title'=>'QQ开放平台ID',	'type'=>'text',	'description'=>''),
		'um_open_qq_key'	=> array('title'=>'QQ开放平台KEY',	'type'=>'text',	'description'=>''),
		'um_open_weibo'	=> array('title'=>'微博快速登录',	'type'=>'checkbox',	'description'=>'在登录弹窗等区域显示新浪微博快速登录按钮，需要自行申请APP KEY'),
		'um_open_weibo_key'	=> array('title'=>'微博开放平台KEY',	'type'=>'text',	'description'=>''),
		'um_open_weibo_secret'	=> array('title'=>'微博开放平台SECRET',	'type'=>'text',	'description'=>''),
		'um_open_role'	=> array('title'=>'新登录用户角色',	'type'=>'select',	'description'=>'', 'options'=>array('订阅者'=>'subscriber','投稿者'=>'contributor','作者'=>'author','编辑'=>'editor'))
	);

	$other_fields = array(
		'font_awesome'	=> array('title'=>'启用Font-Awesome图标字体',	'type'=>'checkbox',	'description'=>'如果主题未包含font awesome字体，请开启该选项以保证图标应用功能正常'),
		'custom_login_logo'	=> array('title'=>'登录页Logo',	'type'=>'image',	'description'=>'登录页自定义Logo图像，留空则采用默认WordPress标志'),
		'bing_login_bg'	=> array('title'=>'登录页Bing动态背景',	'type'=>'checkbox',	'description'=>'登录页采用Bing动态壁纸做背景，每日更新')
	);

	$auth_fields = array(
		'order_id'	=> array('title'=>'订单号',	'type'=>'text',	'description'=>'请输入在<a href="http://www.zhiyanblog.com/store/goods/wordpress-plugin-ucenter-and-market.html" target="_blank" title="Ucenter&amp;Market购买">购买Ucenter&amp;Market插件</a>的订单号'),
		'sn'	=> array('title'=>'授权码',	'type'=>'text',	'description'=>'请输入在购买该插件后凭订单号至<a href="http://www.zhiyanblog.com/cdn/tinection/authorize.php" target="_blank" title="验证授权">Ucenter&amp;Market验证授权</a>获取到的授权码')
	);

	$sections = array( 
    	'membership'	=>array('title'=>'会员设置',		'fields'=>$membership_fields,	'callback'=>'',	),
    	'ucenter'		=>array('title'=>'用户中心设置',		'fields'=>$ucenter_fields,	'callback'=>'',	),
    	'store'		=>array('title'=>'商城设置',		'fields'=>$store_fields,	'callback'=>'',	),
    	'payment'		=>array('title'=>'支付宝设置',		'fields'=>$payment_fields,	'callback'=>'',	),
    	'credit'		=>array('title'=>'积分设置',		'fields'=>$credit_fields,	'callback'=>'',	),
    	'mail'		=>array('title'=>'邮件设置',	'fields'=>$mail_fields,	'callback'=>'',	),
    	'social'	=> array('title'=>'社会化登录设置',	'fields'=>$social_fields,	'callback'=>'',	),
    	'other'	=> array('title'=>'其他设置',		'fields'=>$other_fields,'callback'=>'um_other_field_callback',	),
    	'auth'	=> array('title'=>'插件授权激活',		'fields'=>$auth_fields,'callback'=>'um_auth_field_callback',	)
	);

	return compact('option_group','option_name','option_page','sections','field_validate');
}

function um_auth_field_callback(){
	?>
	<p><?php echo base64_decode('5b2T5YmN5r+A5rS754q25oCBOg=='); ?><?php if(um_authorize()) echo base64_decode('5bey5r+A5rS777yM5Y+v5L2/55So5YWo6YOo5Yqf6IO9');else echo base64_decode('5pyq5r+A5rS777yM5Y+v5L2/55So6YOo5YiG5Yqf6IO9'); ?></p>
	<?php
}

function um_other_field_callback(){
	echo '';
}

function um_admin_init() {
	um_add_settings(um_get_option_labels());
}
add_action( 'admin_init', 'um_admin_init' );

function um_add_settings($labels){
	extract($labels);
	register_setting( $option_group, $option_name, $field_validate );

	$field_callback = empty($field_callback)?'um_option_field_callback' : $field_callback;
	if($sections){
		foreach ($sections as $section_name => $section) {
			add_settings_section( $section_name, $section['title'], $section['callback'], $option_page );

			$fields = isset($section['fields'])?$section['fields']:(isset($section['fields'])?$section['fields']:'');

			if($fields){
				foreach ($fields as $field_name=>$field) {
					$field['option']	= $option_name;
					$field['name']		= $field_name;

					$field_title		= $field['title'];

					$field_title = '<label for="'.$field_name.'">'.$field_title.'</label>';

					add_settings_field( 
						$field_name,
						$field_title,		
						$field_callback,	
						$option_page, 
						$section_name,	
						$field
					);	
				}
			}
		}
	}
}

/* Get checkbox type options */
function um_option_get_checkbox_settings($labels){
	$sections = $labels['sections'];
	$checkbox_options = array();
	foreach ($sections as $section) {
		$fields = $section['fields'];
		foreach ($fields as $field_name => $field) {
			if($field['type'] == 'checkbox'){
				$checkbox_options[] = $field_name;
			}
		}
	}
	return $checkbox_options;
}

/* Checkbox validate */
function ucenter_market_validate( $ucenter_market ) {
	$current = get_option( 'ucenter_market' );

	foreach (array('open_ucenter','smtp_switch','smtp_ssl','comment_reply_mail','login_mail','login_error_mail','open_qq','open_weibo','font_awesome','bing_login_bg') as $key ) {
		if(empty($ucenter_market[$key])){ //checkbox 未选，Post 的时候 $_POST 中是没有的，
			$ucenter_market[$key] = 0;
		}
	}

	flush_rewrite_rules();

	return $ucenter_market;
}

/* Copy from do_settings_sections, display options of tabs */
function um_option_do_settings_section($option_page, $section_name){
	global $wp_settings_sections, $wp_settings_fields;

	if ( ! isset( $wp_settings_sections[$option_page] ) )
		return;

	$section = $wp_settings_sections[$option_page][$section_name];

	if ( $section['title'] )
		echo "<h3>{$section['title']}</h3>\n";

	if ( $section['callback'] )
		call_user_func( $section['callback'], $section );

	if ( isset( $wp_settings_fields ) && isset( $wp_settings_fields[$option_page] ) && !empty($wp_settings_fields[$option_page][$section['id']] ) ){
		echo '<table class="form-table">';
		do_settings_fields( $option_page, $section['id'] );
		echo '</table>';
	}
}

/* Defaults value */
function um_option_defaults(){
	$name = get_bloginfo('name');
	$defaults = array(
			'monthly_mb_price'		=>	5,
			'quarterly_mb_price'	=>	12,
			'annual_mb_price'		=>	45,
			'life_mb_price'			=>	120,
			'monthly_mb_disc'		=>	0.95,
			'quarterly_mb_disc'		=>	0.90,
			'annual_mb_disc'		=>	0.85,
			'life_mb_disc'			=>	0.75,
			'open_ucenter'			=>	1,
			'store_archive_slug'	=>	'store',
			'product_link_mode'		=>	'post_id',
			'store_archive_title'	=>	'WordPress商城',
			'store_archive_subtitle'=>	'Themes - Service - Resource',
			'store_archive_des'		=>	$name.'商城',
			'store_cat_pre'			=>	'category',
			'store_tag_pre'			=>	'tag',
			'alipay_sign_type'		=>	'trade_create_by_buyer',
			'aff_ratio'				=>	10,
			'aff_discharge_lowest'	=>	100,
			'exchange_ratio'		=>	100,
			'new_reg_credit'		=>	50,
			'daily_sign_credit'		=>	10,
			'comment_credit'		=>	5,
			'comment_credit_times'	=>	20,
			'like_article_credit'	=>	5,
			'like_article_credit_times'	=>	5,
			'contribute_credit'		=>	100,
			'contribute_credit_times'=>	5,
			'aff_visit_credit'		=>	10,
			'aff_visit_credit_times'=>	10,
			'aff_reg_credit'		=>	20,
			'aff_reg_credit_times'	=>	5,
			'source_download_credit'=>	10,
			'smtp_port'				=>	465,
			'smtp_ssl'				=>	1,
			'smtp_name'				=>	$name,
			'comment_reply_mail'	=>	0,
			'login_mail'			=>	0,
			'login_error_mail'		=>	1,
			'logo_img'				=>	'',
			'um_open_qq'			=>	0,
			'um_open_weibo'			=>	0,
			'um_open_role'			=>	'contributor',
			'font_awesome'			=>	1,
			'bing_login_bg'			=>	0
		);
	return $defaults;
}

/* Get options filtered by defaults */
function um_get_option($option_name){
	$options = get_option( $option_name );
	if($options && !is_admin()){
		return $options;
	}else{
		$defaults = um_option_defaults();
		return wp_parse_args($options, $defaults);
	}
}

/* Get setting value */
function um_get_setting($setting_name,$default=''){
	$option = get_option('ucenter_market');
	if(isset($option[$setting_name])){
		return str_replace("\r\n", "\n", $option[$setting_name]);
	}else{
		return $default;
	}
}

/* Upload image JS */
function um_upload_image_enqueue_scripts() {
    wp_enqueue_media();
    wp_enqueue_script('um-upload-image', plugins_url('../static/setting.js', __FILE__), array('jquery'));
    ?>
    <style type="text/css">
    	body #wpadminbar *, body #wpwrap {font-family: consolas,"Microsoft Yahei";}
	</style>
    <?php
}
add_action('admin_enqueue_scripts', 'um_upload_image_enqueue_scripts');

?>