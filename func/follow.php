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
/* Create database */
// user_id -> be followed
// follow_user_id -> follower
function um_follow_install(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'um_follow';   
    if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) :   
		$sql = " CREATE TABLE `$table_name` (
			`id` int NOT NULL AUTO_INCREMENT, 
			PRIMARY KEY(id),
			INDEX uid_index(user_id),
			INDEX fuid_index(follow_user_id),
			`user_id` int,
			`follow_user_id` int,
			`follow_status` int,
			`follow_time` datetime
		) ENGINE = MyISAM CHARSET=utf8;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');   
			dbDelta($sql);   
    endif;
}
add_action( 'admin_menu', 'um_follow_install' ); 

/* Following */
function um_following($uid,$limits){
	$uid = (int)$uid;
	$limits = (int)$limits;
	if(!$uid) return 0;
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_follow';
	$results = $wpdb->get_results("SELECT * FROM $table_name WHERE follow_user_id='$uid' AND follow_status IN(1,2) ORDER BY follow_time DESC LIMIT $limits");
	return $results;
}

function um_following_count($uid){
	$uid = (int)$uid;
	if(!$uid) return 0;
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_follow';
	$results = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE follow_user_id='$uid' AND follow_status IN(1,2)");
	return $results;
}

/* Follower */
function um_follower($uid,$limits){
	$uid = (int)$uid;
	$limits = (int)$limits;
	if(!$uid) return 0;
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_follow';
	$results = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='$uid' AND follow_status IN(1,2) ORDER BY follow_time DESC LIMIT $limits");
	return $results;
}

function um_follower_count($uid){
	$uid = (int)$uid;
	if(!$uid) return 0;
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_follow';
	$results = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE user_id='$uid' AND follow_status IN(1,2)");
	return $results;
}

/* Following and follower avatar html output */
function um_follow_list($uid,$limits,$type='follower'){
	if($type=='following'){$results = um_following($uid,$limits);$field='user_id';} else {$results = um_follower($uid,$limits);$field='follow_user_id';}
	$html = '';
	if($results)
		foreach($results as $result){
			$user = get_userdata($result->$field);
			$username = $user->display_name;
			$html .= '<li class="flow" title="'.$username.'"><a href="'.um_get_user_url('post',$user->ID).'" target="_blank">'.um_get_avatar( $result->$field , '40' , um_get_avatar_type($result->$field) ).'</a><span class="name">'.$username.'</span></li>';
		}
	return $html;
}

/* AJAX follow & disfollow */
function um_follow_unfollow(){
	date_default_timezone_set ('Asia/Shanghai');
	$followed = (int)$_POST['followed'];
	$follower = get_current_user_id();
	$action = isset($_POST['act'])&&$_POST['act']=='disfollow'?'disfollow':'follow';
	$success=0;
	$msg = '';
	$type = 0;
	//if ( !wp_verify_nonce( trim($_POST['wp_nonce']), 'check-nonce' ) ){
		//echo 'NonceIsInvalid';
		//die();
	//}
	if($follower&&$follower!=$followed){
		global $wpdb;
		$table_name = $wpdb->prefix . 'um_follow';
		if($action=='disfollow'){
			$check = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE user_id='$followed' AND follow_user_id='$follower'");
			$status = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE user_id='$follower' AND follow_user_id='$followed' AND follow_status IN(1,2)");
			$status1 = 0;
			$status2 = $status?1:0;
			if($check){
				if($wpdb->query("UPDATE $table_name SET follow_status = '$status1' WHERE user_id='$followed' AND follow_user_id='$follower'")){
					$success = 1;
					$msg = '取消关注成功';
					$wpdb->query("UPDATE $table_name SET follow_status = '$status2' WHERE user_id='$follower' AND follow_user_id='$followed'");
				}else{$msg = '取消关注失败,请重试';}
			}else{
				$msg = '取消关注失败,你没有关注该用户';
			}
		}else{
			$check = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE user_id='$followed' AND follow_user_id='$follower'");
			$status = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE user_id='$follower' AND follow_user_id='$followed' AND follow_status IN(1,2)");
			$status1 = $status?2:1;
			$status2 = $status?2:0;
			$type = $status1;
			$time = current_time('mysql');
			if($check){
				if($wpdb->query("UPDATE $table_name SET follow_status = '$status1',follow_time = '$time' WHERE user_id='$followed' AND follow_user_id='$follower'")){
					$success = 1;
					$msg = '关注成功';
					$wpdb->query("UPDATE $table_name SET follow_status = '$status2' WHERE user_id='$follower' AND follow_user_id='$followed'");
				}else{$msg = '关注失败,请重试';}
			}else{
				if($wpdb->query( "INSERT INTO $table_name (user_id,follow_user_id,follow_status,follow_time) VALUES ('$followed', '$follower', '$status1', '$time')" )){
					$success = 1;
					$msg = '关注成功';
					$wpdb->query("UPDATE $table_name SET follow_status = '$status2' WHERE user_id='$follower' AND follow_user_id='$followed'");
				}else{$msg = '关注失败,请重试';}
			}
		}

	}
	$return = json_encode(array('success'=>$success,'msg'=>$msg,'type'=>$type));
	echo $return;
	exit;
}
add_action( 'wp_ajax_follow', 'um_follow_unfollow' );
add_action( 'wp_ajax_nopriv_follow', 'um_follow_unfollow' );

/* Follow button */
function um_follow_button($uid){
	$uid = (int)$uid;
	$cuid = get_current_user_id();
	if($uid==0)return;
	global $wpdb;
	$table_name = $wpdb->prefix . 'um_follow';
	$check = $wpdb->get_row("SELECT * FROM $table_name WHERE user_id='$uid' AND follow_user_id='$cuid' AND follow_status IN(1,2)");
	if($check){
		if($check->follow_status==2){$button = '<span data-uid="'.$uid.'" data-act="disfollow" class="follow-btn followed"><i class="fa fa-exchange"></i>互相关注</span>';}else{$button = '<span data-uid="'.$uid.'" data-act="disfollow" class="follow-btn followed"><i class="fa fa-check"></i>已关注</span>';}
	}else{$button = '<span data-uid="'.$uid.'" data-act="follow" class="follow-btn unfollowed"><i class="fa fa-plus"></i>关 注</span>';}
	return $button;
}

?>