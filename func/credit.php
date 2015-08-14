<?php

/**
 * Main Functions of Ucenter & Market WordPress Plugin
 *
 * @package   Ucenter & Market
 * @version   1.0
 * @date      2015.4.1
 * @author    Zhiyan <chinash2010@gmail.com>
 * @site      Zhiyanblog <www.zhiyanblog.com> | Dmeng <www.dmeng.net>
 * @copyright Copyright (c) 2015-2015, Zhiyan&Dmeng
 * @license   http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link      http://www.zhiyanblog.com/wordpress-plugin-ucenter-and-market.html
**/

?>
<?php

/* 更新用户积分
/* ------------- */
function update_um_credit( $user_id , $num , $method='add' , $field='um_credit' , $msg='' ){
	 
	if( !is_numeric($user_id)  ) return;

	$field = $field=='um_credit' ? $field : 'um_credit_void';
	
	$credit = (int)get_user_meta( $user_id, $field, true );
	$num = (int)$num;

	if( $method=='add' ){
		
		$add = update_user_meta( $user_id , $field, ( ($credit+$num)>0 ? ($credit+$num) : 0 ) );
		if( $add ){
			add_um_message( $user_id ,  'credit' , current_time('mysql') , ($msg ? $msg : sprintf( __('获得%s积分','um') , $num )) );
			return $add;
		}
	}
	
	if($method=='cut'){
		
		$cut = update_user_meta( $user_id , $field, ( ($credit-$num)>0 ? ($credit-$num) : 0 )  );
		if( $cut ){
			add_um_message( $user_id ,  'credit' , current_time('mysql') , ($msg ? $msg : sprintf( __('消费%s积分','um') , $num )) );
			return $cut;
		}
	}
	
	$update = update_user_meta( $user_id , $field, $num );
	if( $update ){
		add_um_message( $user_id ,  'credit' , current_time('mysql') , ($msg ? $msg : sprintf( __('更新积分为%s','um') , $num )) );
		return $update;
	}

}

/* 用户已消费积分
/* ---------------- */
function um_credit_to_void( $user_id , $num, $msg='' ){
	if( !is_numeric($user_id) || !is_numeric($num) ) return;
	$credit = (int)get_user_meta( $user_id, 'um_credit' , true );
	$num = (int)$num;
	if($credit<$num) return 'less';
	$cut = update_user_meta( $user_id , 'um_credit' , ($credit-$num) );
	$credit_void = (int)get_user_meta( $user_id, 'um_credit_void' , true );
	$add = update_user_meta( $user_id , 'um_credit_void' , ($credit_void+$num) );
	add_um_message( $user_id ,  'credit' , current_time('mysql') , ($msg ? $msg : sprintf( __('消费了%s积分','um') , $num )) );
	return 0;	
}

/* 用户注册时添加推广人和奖励积分
/* --------------------------------- */
function user_register_update_um_credit( $user_id ) {
    if( isset($_COOKIE['um_aff']) && is_numeric($_COOKIE['um_aff']) ){
    	//链接推广人与新注册用户(推广人meta)
		if(get_user_meta( $_COOKIE['um_aff'], 'um_aff_users', true)){
			$aff_users = get_user_meta( $_COOKIE['um_aff'], 'um_aff_users', true);
			if(empty($aff_users)){$aff_users=$user_id;}else{$aff_users .= ','.$user_id;}				
			update_user_meta( $_COOKIE['um_aff'], 'um_aff_users', $aff_users);
		}else{
			update_user_meta( $_COOKIE['um_aff'], 'um_aff_users', $user_id);
		}
    	//链接推广人与新注册用户(注册人meta)
		update_user_meta( $user_id, 'um_aff', $_COOKIE['um_aff'] );
		$rec_reg_num = (int)um_get_setting('aff_reg_credit_times','5');
		$rec_reg = json_decode(get_user_meta( $_COOKIE['um_aff'], 'um_rec_reg', true ));
		$ua = $_SERVER["REMOTE_ADDR"].'&'.$_SERVER["HTTP_USER_AGENT"];
		if(!$rec_reg){
			$rec_reg = array();
			$new_rec_reg = array($ua);
		}else{
			$new_rec_reg = $rec_reg;
			array_push($new_rec_reg , $ua);
		}
		if( (count($rec_reg) < $rec_reg_num) &&  !in_array($ua,$rec_reg) ){
			update_user_meta( $_COOKIE['um_aff'] , 'um_rec_reg' , json_encode( $new_rec_reg ) );

			$reg_credit = (int)um_get_setting('aff_reg_credit','20');
			if($reg_credit) update_um_credit( $_COOKIE['um_aff'] , $reg_credit , 'add' , 'um_credit' , sprintf(__('获得注册推广（来自%1$s的注册）奖励%2$s积分','um') , get_the_author_meta('display_name', $user_id) ,$reg_credit) );
		}
	}
	$credit = um_get_setting('new_reg_credit','50');
	if($credit){
		update_um_credit( $user_id , $credit , 'add' , 'um_credit' , sprintf(__('获得注册奖励%s积分','um') , $credit) );
	}
}
add_action( 'user_register', 'user_register_update_um_credit');

