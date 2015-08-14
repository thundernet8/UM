<?php

/**
 * Main Functions of Ucenter & Market WordPress Plugin
 *
 * @package   Ucenter & Market
 * @version   1.0
 * @date      2015.4.13
 * @author    Zhiyan <chinash2010@gmail.com>
 * @site      Zhiyanblog <www.zhiyanblog.com>
 * @copyright Copyright (c) 2015-2015, Zhiyan
 * @license   http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link      http://www.zhiyanblog.com/wordpress-plugin-ucenter-and-market.html
**/

?>
<?php
/* Create database for orders and coupon code */
function create_withdraw_table(){
		global $wpdb;
		include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
		$table_charset = '';
		$prefix = $wpdb->prefix;
		$withdraw_table = $prefix.'um_withdraw';
		if($wpdb->has_cap('collation')) {
			if(!empty($wpdb->charset)) {
				$table_charset = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if(!empty($wpdb->collate)) {
				$table_charset .= " COLLATE $wpdb->collate";
			}		
		}
		$create_withdraw_sql = "CREATE TABLE $withdraw_table (id int(11) NOT NULL auto_increment,user_id int(11) NOT NULL,time datetime NOT NULL default '0000-00-00 00:00:00',money double(10,2) NOT NULL default 0,balance double(10,2) NOT NULL default 0,status int(1) NOT NULL default 0,PRIMARY KEY (id)) ENGINE = MyISAM $table_charset;";
		maybe_create_table($withdraw_table,$create_withdraw_sql);
}
add_action('admin_menu','create_withdraw_table');

// Get withdraw records
function um_withdraw_records($uid,$limit=0, $offset=0){
	$uid = (int)$uid;
	if(!$uid)return;
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_withdraw';
	$check = $wpdb->get_results( "SELECT * FROM $table_name WHERE user_id='".$uid."' ORDER BY id DESC" );
	if($check)	return $check;
	return 0;
}

// Get user withdrawed and withdrawing money
function um_get_withdraw_sum($uid,$status=''){
	$uid = (int)$uid;
	if(!$uid)return;
	$where = "WHERE user_id='".$uid."'";
	if($status=='withdrawing'){
		$where .= ' AND status=0';
	}elseif($status=='withdrawed'){
		$where .= ' AND status=1';
	}
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_withdraw';
	$check = $wpdb->get_var( "SELECT SUM(money) FROM $table_name $where" );
	if($check)	return $check;
	return 0;
}

// Add withdraw record
function um_add_withdraw_record($uid,$time='',$money,$balance,$status){
	if(!$uid) return;
	if(!$time)$time=current_time('mysql');
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_withdraw';
	$row=$wpdb->query("INSERT INTO $table_name (user_id,time,money,balance,status) VALUES ('$uid','$time','$money','$balance','$status')");
	return $row;
}

// Update a withdraw record
function um_update_withdraw_record($id,$status){
	if(!$id) return;
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_withdraw';
	$row=$wpdb->query("UPDATE $table_name SET status= '$status' WHERE id='$id'");
	return $row;
}

// AJAX request withdraw
function um_withdraw_request(){
	$uid = get_current_user_id();
	$success = 0;
	$msg = '';
	if(!$uid)return;
	$num = isset($_POST['money'])?$_POST['money']:0;
	$withdraw = um_get_withdraw_sum($uid,'');
	$sum = 	get_um_aff_sum_money($uid);
	$left = $sum-$withdraw-$num;
	if(0>$left){$msg='提现金额不能大于推广余额';}else{
		$withdraw = $withdraw+$num;
		$add = um_add_withdraw_record($uid,'',$num,$left,0);
		if($add):
		um_withdraw_admin_email($uid,$num,$left);
		$success = 1;
		$msg = '提现申请提交成功，请等待管理员处理';
		else:
		$msg = '提现申请提交失败，请刷新页面再试';
		endif;
	}
	$return = json_encode(array('success'=>$success,'msg'=>$msg));
	echo $return;
	exit;
}
add_action( 'wp_ajax_withdraw', 'um_withdraw_request' );
//add_action( 'wp_ajax_nopriv_withdraw', 'um_withdraw_request' );

// Withdraw admin email
function um_withdraw_admin_email($uid,$num,$left){
	$blogname =  get_bloginfo('name');
	$admin_email = get_bloginfo ('admin_email');
	$user_name = get_userdata($uid)->display_name;
	$alipay_account = get_userdata($uid)->um_alipay_email;
	$user_ucenter_url = um_get_user_url('affiliate',$uid);
	$num = sprintf('%0.2f',$num);
	$left = sprintf('%0.2f',$left);
	$balance_before = $num+$left;
	$balance_before = sprintf('%0.2f',$balance_before);
	$time = date('Y年m月j日 H:i:s');
	$title=$blogname.'新推广提现请求';
	$wp_email = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
	$fr = "From: \"" . $blogname . "\" <$wp_email>";
	$headers = "$fr\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
	$content_admin = '<p>你的站点有一笔新的推广提现请求，请及时处理，以下是详细信息:</p><div style="background-color:#fefcc9; padding:10px 15px; border:1px solid #f7dfa4; font-size: 12px;line-height:160%;">申请人：<a href="'.$user_ucenter_url.'" title="用户个人中心" target="_blank">'.$user_name.'</a><br>支付宝账号：'.$alipay_account.'元<br>推广余额：'.$balance_before.'元<br>申请提现：'.$num.'元<br>提现后余额：'.$left.'<br>发起时间：'.$time.'</div>';
	$html_admin = store_email_template_wrap('',$content_admin);
	wp_mail( $admin_email, $title, $html_admin, $headers );
}

// AJAX admin confirm payed the withdraw request
function um_admin_confirm_payed_withdraw(){
	$id = isset($_POST['id'])?$_POST['id']:0;
	if(!$id)return;
	$success = 0;
	$msg = '';
	$check = um_update_withdraw_record($id,1);
	if($check){
		$success = 1;
		um_confirm_withdraw_email_user($id);
	}else{
		$msg = '确认出错，请刷新页面再试';
	}
	$return = json_encode(array('success'=>$success,'msg'=>$msg));
	echo $return;
	exit;
}
add_action( 'wp_ajax_confirm_payed_withdraw', 'um_admin_confirm_payed_withdraw' );
//add_action( 'wp_ajax_nopriv_confirm_payed_withdraw', 'um_admin_confirm_payed_withdraw' );

// Email user notice payed a withdraw request
function um_confirm_withdraw_email_user($id){
	if(!$id) return;
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_withdraw';
	$check = $wpdb->get_row( "SELECT * FROM $table_name WHERE id='".$id."'" );
	if($check){
		$uid = $check->user_id;
		$num = $check->money;
		$balance = $check->balance;
		$balance_before = $num+$balance;
		$time = $check->time;
		$status = $check->status==1?'已支付':'处理中';
		$blogname =  get_bloginfo('name');
		$bloghome = get_bloginfo('url');
		$admin_email = get_bloginfo ('admin_email');
		$user_email = get_userdata ($uid)->user_email;
		$user_name = get_userdata($uid)->display_name;
		$alipay_account = get_userdata($uid)->um_alipay_email;
		$user_ucenter_url = um_get_user_url('affiliate',$uid);
		$title='推广提现请求处理通知';
		$wp_email = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
		$fr = "From: \"" . $blogname . "\" <$wp_email>";
		$headers = "$fr\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
		$content = '<p><strong>亲爱的会员'.$user_name.' 您好：</strong></p><p>您于'.$time.'在'.$blogname.'( <a target="_blank" href="'.$bloghome.'">'.$bloghome.'</a>)发起一笔推广提现请求，现在管理员已确认处理，以下是您的请求信息，您可进入个人中心-“<a target="_blank" href="'.$user_ucenter_url.'">我的推广</a>”页面查看详细内容，同时留意在本站提供的支付账号等的余额变动，如有任何疑问，请及时联系我们（Email:<a href="mailto:'.$admin_email.'" target="_blank">'.$admin_email.'</a>）。</p>';
		$content .= '<div style="background-color:#fefcc9; padding:10px 15px; border:1px solid #f7dfa4; font-size: 12px;line-height:160%;">申请人：<a href="'.$user_ucenter_url.'" title="用户个人中心" target="_blank">'.$user_name.'</a><br>支付宝账号：'.$alipay_account.'元<br>推广余额：'.$balance_before.'元<br>申请提现：'.$num.'元<br>提现后余额：'.$balance.'<br>发起时间：'.$time.'<br>请求状态：'.$status.'</div>';
		$html = store_email_template_wrap('',$content);
		wp_mail( $user_email, $title, $html, $headers );
	}
	return;
}

// Output withdraw status
function um_withdraw_status_output($status,$id){
	if($status){$td1 = '已支付';$td2='';}else{$td1 = '等待处理';$td2='<a href="javascript:" class="confirm_payed_withdraw" style="color:#f00;" data-id="'.$id.'">确认已支付</a>';}
	if(current_user_can('edit_users')){return $td1.'</td><td>'.$td2;}else{return $td1;}
}

// Affiliate records
function get_um_aff_orders( $uid , $count=0, $currency='', $limit=0, $offset=0 ){
	$uid = intval($uid);
	if( !$uid ) return;
	$where = "WHERE order_status=4 AND aff_user_id='".$uid."'";
	if($currency){
		$where .= " AND order_currency='".$currency."'"; 
	}
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_orders';
	if($count){		
		$check = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $where" );
	}else{
		$check = $wpdb->get_results( "SELECT user_id,order_total_price,aff_rewards,user_name FROM $table_name $where ORDER BY id DESC LIMIT $offset,$limit" );
	}
	if($check)	return $check;
	return 0;
}

// Affiliate records summary
function get_um_aff_sum_orders( $uid , $currency='cash', $limit=0, $offset=0 ){
	$uid = intval($uid);
	if( !$uid ) return;
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_orders';
	$sql = "SELECT user_id,SUM(order_total_price) AS total_cost,SUM(aff_rewards) AS total_rewards FROM $table_name WHERE order_status=4 AND aff_user_id=".$uid." AND order_currency='".$currency."' GROUP BY user_id ASC";
	$check = $wpdb->get_results( $sql );
	if($check)	return $check;
	return 0;	
}

// Affiliate money sum
function get_um_aff_sum_money($uid){
	if(!$uid)return;
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_orders';
	$sql = "SELECT SUM(aff_rewards) FROM $table_name WHERE aff_user_id=".$uid." AND order_status=4 AND order_currency='cash'";
	$check = $wpdb->get_var( $sql );
	if($check) return $check;
	return 0;
}

?>