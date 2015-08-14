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
// 拒绝搜索引擎索引开放平台登录地址
function um_connect_robots_mod( $output, $public ){
	$output .= "Disallow: /?connect=*";
	return $output;
}
add_filter( 'robots_txt', 'um_connect_robots_mod', 10, 2 );

//获取重定向链接
function um_get_redirect_uri(){
	if( isset($_GET['redirect_uri']) ) return urldecode($_GET['redirect_uri']);
	if( isset($_GET['redirect_to']) ) return urldecode($_GET['redirect_to']);
	if( isset($_GET['redirect']) ) return urldecode($_GET['redirect']);
	if( isset($_SERVER['HTTP_REFERER']) ) return urldecode($_SERVER['HTTP_REFERER']);
	return home_url();
}

//重定向Cookie名
function um_redirect_cookie_name(){
	$home = home_url();
	$home = str_replace('.', '_', $home);
	$cookie_name = 'um_logged_in_redirect_'.$home;
	return $cookie_name;
}

//判断QQ登录
function um_is_open_qq($user_id=0){
	$open_qq = um_get_setting('um_open_qq');
	if(!$open_qq) return false;
	$id = (int)$user_id;
	$O = array(
		'ID'=>um_get_setting('um_open_qq_id'),
		'KEY'=>um_get_setting('um_open_qq_key')
	);		
	if( !$O['ID'] || !$O['KEY'] ) return false;
	if($id){		
		$U = array(
			'ID'=>get_user_meta( $id, 'um_qq_openid', true ),
			'TOKEN'=>get_user_meta( $id, 'um_qq_access_token', true )
		);		
		if( !$U['ID'] || !$U['TOKEN'] ) return false;
	}
	return true;	
}

//判断微博登录
function um_is_open_weibo($user_id=0){
	$open_weibo = um_get_setting('um_open_weibo');
	if(!$open_weibo) return false;
	$id = (int)$user_id;
	$O = array(
		'KEY'=>um_get_setting('um_open_weibo_key'),
		'SECRET'=>um_get_setting('um_open_weibo_secret')
	);	
	if( !$O['KEY'] || !$O['SECRET'] ) return false;
	if($id){		
		$U = array(
			'ID'=>get_user_meta( $id, 'um_weibo_openid', true ),
			'TOKEN'=>get_user_meta( $id, 'um_weibo_access_token', true )
		);		
		if( !$U['ID'] || !$U['TOKEN'] ) return false;		
	}	
	return true;
}

//后台个人资料QQ及微博信息
function um_open_user_contactmethods($user_contactmethods ){
	$user_contactmethods ['um_qq_openid'] = 'QQ OPEN ID';
	$user_contactmethods ['um_qq_access_token'] = 'QQ TOKEN';
	$user_contactmethods ['um_weibo_openid'] = 'WEIBO OPEN ID';
	$user_contactmethods ['um_weibo_access_token'] = 'WEIBO TOKEN';
	return $user_contactmethods ;
}
//~ add_filter('user_contactmethods','um_open_user_contactmethods');


//后台个人资料页添加QQ及微博绑定按钮
function um_open_profile_fields( $user ) {
	$qq = um_is_open_qq();
	$weibo = um_is_open_weibo();	
	if( $qq || $weibo ) {
    ?>
<table class="form-table">
	<?php if($qq){ ?>
	<tr>
		<th scope="row">QQ登录</th>
		<td>
	<?php  if(um_is_open_qq($user->ID)) { ?>
		<p><?php _e('已绑定','um');?> <a href="<?php echo home_url('/?connect=qq&action=logout'); ?>"><?php _e('点击解绑','um');?></a></p>
		<?php echo um_get_avatar( $user->ID , '100' , 'qq' ); ?>
	<?php }else{ ?>
		<a class="button button-primary" href="<?php echo home_url('/?connect=qq&action=login'); ?>">绑定QQ账号</a>
	<?php } ?>
		</td>
	</tr>
	<?php } ?>
	<?php if($weibo){ ?>
	<tr>
		<th scope="row">微博登录</th>
		<td>
	<?php if(um_is_open_weibo($user->ID)) { ?>
		<p><?php _e('已绑定','um');?> <a href="<?php echo home_url('/?connect=weibo&action=logout'); ?>"><?php _e('点击解绑','um');?></a></p>
		<?php echo um_get_avatar( $user->ID , '100' , 'weibo' ); ?>
	<?php }else{ ?>
		<a class="button button-primary" href="<?php echo home_url('/?connect=weibo&action=login'); ?>">绑定微博账号</a>
	<?php } ?>
		</td>
	</tr>
	<?php } ?>
</table>
    <?php
	}
}
add_action( 'profile_personal_options', 'um_open_profile_fields' );