/* 访问推广检查
/* -------------- */
function hook_um_affiliate_check_to_tracker_ajax(){
	if( isset($_COOKIE['um_aff']) && is_numeric($_COOKIE['um_aff']) ){
		$rec_view_num = (int)um_get_setting('aff_visit_credit_times','10');
		$rec_view = json_decode(get_user_meta( $_COOKIE['um_aff'], 'um_rec_view', true ));
		$ua = $_SERVER["REMOTE_ADDR"].'&'.$_SERVER["HTTP_USER_AGENT"];
		if(!$rec_view){
			$rec_view = array();
			$new_rec_view = array($ua);
		}else{
			$new_rec_view = $rec_view;
			array_push($new_rec_view , $ua);
		}
		//推广人推广访问数量，不受每日有效获得积分推广次数限制，但限制同IP且同终端刷分
		if( !in_array($ua,$rec_view) ){
			$aff_views = (int)get_user_meta( $_COOKIE['um_aff'], 'um_aff_views', true);
			$aff_views++;
			update_user_meta( $_COOKIE['um_aff'], 'um_aff_views', $aff_views);
		}
		//推广奖励，受每日有效获得积分推广次数限制及同IP终端限制刷分
		if( (count($rec_view) < $rec_view_num) && !in_array($ua,$rec_view) ){
			update_user_meta( $_COOKIE['um_aff'] , 'um_rec_view' , json_encode( $new_rec_view ) );
			$view_credit = (int)um_get_setting('aff_visit_credit','10');
			if($view_credit) update_um_credit( $_COOKIE['um_aff'] , $view_credit , 'add' , 'um_credit' , sprintf(__('获得访问推广奖励%1$s积分','um') ,$view_credit) );
			//历史推广获得总积分
			$um_aff_view_credit = (int)get_user_meta( $_COOKIE['um_aff'],'um_aff_view_credit',true );
			$um_aff_view_credit += $view_credit;
			update_user_meta( $_COOKIE['um_aff'],'um_aff_view_credit',$um_aff_view_credit );
		}
	}
}
add_action( 'um_tracker_ajax_callback', 'hook_um_affiliate_check_to_tracker_ajax');

/* 资源被下载时作者获得积分奖励
/* ----------------------------- */
function um_resource_dl_add_credit($uid,$pid,$sid){
	$current_user = get_current_user_id() ? get_current_user_id() : 0;
	$dl_users = json_decode(get_user_meta( $uid, 'um_resource_dl_users', true ));
	$ua = $_SERVER["REMOTE_ADDR"].'&'.$_SERVER["HTTP_USER_AGENT"].'&'.$pid.'&'.$sid;
	if(!$dl_users){
		$dl_users = array();
		$new_dl_users = array($ua);
	}else{
		$new_dl_users = $dl_users;
		array_push($new_dl_users , $ua);
	}
	if( !in_array($ua,$dl_users) && $current_user != $uid){
		update_user_meta( $uid , 'um_resource_dl_users' , json_encode( $new_dl_users ) );
		$dl_credit = (int)ot_get_option('um_rec_resource_dl_credit','5');
		if($dl_credit) update_um_credit( $uid , $dl_credit , 'add' , 'um_credit' , sprintf(__('你发布的文章《%1$s》中资源被其他用户下载，奖励%2$s积分','um') ,get_post_field('post_title',$pid),$dl_credit) );
	}
}

/* 发表评论时给作者添加积分
/* ------------------------- */
function um_comment_add_credit($comment_id, $comment_object){
	
	$user_id = $comment_object->user_id;
	
	if($user_id){
		
		$rec_comment_num = (int)um_get_setting('comment_credit_times','20');
		$rec_comment_credit = (int)um_get_setting('comment_credit','5');
		$rec_comment = (int)get_user_meta( $user_id, 'um_rec_comment', true );
		
		if( $rec_comment<$rec_comment_num && $rec_comment_credit ){
			update_um_credit( $user_id , $rec_comment_credit , 'add' , 'um_credit' , sprintf(__('获得评论回复奖励%1$s积分','um') ,$rec_comment_credit) );
			update_user_meta( $user_id, 'um_rec_comment', $rec_comment+1);
		}
	}
}
add_action('wp_insert_comment', 'um_comment_add_credit' , 99, 2 );

/* 每天 00:00 清空推广数据
/* ------------------------- */
function clear_um_rec_setup_schedule() {
	if ( ! wp_next_scheduled( 'clear_um_rec_daily_event' ) ) {
		//~ 1193875200 是 2007/11/01 00:00 的时间戳
		wp_schedule_event( '1193875200', 'daily', 'clear_um_rec_daily_event');
	}
}
add_action( 'wp', 'clear_um_rec_setup_schedule' );

function clear_um_rec_do_this_daily() {
	global $wpdb;
	$wpdb->query( " DELETE FROM $wpdb->usermeta WHERE meta_key='um_rec_view' OR meta_key='um_rec_reg' OR meta_key='um_rec_post' OR meta_key='um_rec_comment' OR meta_key='um_resource_dl_users' " );
}
add_action( 'clear_um_rec_daily_event', 'clear_um_rec_do_this_daily' );

//~ 在后台用户列表中显示积分
function um_credit_column( $columns ) {
	$columns['um_credit'] = __('积分','um');
	return $columns;
}
add_filter( 'manage_users_columns', 'um_credit_column' );
 
function um_credit_column_callback( $value, $column_name, $user_id ) {

	if( 'um_credit' == $column_name ){
		$credit = intval(get_user_meta($user_id,'um_credit',true));
		$void = intval(get_user_meta($user_id,'um_credit_void',true));
		$value = sprintf(__('总积分 %1$s 已消费 %2$s','um'), ($credit+$void), $void );
	}
	return $value;
}
add_action( 'manage_users_custom_column', 'um_credit_column_callback', 10, 3 );

//~ 用户积分排行
function um_credits_rank($limits=10){
	global $wpdb;
	$limits = (int)$limits;
	$ranks = $wpdb->get_Results( " SELECT * FROM $wpdb->usermeta WHERE meta_key='um_credit' ORDER BY -meta_value ASC LIMIT $limits" );
	return $ranks;
}

//~ 每日签到
function um_whether_signed($user_id){
	if(get_user_meta($user_id,'um_daily_sign',true)){
		date_default_timezone_set ('Asia/Shanghai');
		$sign_date_meta = get_user_meta($user_id,'um_daily_sign',true);
		$sign_date = date('Y-m-d',strtotime($sign_date_meta));
		$now_date = date('Y-m-d',time());
		if($sign_date != $now_date){
			$sign_anchor = '<a href="javascript:" id="daily_sign" title="签到送积分">'.__('签到','um').'</a>';
		}else{
			$sign_anchor = '<a href="javascript:" id="daily_signed" title="已于'.$sign_date_meta.'签到" style="cursor:default;">'.__('今日已签到','um').'</a>';
		}
	}else{
		$sign_anchor = '<a href="javascript:" id="daily_sign" title="签到送积分">'.__('签到','um').'</a>';
	}
	return $sign_anchor;
}

function um_daily_sign_callback(){
	date_default_timezone_set ('Asia/Shanghai');
	$msg = '';
	$success = 0;
	$credits = 0;
	if(!is_user_logged_in()){$msg='请先登录';}else{
		$uid = get_current_user_id();
		$date = date('Y-m-d H:i:s',time());
		$sign_date_meta = get_user_meta($uid,'um_daily_sign',true);
		$sign_date = date('Y-m-d',strtotime($sign_date_meta));
		$now_date = date('Y-m-d',time());
		if($sign_date != $now_date):
			update_user_meta($uid,'um_daily_sign',$date);
			$credits = um_get_setting('daily_sign_credit',10);
			$credit_msg = '每日签到赠送'.$credits.'积分';
			update_um_credit( $uid , $credits , 'add' , 'um_credit' , $credit_msg );
			$success = 1;
			$msg = '签到成功，获得'.$credits.'积分';
		else:
			$success = 0;
			$credits = 0;
			$msg = '今日已签到';
		endif;
	}
	$return = array('msg'=>$msg,'success'=>$success,'credits'=>$credits);
	echo json_encode($return);
	exit;
}
add_action( 'wp_ajax_daily_sign', 'um_daily_sign_callback' );
//add_action( 'wp_ajax_nopriv_daily_sign', 'um_daily_sign_callback' );

?>