function um_open_template_redirect(){	
	$redirect = um_get_redirect_uri();	
	$die_title = '请重试或报告管理员';		
	$redirect_text = '<p>'.$die_title.' </p><p><a href="'.$redirect.'">点击返回</a></p>';
	$user_ID = get_current_user_id();
	
	function um_open_login($openid='',$token='',$type='qq',$name=''){
		$cookie_name = um_redirect_cookie_name();
		$redirect = isset($_COOKIE[$cookie_name]) ? urldecode($_COOKIE[$cookie_name]) : home_url();	
		$die_title = '请重试或报告管理员';		
		$redirect_text = '<p>'.$die_title.' </p><p><a href="'.$redirect.'">点击返回</a></p>';
		$user_ID = get_current_user_id();
			$id_field = 'um_'.$type.'_openid';
			$token_field = 'um_'.$type.'_access_token';
			global $wpdb;
			$user_exist = $wpdb->get_var( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key='$id_field' AND meta_value='$openid' " );		
			if(is_user_logged_in()){		
				if( isset($user_exist) && (int)$user_exist>0 ){
					wp_die($name.' 已有绑定账号，请绑定其他账号或先解除原有账号。 '.$redirect_text  , $die_title );	
				}else{
				update_user_meta($user_ID, $id_field, $openid);
				update_user_meta($user_ID,$token_field, $token);
				header('Location:'.$redirect);
				exit;	
				}	
			}else{		
				if( isset($user_exist) && (int)$user_exist>0 ){	
					$insert_user_id = $user_exist;
					$is_new_user = 0;
				}else{
					$user_login = strtoupper(substr( $type, 0, 1 )).$openid;
					$insert_user_id = wp_insert_user( array(
						'user_login'  => $user_login,
						'nickname'  => $name,
						'display_name'  => $name,
						'user_pass' => wp_generate_password()
					) ) ;
					$is_new_user = 1;
				}
				if( is_wp_error($insert_user_id) ) {
					wp_die('登录失败！ '.$redirect_text  , $die_title );
				}else{
					update_user_meta($insert_user_id, $id_field, $openid);
					update_user_meta($insert_user_id, $token_field, $token);
					if( $is_new_user ){
						update_user_meta($insert_user_id, 'um_avatar', $type);
						wp_update_user( array ('ID' => $insert_user_id, 'role' => um_get_setting('um_open_role') ) );
						add_um_message( $insert_user_id, 'unread', current_time('mysql'), __('请完善账号信息','um'), sprintf(__('欢迎来到%1$s，请<a href="%2$s">完善资料</a>，其中电子邮件尤为重要，许多信息都将通过电子邮件通知您！','um') , get_bloginfo('name'), admin_url('profile.php')) );
					}
					update_user_meta( $insert_user_id, 'um_latest_login', current_time( 'mysql' ) );
					wp_set_current_user( $insert_user_id, $user_login );
					wp_set_auth_cookie( $insert_user_id );
					do_action( 'wp_login', $user_login );
					header('Location:'.$redirect);
					exit;		
				}		
			}
	}
	
	function um_open_logout($type='qq'){
		$redirect = get_edit_profile_url();
		if($type==='qq'){
			$type = 'qq';
			$name = ' <img src="'.UM_URI.'static/img/qq_32x32.png" > QQ ';	
		}else{
			$type = 'weibo';
			$name = ' <img src="'.UM_URI.'static/img/weibo_32x32.png" > 微博 ';	
		}
				if( isset($_GET['wpnonce']) && wp_verify_nonce( trim($_GET['wpnonce']), $type.'_logout' ) ){
					$user_ID = get_current_user_id();
					if($type==='weibo'){
						$token = get_user_meta($user_ID , 'um_weibo_access_token', true );
						$info = wp_remote_retrieve_body(wp_remote_get('https://api.weibo.com/oauth2/revokeoauth2?access_token='.$token));	
					}
					delete_user_meta($user_ID, 'um_'.$type.'_openid');
					delete_user_meta($user_ID, 'um_'.$type.'_access_token');
					header('Location:'.$redirect);
					exit;
				}else{
					wp_die(
						sprintf( __('你正在试图解除%1$s绑定，确定这样做吗？<a href="%2$s">点击继续</a> <p>不知道这是哪里？<a href="%3$s">点击返回</a></p>','um'), $name, add_query_arg( 'wpnonce', wp_create_nonce( $type.'_logout' ), um_get_current_page_url() ), $redirect ),
						__('解除账号绑定','um')
					);	
				}	
	}

	function um_set_redirect_cookie(){
		setcookie(um_redirect_cookie_name(), urlencode(um_get_redirect_uri()), time()+3600);
	}
	
	function um_get_redirect_text(){
		$cookie_name = um_redirect_cookie_name();
		$redirect = isset($_COOKIE[$cookie_name]) ? urldecode($_COOKIE[$cookie_name]) : um_get_redirect_uri();
		return '<a href="'.$redirect.'">点击返回</a>';
	}
	
	function um_connect_check($str=''){
		if(empty($str)){
			wp_die('服务器无法连接开放平台，请重试或联系管理员！'.um_get_redirect_text(), '无法连接开发平台');
		}
		return $str;
	}

	if( isset($_GET['connect']) && trim($_GET['connect'])==='qq' && um_is_open_qq() && ( is_home() ||is_front_page() ) ){
		um_set_redirect_cookie();
		$OPEN_QQ = array(
			'APPID'=>um_get_setting('um_open_qq_id'),
			'APPKEY'=>um_get_setting('um_open_qq_key'),
			'CALLBACK'=>home_url('/?connect=qq')
		);
		if( isset($_GET['action']) ){
			if( trim($_GET['action'])==='login' ){
					if( is_user_logged_in() && get_user_meta( $user_ID, 'um_qq_openid', TRUE ) ){
						wp_die('你已经绑定了QQ号，一个账号只能绑定一个QQ号，如要更换，请先解绑现有QQ账号，再绑定新的。<p><a href="'.$redirect.'">点击返回</a></p>','不能绑定多个QQ');
					}
				$state = md5(uniqid(rand(), true));
				$params = array(
					'response_type'=>'code',
					'client_id'=>$OPEN_QQ['APPID'],
					'state'=>$state,
					'scope'=>'get_user_info,get_info,add_t,del_t,add_pic_t,get_repost_list,get_other_info,get_fanslist,get_idollist,add_idol,del_idol',
					'redirect_uri'=>$OPEN_QQ['CALLBACK']
				);
				setcookie('qq_state', md5($state), time()+600);
				header('Location:https://graph.qq.com/oauth2.0/authorize?'.http_build_query($params));
				exit();
			}
			if(trim($_GET['action'])==='logout' && is_user_logged_in()) um_open_logout('qq');
		}

		if( isset($_GET['code']) && isset($_GET['state']) && isset($_COOKIE['qq_state']) && $_COOKIE['qq_state']==md5($_GET['state']) ){
			$params = array(
				'grant_type'=>'authorization_code',
				'code'=>$_GET['code'],
				'client_id'=>$OPEN_QQ['APPID'],
				'client_secret'=>$OPEN_QQ['APPKEY'],
				'redirect_uri'=>$OPEN_QQ['CALLBACK']
			);

			$response = um_connect_check(wp_remote_retrieve_body(wp_remote_get('https://graph.qq.com/oauth2.0/token?'.http_build_query($params))));

			 if (strpos($response, "callback") !== false){
				$lpos = strpos($response, "(");
				$rpos = strrpos($response, ")");
				$response  = substr($response, $lpos + 1, $rpos - $lpos -1);
				$msg = json_decode($response);
				if (isset($msg->error)){
					wp_die( "<b>error</b> " . $msg->error . " <b>msg</b> " . $msg->error_description.$redirect_text , $die_title );
				}
			 }
			 
			$params = array();
			parse_str($response, $params);
			$token = $params['access_token'];
			
			$graph_url = "https://graph.qq.com/oauth2.0/me?access_token=".$token;
			
			$str = um_connect_check(wp_remote_retrieve_body(wp_remote_get($graph_url)));
	 
			if (strpos($str, "callback") !== false){
				$lpos = strpos($str, "(");
				$rpos = strrpos($str, ")");
				$str  = substr($str, $lpos + 1, $rpos - $lpos -1);
			}
			$user = json_decode($str);
			if (isset($user->error)){
				wp_die( "<b>error</b> " . $user->error . " <b>msg</b> " . $user->error_description.$redirect_text , $die_title );
			}

			$qq_openid = $user->openid;
			
			$info_url = "https://graph.qq.com/user/get_user_info?access_token=".$token."&oauth_consumer_key=".$OPEN_QQ['APPID']."&openid=".$qq_openid;
			
			$info = json_decode(um_connect_check(wp_remote_retrieve_body(wp_remote_get($info_url))));
			
			if ($info->ret){
				wp_die( "<b>error</b> " . $info->ret . " <b>msg</b> " . $info->msg.$redirect_text , $die_title );
			}

			um_open_login($qq_openid,$token,'qq',$info->nickname);

			exit;
		}
	
	}

	if( isset($_GET['connect']) && trim($_GET['connect'])==='weibo' && um_is_open_weibo() && ( is_home() ||is_front_page() ) ){
		
		um_set_redirect_cookie();
		$OPEN_WEIBO = array(
			'KEY'=>um_get_setting('um_open_weibo_key'),
			'SECRET'=>um_get_setting('um_open_weibo_secret'),
			'CALLBACK'=>home_url('/?connect=weibo')
		);
		
		if( isset($_GET['action']) ){
			
			if(trim($_GET['action'])==='login'){
				if( is_user_logged_in() && get_user_meta( $user_ID, 'um_weibo_openid', TRUE ) ){
					wp_die('你已经绑定了微博账号，一个账号只能绑定一个微博，如要更换，请先解绑现有微博账号，再绑定新的。<p><a href="'.$redirect.'">点击返回</a></p>','不能绑定多个微博');
				}
				$params = array(
					'response_type'=>'code',
					'client_id'=>$OPEN_WEIBO['KEY'],
					'redirect_uri'=>$OPEN_WEIBO['CALLBACK']
				);
				header('Location:https://api.weibo.com/oauth2/authorize?'.http_build_query($params));
				exit();
			}
			
			if(trim($_GET['action'])==='logout' && is_user_logged_in()) um_open_logout('weibo');

		}

		if( isset($_GET['code']) ){

			$access = um_connect_check(wp_remote_retrieve_body(wp_remote_post('https://api.weibo.com/oauth2/access_token?',array( 'body' => array(
				'grant_type'=>'authorization_code',
				'client_id'=>$OPEN_WEIBO['KEY'],
				'client_secret'=>$OPEN_WEIBO['SECRET'],
				'code'=>trim($_GET['code']),
				'redirect_uri'=>$OPEN_WEIBO['CALLBACK']
			)))));
			
			$access = json_decode($access,true);
			
			if (isset($access["error"])){
				wp_die( "<b>error</b> " . $access["error"] . " <b>msg</b> " . $access["error_description"].$redirect_text , $die_title );
			}
	
			$openid = $access["uid"];
			$token = $access["access_token"];

			$info = um_connect_check(wp_remote_retrieve_body(wp_remote_get('https://api.weibo.com/2/users/show.json?access_token='.$token.'&uid='.$openid)));

			$info = json_decode($info,true);
			
			if (isset($info["error"])){
				wp_die( "<b>error</b> " . $info["error_code"] . " <b>msg</b> " . $info["error"].$redirect_text , $die_title );
			}
			
			um_open_login($openid,$token,'weibo',$info["name"]);
			
			exit();
		}

	}
}
add_action('template_redirect', 'um_open_template_redirect' );

?>