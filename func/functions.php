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
/* Add JS and CSS */
function um_add_scripts() {
		$jq = UM_URI.'static/jquery.min.js';
		wp_deregister_script( 'jquery' ); 
		wp_register_script( 'jquery', $jq ); 
		wp_enqueue_script( 'jquery' );
		$um_js = UM_URI.'static/um.js';
		wp_enqueue_script( 'um', $um_js, 'jquery', '', true );
		if(is_author()){
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			wp_enqueue_style('thickbox');
		}
		$um_css = UM_URI.'static/um.css';
		wp_enqueue_style( 'um', $um_css );
		if(um_get_setting('font_awesome')){
			$font_awesome = UM_URI.'static/font-awesome/font-awesome.css';
			wp_enqueue_style( 'fa', $font_awesome );
		}
		?>
		<script type="text/javascript">
			var um = <?php echo um_script_parameter(); ?>;
		</script>
	<?php
	if ( get_post_type()=='store' ){
		?>
		<script type="text/javascript">var bds_config = {'snsKey':{'tsina':"<?php echo um_get_setting('um_open_weibo_key','2884429244'); ?>"}};</script>
		<script type="text/javascript" id="bdshell_js" src = "http://bdimg.share.baidu.com/static/api/js/share.js"></script>
		<?php
	}
}
add_action('wp_enqueue_scripts', 'um_add_scripts');

/* Remove open sans */
function um_remove_open_sans() {    
    wp_deregister_style( 'open-sans' );    
    wp_register_style( 'open-sans', false );    
    wp_enqueue_style('open-sans','');    
}    
add_action( 'init', 'um_remove_open_sans' );

/* Add avatar upload folder */
function um_add_avatar_folder() {
    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . '/avatars';
    if (! is_dir($upload_dir)) {
       mkdir( $upload_dir, 0755 );
    }
}
add_action('init','um_add_avatar_folder');

/* Rename uploaded image name include Chinese */
function um_custom_upload_name($file){
	if(preg_match('/[一-龥]/u',$file['name'])):
	$ext=ltrim(strrchr($file['name'],'.'),'.');
	$file['name']=preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME'])).'_'.date('Y-m-d_H-i-s').'.'.$ext;
	endif;
	return $file;
}
add_filter('wp_handle_upload_prefilter','um_custom_upload_name',5,1);

/* Register shop menu */
function um_register_menu(){
	register_nav_menus( array(
		'shopcatbar' => '商城分类导航'
	) );
}
add_action('init','um_register_menu');

/* Get current page url */
function um_get_current_page_url(){
	global $wp;
	return get_option( 'permalink_structure' ) == '' ? add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) : home_url( add_query_arg( array(), $wp->request ) );
}

function um_get_current_page_url2(){
    $protocol = strtolower($_SERVER['REQUEST_SCHEME']);
    $ssl = $protocol=='https'?true:false;
    $port  = $_SERVER['SERVER_PORT'];
    $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
    $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
    return $protocol . '://' . $host . $port . $_SERVER['REQUEST_URI'];
}

/* JS parameters */
function um_script_parameter(){
	$object = array();
	$object['ajax_url'] = admin_url('admin-ajax.php');
	$object['admin_url'] = admin_url();
	$object['wp_url'] = get_bloginfo('url');
	$object['um_url'] = UM_URI;
	$object['uid'] = (int)get_current_user_id();
	$object['is_admin'] = current_user_can('edit_users')?1:0;
	$object['redirecturl'] = um_get_current_page_url();
	$object['loadingmessage'] = '正在请求中，请稍等...';
	$object['paged']	= get_query_var('paged')?(int)get_query_var('paged'):1;
	$object['cpage']	= get_query_var('cpage')?(int)get_query_var('cpage'):1;
	if(is_single()){
		global $post;
		$object['pid'] = $post->ID;
	}
	$object['timthumb'] = UM_URI.'func/timthumb.php?src=';
	$object_json = json_encode($object);
	return $object_json;
}

/* AJAX login */
function um_ajax_login(){
	$result	= array('loggedin'=>0,'message'=>'');
	if(isset($_POST['security']) /*&& wp_verify_nonce( $_POST['security'], 'security_nonce' )*/ ){
		$creds = array();
		$creds['user_login'] = $_POST['username'];
		$creds['user_password'] = $_POST['password'];
		$creds['remember'] = ( isset( $_POST['remember'] ) ) ? $_POST['remember'] : false;
		$login = wp_signon($creds, false);
		if ( ! is_wp_error( $login ) ){
			$result['loggedin']	= 1;
			$result['message'] = '登录成功！即将为你刷新';
		}else{
			$result['message']	= ( $login->errors ) ? strip_tags( $login->get_error_message() ) : '<strong>ERROR</strong>: ' . esc_html__( '请输入正确用户名和密码以登录', 'um' );
		}
	}else{
		$result['message'] = __('安全认证失败，请重试！','um');
	}
	header( 'content-type: application/json; charset=utf-8' );
	echo json_encode( $result );
	exit;
	
}
add_action( 'wp_ajax_ajax_login', 'um_ajax_login' );
add_action( 'wp_ajax_nopriv_ajax_login', 'um_ajax_login' );

/* AJAX register */
function um_ajax_register(){
	$result	= array();
	if(isset($_POST['security']) /*&& wp_verify_nonce( $_POST['security'], 'user_security_nonce' )*/ ){
		$user_login = sanitize_user($_POST['username']);
		$user_pass = $_POST['password'];
		$user_email	= apply_filters( 'user_registration_email', $_POST['email'] );
		$captcha = strtolower(trim($_POST['um_captcha']));
		session_start();
		$session_captcha = strtolower($_SESSION['um_captcha']);
		$errors	= new WP_Error();
		if( ! validate_username( $user_login ) ){
			$errors->add( 'invalid_username', __( '请输入一个有效用户名','um' ) );
		}elseif(username_exists( $user_login )){
			$errors->add( 'username_exists', __( '此用户名已被注册','um' ) );
		}elseif(email_exists( $user_email )){
			$errors->add( 'email_exists', __( '此邮箱已被注册','um' ) );
		}
		do_action( 'register_post', $user_login, $user_email, $errors );
		$errors = apply_filters( 'registration_errors', $errors, $user_login, $user_email );
		if ( $errors->get_error_code() ){
			$result['success']	= 0;
			$result['message'] 	= $errors->get_error_message();	
		} else {
			$user_id = wp_create_user( $user_login, $user_pass, $user_email );
			if ( ! $user_id ) {
				$errors->add( 'registerfail', sprintf( __( '无法注册，请联系管理员','um' ), get_option( 'admin_email' ) ) );
				$result['success']	= 0;
				$result['message'] 	= $errors->get_error_message();		
			} else{
				update_user_option( $user_id, 'default_password_nag', true, true );
				wp_new_user_notification( $user_id, $user_pass );	
				$result['success']	= 1;
				$result['message']	= __( '注册成功，即将为你自动登录','um' );
				//auto login in
				wp_set_current_user($user_id);
  				wp_set_auth_cookie($user_id);
  				$result['loggedin']	= 1;
			}	
		}	
	}else{
		$result['message'] = __('安全认证失败，请重试！','um');
	}
	header( 'content-type: application/json; charset=utf-8' );
	echo json_encode( $result );
	exit;	
}
add_action( 'wp_ajax_ajax_register', 'um_ajax_register' );
add_action( 'wp_ajax_nopriv_ajax_register', 'um_ajax_register' );

/* Add captcha in login page
/* -------------------------- */
function um_add_register_captcha(){
	$captcha = UM_URI.'/template/captcha.php';
	?>
	<p style="overflow:hidden;">
		<label for="um_captcha">验证码<br>
		<input type="text" name="um_captcha" id="um_captcha" aria-describedby="" class="input" value="" size="20" style="float:left;margin-right:10px;width:200px;">
		<img src="<?php echo $captcha; ?>" class="captcha_img inline" title="点击刷新验证码" onclick="this.src='<?php echo $captcha; ?>';" style="float:right;margin-top: 5px;"></label>
	</p>
	<?php
}
add_action('register_form','um_add_register_captcha');

function um_add_register_captcha_verify($sanitized_user_login,$user_email,$errors){
	if(!isset($_POST['um_captcha'])||empty($_POST['um_captcha'])){
		return $errors->add( 'empty_captcha', __( '请填写验证码','um' ) );
	}else{
		$captcha = strtolower(trim($_POST['um_captcha']));
		session_start();
		$session_captcha = strtolower($_SESSION['um_captcha']);
		if($captcha!=$session_captcha){
			return $errors->add( 'wrong_captcha', __( '验证码错误','um' ) );
		}
	}
}
add_action('register_post','um_add_register_captcha_verify',10,3);

/* Load author template */
function um_load_author_template($template_path){
	if(!um_get_setting('open_ucenter',1))return $template_path;
	if(is_author()){
		$template_path = UM_DIR.'/template/author.php';
	}
	return $template_path;
}
add_filter( 'template_include', 'um_load_author_template', 1 );

/* Catch first image of post */
function um_catch_first_image(){
  global $post, $posts;
  $first_img = '';
  ob_start();
  ob_end_clean();
  $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
  $first_img = isset($matches [1] [0]) ? $matches [1] [0] : '';
  if(empty($first_img)){	
		$random = mt_rand(1, 20);
		$first_img = UM_URI;
		$first_img .= '/static/img/rand/'.$random.'.jpg';
  }
  return $first_img;
}

/* Timthumb */
function um_timthumb($src,$width=375,$height=250,$q=100){
	return UM_URI.'/func/timthumb.php?src='.$src.'&q='.$q.'&w='.$width.'&h='.$height.'&zc=1';
}

/* Ucenter tab */
function um_get_user_url( $type='', $user_id=0 ){
	$user_id = intval($user_id);
	if( $user_id==0 ){
		$user_id = get_current_user_id();
	}
	$url = add_query_arg( 'tab', $type, get_author_posts_url($user_id) );
	return $url;
}

/* No robots for author page */
function um_author_tab_no_robots(){
	if( is_author() && isset($_GET['tab']) ) wp_no_robots();
}
add_action('wp_head', 'um_author_tab_no_robots');

/* Profile page fronted */
function um_profile_page( $url ) {
    return is_admin() ? $url : um_get_user_url('profile');
}
add_filter( 'edit_profile_url', 'um_profile_page' );

/* Prohibit none admin user visit admin page */
function um_redirect_wp_admin(){
	$url = um_get_current_page_url();
	if( (is_admin()&&!stripos($url,'media-upload.php')) && is_user_logged_in() && !current_user_can('edit_users') && ( !defined('DOING_AJAX') || !DOING_AJAX )  ){
		wp_redirect( um_get_user_url('profile') );
		exit;
	}
}
add_action( 'init', 'um_redirect_wp_admin' );

/* None admin users edit post fronted */
function um_edit_post_link($url, $post_id){
	if( !current_user_can('edit_users') ){
		$url = add_query_arg(array('action'=>'edit', 'id'=>$post_id), um_get_user_url('post'));
	}
	return $url;
}
add_filter('get_edit_post_link', 'um_edit_post_link', 10, 2);

/* Login page customize logo and dynamic background image */
function um_login_logo_bg(){
	$custom_login_logo = um_get_setting('custom_login_logo');
	$default_login_logo = UM_URI.'static/img/wordpress-logo.png';
	$imgurl = '';
	if(um_get_setting('bing_login_bg')){
		$str=@file_get_contents('http://cn.bing.com/HPImageArchive.aspx?idx=0&n=1');
 		if(preg_match("/<url>(.+?)<\/url>/ies",$str,$matches)){
  			$imgurl='http://cn.bing.com'.$matches[1];
 		}
 		echo '<link rel="stylesheet" href="' . UM_URI . 'static/login.css" type="text/css" media="all" />' . "\n";
 	}
	if( !empty($custom_login_logo) ){
		$css = sprintf('background-image:url(%1$s);-webkit-background-size:85px 85px;background-size:85px 85px;width:85px;height:85px;', $custom_login_logo);
	}else{
		$css = sprintf('background-image:url(%1$s);-webkit-background-size:85px 85px;background-size:85px 85px;width:85px;height:85px;', $default_login_logo);;
	}
	?>
    <style type="text/css">
        body.login div#login h1 a{
			<?php echo $css;?>
		}
		<?php if($imgurl){ ?>
		@media screen and (min-width: 960px){
			body.login{
				background: url( <?php echo $imgurl; ?> );
				background-size: cover;
			}
		}
		<?php } ?>
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'um_login_logo_bg' );

function um_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'um_login_logo_url' );

function um_login_logo_url_title() {
    return get_bloginfo('name');
}
add_filter( 'login_headertitle', 'um_login_logo_url_title' );

/* Display nickname on admin users managing page */
function um_display_name_column( $columns ) {
	$columns['um_display_name'] = '显示名称';
	unset($columns['name']);
	return $columns;
}
add_filter( 'manage_users_columns', 'um_display_name_column' );
 
function um_display_name_column_callback( $value, $column_name, $user_id ) {

	if( 'um_display_name' == $column_name ){
		$user = get_user_by( 'id', $user_id );
		$value = ( $user->display_name ) ? $user->display_name : '';
	}

	return $value;
}
add_action( 'manage_users_custom_column', 'um_display_name_column_callback', 10, 3 );

/* Lastest login info */
function um_update_latest_login( $login ) {
	$user = get_user_by( 'login', $login );
	$latest_login = get_user_meta( $user->ID, 'um_latest_login', true );
	$latest_ip = get_user_meta( $user->ID, 'um_latest_ip', true );
	update_user_meta( $user->ID, 'um_latest_login_before', $latest_login );
	update_user_meta( $user->ID, 'um_latest_ip_before', $latest_ip );
	update_user_meta( $user->ID, 'um_latest_login', current_time( 'mysql' ) );
	update_user_meta( $user->ID, 'um_latest_ip', $_SERVER['REMOTE_ADDR'] );
}
add_action( 'wp_login', 'um_update_latest_login', 10, 1 );
 
function um_latest_login_column( $columns ) {
	$columns['um_latest_login'] = '上次登录';
	return $columns;
}
add_filter( 'manage_users_columns', 'um_latest_login_column' );
 
function um_latest_login_column_callback( $value, $column_name, $user_id ) {
	if('um_latest_login' == $column_name){
		$user = get_user_by( 'id', $user_id );
		$value = ( $user->um_latest_login ) ? $user->um_latest_login : $value = __('没有记录','um');
	}
	return $value;
}
add_action( 'manage_users_custom_column', 'um_latest_login_column_callback', 10, 3 );

/* Get recent login users */
function um_get_recent_user($number=10){
	$user_query = new WP_User_Query( array ( 'orderby' => 'meta_value', 'order' => 'DESC', 'meta_key' => 'um_latest_login', 'number' => $number ) );
	if($user_query) return $user_query->results;
	return;
}

/* Page nav */
function um_pagenavi( $before = '', $after = '', $p = 2 ) {
    if ( is_singular() ) return;
    global $wp_query, $paged;
    $max_page = $wp_query->max_num_pages;
    if ( $max_page == 1 )
        return;
    if ( empty( $paged ) )
        $paged = 1;
    echo '<div class="pages"><ul class="page-list">';
    if($paged>3)
    	um_p_link( 1, '首页','<li>','首页' );
    if ( $paged > 1)
        um_p_link( $paged - 1, '上一页', '<li class="prev-page">' ,'<i class="fa fa-angle-left"></i>' );

    for( $i = $paged - $p; $i <= $paged + $p; $i++ ) {
        if ( $i > 0 && $i <= $max_page )
            $i == $paged ? print '<li class="current"><a href="">'.$i.'</a></li>' : um_p_link( $i,'', '<li>',$i);
    }
    if ( $paged < $max_page ) {um_p_link( $paged + 1,'下一页', '<li class="next-page">' ,'<i class="fa fa-angle-right"></i>');um_p_link( $max_page, '尾页','<li>','尾页' );}
    echo '</ul></div>';
}
function um_p_link( $i, $title = '', $linktext = '' , $prevnext='') {
    if ( $title == '' ) $title = "第 {$i} 页";
    echo "{$linktext}<a href='", esc_html( get_pagenum_link( $i ) ), "' title='{$title}'>{$prevnext}</a></li>";
}

/* AJAX update nonce */
function um_create_nonce_callback(){

	echo wp_create_nonce( 'check-nonce' );

   die();
}
add_action( 'wp_ajax_um_create_nonce', 'um_create_nonce_callback' );
add_action( 'wp_ajax_nopriv_um_create_nonce', 'um_create_nonce_callback' );

/* Update product traffic */
function um_tracker_ajax_callback(){
	if ( ! wp_verify_nonce( trim($_POST['wp_nonce']), 'check-nonce' ) ){
		echo 'NonceIsInvalid';
		die();
	}
	if( $_POST['pid']=='' ) return;
	$pid = sanitize_text_field($_POST['pid']);
	if(!empty($pid)){
		$views = get_post_meta($pid,'um_post_views',true)?(int)get_post_meta($pid,'um_post_views',true):0;
		$views++;
		update_post_meta($pid,'um_post_views',$views);
	}
	echo $views;
	//do_action( 'um_tracker_ajax_callback', $pid ); 
	die();
}
add_action( 'wp_ajax_um_tracker_ajax', 'um_tracker_ajax_callback' );
add_action( 'wp_ajax_nopriv_um_tracker_ajax', 'um_tracker_ajax_callback' );

/* Author page paginate */
function um_paginate($wp_query=''){
	if(empty($wp_query)) global $wp_query;
	$pages = $wp_query->max_num_pages;
	if ( $pages >= 2 ):
		$big = 999999999;
		$paginate = paginate_links( array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'current' => max( 1, get_query_var('paged') ),
			'total' => $pages,
			'type' => 'array'
		) );
		echo '<div class="pagination">';
		foreach ($paginate as $value) {
			echo '<span class="pg-item">'.$value.'</span>';
		}
		echo '</div>';
	endif;
}
function um_pager($current, $max){
	$paged = intval($current);
	$pages = intval($max);
	if($pages<2) return '';
	$pager = '<div class="pagination clx">';
		$pager .= '<div class="btn-group">';
			if($paged>1) $pager .= '<a class="btn btn-default" style="float:left;padding:6px 12px;" href="' . add_query_arg('page',$paged-1) . '">'.__('上一页','um').'</a>';
			if($paged<$pages) $pager .= '<a class="btn btn-default" style="float:left;padding:6px 12px;" href="' . add_query_arg('page',$paged+1) . '">'.__('下一页','um').'</a>';
		if ($pages>2 ){
			$pager .= '<div class="btn-group pull-right"><select class="form-control pull-right" onchange="document.location.href=this.options[this.selectedIndex].value;">';
				for( $i=1; $i<=$pages; $i++ ){
					$class = $paged==$i ? 'selected="selected"' : '';
					$pager .= sprintf('<option %s value="%s">%s</option>', $class, add_query_arg('page',$i), sprintf(__('第 %s 页','um'), $i));
				}
			$pager .= '</select></div>';
		}
	$pager .= '</div></div>';
	return $pager;
}

/* Action : like article */
function um_like_article(){
	$pid = $_POST['pid'];
	$likes = get_post_meta($pid,'um_post_likes',true);
	$likes++;
	update_post_meta($pid,'um_post_likes',$likes);
	$uid = get_current_user_id();
	$return = 1;
	if($uid){
		$meta = get_user_meta($uid,'um_article_interaction',true);
		$meta = json_decode($meta);
		$now_date = date('Y-m-j');
		$credit = um_get_setting('like_article_credit',5);
		$times = um_get_setting('like_article_credit_times',5);
		$get = 0;
		if(!isset($meta->dated)||$now_date!=$meta->dated){
			update_um_credit( $uid , $credit , 'add' , 'um_credit' , sprintf( __('参与文章互动，获得%s积分','um') , $credit ) );
			$new_times = 1;
			$new_meta = json_encode(array('dated'=>$now_date,'times'=>$new_times));
			update_user_meta($uid,'um_article_interaction',$new_meta);
			$get = 1;
		}else if($meta->times<$times){
			update_um_credit( $uid , $credit , 'add' , 'um_credit' , sprintf( __('参与文章互动，获得%s积分','um') , $credit ) );
			$new_times = $meta->times;
			$new_times++;
			$new_meta = json_encode(array('dated'=>$now_date,'times'=>$new_times));
			update_user_meta($uid,'um_article_interaction',$new_meta);
			$get = 1;
		}else{}
		$return = json_encode(array('get'=>$get,'credit'=>$credit));
	}
	echo $return;
	exit;
}
add_action( 'wp_ajax_nopriv_like', 'um_like_article' );
add_action( 'wp_ajax_like', 'um_like_article' );

/* Action : collect article */
function um_collect(){
	$pid = $_POST['pid'];
	$uid = $_POST['uid'];
	$action = $_POST['act'];
	if($action!='remove'){
		$collect = get_user_meta($uid,'um_collect',true);
		$plus=1;
		if(!empty($collect)){
			$collect_arr = explode(',', $collect);
			if(in_array($pid, $collect_arr)){$plus=0;return;}
			$collect .= ','.$pid;
			update_user_meta($uid,'um_collect',$collect);
		}else{
			$collect = $pid;
			update_user_meta($uid,'um_collect',$collect);		
		}
		$collects = get_post_meta($pid,'um_post_collects',true);
		$collects += $plus;
		$plus!=0?update_post_meta($pid,'um_post_collects',$collects):'';
	}else{
		$plus = -1;
		$collect = get_user_meta($uid,'um_collect',true);
		$collect_arr = explode(',', $collect);
		if(!in_array($pid, $collect_arr)){$plus=0;return;}
		$collect = um_delete_string_specific_value(',',$collect,$pid);
		update_user_meta($uid,'um_collect',$collect);
		$collects = get_post_meta($pid,'um_post_collects',true);
		$collects--;
		update_post_meta($pid,'um_post_collects',$collects);
	}
	echo $plus;
	exit;
}
//add_action( 'wp_ajax_nopriv_collect', 'um_collect' );
add_action( 'wp_ajax_collect', 'um_collect' );

/* Delete specified record of string */
function um_delete_string_specific_value($separator,$string,$value){
	$arr = explode($separator,$string);
	$key =array_search($value,$arr);
	array_splice($arr,$key,1);
	$str_new = implode($separator,$arr);
	return $str_new;
}

/* Get avatar */
function um_get_avatar( $id , $size='40' , $type=''){
	if($type==='qq'){
		$O = array(
			'ID'=>um_get_setting('um_open_qq_id'),
			'KEY'=>um_get_setting('um_open_qq_key')
		);
		$U = array(
			'ID'=>get_user_meta( $id, 'um_qq_openid', true ),
			'TOKEN'=>get_user_meta( $id, 'um_qq_access_token', true )
		);	
		if( $O['ID'] && $O['KEY'] && $U['ID'] && $U['TOKEN'] ){
			$avatar_url = 'http://q.qlogo.cn/qqapp/'.$O['ID'].'/'.$U['ID'].'/100';
		}	
	}else if($type==='weibo'){
		$O = array(
			'KEY'=>um_get_setting('um_open_weibo_key'),
			'SECRET'=>um_get_setting('um_open_weibo_secret')
		);
		$U = array(
			'ID'=>get_user_meta( $id, 'um_weibo_openid', true ),
			'TOKEN'=>get_user_meta( $id, 'um_weibo_access_token', true )
		);
		if( $O['KEY'] && $O['SECRET'] && $U['ID'] && $U['TOKEN'] ){
			$avatar_url = 'http://tp3.sinaimg.cn/'.$U['ID'].'/180/1.jpg';
		}
	}else if($type==='customize'){
		$avatar_url = get_bloginfo('url').'/wp-content/uploads/avatars/'.get_user_meta($id,'um_customize_avatar',true);
	}else{
		return get_avatar( $id, $size );
	}
	return '<img src="'.$avatar_url.'" class="avatar" width="'.$size.'" height="'.$size.'" />';
}

/* Avatar type */
function um_get_avatar_type($user_id){
	$id = (int)$user_id;
	if($id===0) return 'default';
	$avatar = get_user_meta($id,'um_avatar',true);
	$customize = get_user_meta($id,'um_customize_avatar',true);
	if( $avatar=='qq' && um_is_open_qq($id) ) return 'qq';
	if( $avatar=='weibo' && um_is_open_weibo($id) ) return 'weibo';
	if( $customize && !empty($customize) ) return 'customize';
	return 'default';
}

/* SSL avatar */
function um_get_ssl_avatar($avatar) {
	$avatar = preg_replace('/.*\/avatar\/(.*)\?s=([\d]+)(&?.*)/','<img src="https://secure.gravatar.com/avatar/$1?s=$2" class="avatar avatar-$2" height="$2" width="$2">',$avatar);	
	return $avatar;
}
add_filter( 'get_avatar', 'um_get_ssl_avatar');

/* Resize uploaded avatar */
function um_resize( $ori ){
    if( preg_match('/^http:\/\/[a-zA-Z0-9]+/', $ori ) ){
        return $ori;
    }
    $info = um_getImageInfo( AVATARS_PATH . $ori );
    if( $info ){
        //上传图片后切割的最大宽度和高度
        $dst_width = 100;
        $dst_height = 100;
        $scrimg = AVATARS_PATH . $ori;
        if( $info['type']=='jpg' || $info['type']=='jpeg' ){
            $im = imagecreatefromjpeg( $scrimg );
        }
        if( $info['type']=='gif' ){
            $im = imagecreatefromgif( $scrimg );
        }
        if( $info['type']=='png' ){
            $im = imagecreatefrompng( $scrimg );
        }
        if( $info['type']=='bmp' ){
            $im = imagecreatefromwbmp( $scrimg );
        }
        if( $info['width']<=$dst_width && $info['height']<=$dst_height ){
            return;
        } else {
            if( $info['width'] > $info['height'] ){
                $height = intval($info['height']);
                $width = $height;
                $x = ($info['width']-$width)/2;
                $y = 0;
            } else {
                $width = intval($info['width']);
                $height = $width;
                $x = 0;
                $y = ($info['height']-$height)/2;
            }

        }
        $newimg = imagecreatetruecolor( $width, $height );
        imagecopy($newimg,$im,0,0,$x,$y,$info['width'],$info['height']);
        $scale = $dst_width/$width;
        $target = imagecreatetruecolor($dst_width, $dst_height);
        $final_w = intval($width*$scale);
        $final_h = intval($height*$scale);
        imagecopyresampled( $target, $newimg, 0, 0, 0, 0, $final_w, $final_h, $width, $height );
        imagejpeg( $target, AVATARS_PATH . $ori );
        imagedestroy( $im );
        imagedestroy( $newimg );
        imagedestroy( $target );
    }
    return;
}

function um_getImageInfo( $img ){
    $imageInfo = getimagesize($img);
    if( $imageInfo!== false) {
        $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]),1));
        $info = array(
                "width"     =>$imageInfo[0],
                "height"    =>$imageInfo[1],
                "type"      =>$imageType,
                "mime"      =>$imageInfo['mime'],
        );
        return $info;
    }else {
        return false;
    }
}

/* Get all categories id */
function get_cat_ids(){
	global $wpdb;
    $request = "SELECT $wpdb->terms.term_id FROM $wpdb->terms ";
    $request .= " LEFT JOIN $wpdb->term_taxonomy ON $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id ";
    $request .= " WHERE $wpdb->term_taxonomy.taxonomy = 'category' ";
    $request .= " ORDER BY term_id asc";
    $categorys = $wpdb->get_results($request,ARRAY_N);
    $ids = array();
    foreach ($categorys as $category){
    	$ids[] .= $category[0];
    }
    return $ids;
}

/* Add paycontent to post */
function um_post_source_price($postid){
	$price = product_smallest_price($postid);
	$currency = get_post_meta($postid,'pay_currency',true);
	$content = '<div id="post-price">';
	if($price[3]==0&&$price[4]==0){
		$content .= '<li class="summary-price"><span class="dt">售价 :</span>';
		if($currency==1) $content .= '<em>¥</em><strong>'.sprintf('%0.2f',$price[0]).'</strong><em>(元)</em>'; else $content .= '<em><i class="fa fa-gift"></i></em><strong>'.sprintf('%0.2f',$price[0]).'</strong><em>(积分)</em>';
		$content .= '</li>';
	}else{ 
		$content .= '<li class="summary-price"><span class="dt">售价 :</span>';
		if($currency==1) $content .= '<em>¥</em><strong><del>'.sprintf('%0.2f',$price[0]).'</del></strong><em>(元)</em>'; else $content .= '<em><i class="fa fa-gift"></i></em><strong><del>'.sprintf('%0.2f',$price[0]).'</del></strong><em>(积分)</em>';
		if($price[4]!=0){
			$content .= '<strong>&nbsp;'.sprintf('%0.2f',$price[2]).'</strong><span>(限时优惠)</span>';
		}
			$content .= '</li>';
		if($price[3]!=0){
			$content .= '<li class="summary-price"><span class="dt">会员价格 :</span>';
			if(getUserMemberType()) { 
				if($currency==1) $content .= '<em>¥</em><strong>'.sprintf('%0.2f',$price[1]).'</strong><em>(元)</em>'; else $content .= '<em><i class="fa fa-gift"></i></em><strong>'.sprintf('%0.2f',$price[1]).'</strong><em>(积分)</em>';
			}else if(is_user_logged_in()){
				$content .= sprintf(__('非 <a href="%1$s" target="_blank" title="开通会员">会员</a> 不能享受该优惠','um'),um_get_user_url('membership'));
			} else {
				if($currency==1) $content .= '<em>¥</em><strong>'.sprintf('%0.2f',$price[6]).'</strong><em>(元)</em>'; else $content .= '<em><i class="fa fa-gift"></i></em><strong>'.sprintf('%0.2f',$price[6]).'</strong><em>(积分)</em>';$content .= '<a href="javascript:" class="user-login">登录</a> 查看实际享受优惠';
			}
			$content .= '</li>';
		}
	}
	$content .= '</div>';
	return $content;
}

function um_post_paycontent($content){
	if(get_post_status(get_the_ID())!='publish'||!get_post_meta(get_the_ID(),'pay_switch',true))return $content;
	$hidden_content = '';
	if(is_single()&&get_post_type()=='post'){
		$price = product_smallest_price(get_the_ID());
		$dl_links = get_post_meta(get_the_ID(),'product_download_links',true);
		$pay_content = get_post_meta(get_the_ID(),'product_pay_content',true);
		if(!count(get_user_order_records(get_the_ID(),0,1))) $hidden_content .= um_post_source_price(get_the_ID());
		if(!empty($dl_links)):
		$hidden_content .= '<div id="pay-content"><li class="summary-content"><span class="dt" style="position:absolute;top:0;left:0;">资源信息 :</span>';
		$arr_links = explode(PHP_EOL,$dl_links);
		foreach($arr_links as $arr_link){
			$arr_link = explode('|',$arr_link);
			$arr_link[0] = isset($arr_link[0]) ? $arr_link[0]:'';
			$arr_link[1] = isset($arr_link[1]) ? $arr_link[1]:'';
			$arr_link[2] = isset($arr_link[2]) ? $arr_link[2]:'';
			$hidden_content .= '<p style="margin:0 0 0 75px;">'.$arr_link[0].'</p><p style="margin:0 0 0 75px;">下载链接：';
			if($price[5]==0||count(get_user_order_records(get_the_ID(),0,1))>0){$hidden_content .= '<a href="'.$arr_link[1].'" title="'.$arr_link[0].'" target="_blank">'.$arr_link[1].'</a>';}else{$hidden_content .= '*** 隐藏内容购买后可见 ***';}
			$hidden_content .= '&nbsp;&nbsp;下载密码：';
			if($price[5]==0||count(get_user_order_records(get_the_ID(),0,1))>0){$hidden_content .= $arr_link[2];}else{$hidden_content .= '*** 隐藏内容购买后可见 ***';}
			$hidden_content .= '</p>';
		}
		$hidden_content .= '</li>';
		endif;
		if($price[5]==0||count(get_user_order_records(get_the_ID(),0,1))>0) $hidden_content .= '<p style="margin-left:75px;">'.$pay_content.'</p>';
		if($price[5]!=0&&count(get_user_order_records(get_the_ID(),0,1))<=0){$amount=(int)get_post_meta(get_the_ID(),'product_amount',true);$btn=$amount>0?'<a class="inner-buy-btn" data-top="false"><i class="fa fa-shopping-cart"></i>立即购买</a>':'<a class="inner-soldout" href="javascript:"><i class="fa fa-shopping-cart">&nbsp;</i>缺货不可购买</a>';$hidden_content .= '<div id="pay"><p>购买该资源后，相关内容将发送至您的邮箱！'.$btn.'</p></div>';}
		$hidden_content .= '</div>';
		$see_content = empty($hidden_content)?$content:$content.'<div class="label-title post"><span id="title"><i class="fa fa-paypal"></i>&nbsp;付费资源</span>'.$hidden_content.'</div>';
	}else{
		$see_content = $content;
	}
	return $see_content;
}
add_filter('the_content','um_post_paycontent',98);


/* Add activity button to post */
function um_post_activity_button($content){
	if(!is_single()) return $content;
	$umlikes=get_post_meta(get_the_ID(),'um_post_likes',true);
	$umcollects=get_post_meta(get_the_ID(),'um_post_collects',true);
	if(empty($umlikes)):$umlikes=0; endif;if(empty($umcollects)):$umcollects=0; endif;
	$c_name = 'um_post_like_'.get_the_ID();$cookie = isset($_COOKIE[$c_name])?$_COOKIE[$c_name]:'';
	$content .= '<div class="activity-btn"><a class="like-btn';
	if($cookie==1)$content .= ' love-yes';
	$content .= '" pid="'.get_the_ID().'" href="javascript:;" title="赞一个"><i class="fa ';
	if($cookie==1)$content .= 'fa-heart'; else $content .= 'fa-heart-o';
	$content .= '">&nbsp;</i>赞一个 (<span>'.$umlikes.'</span>)</a>';
	$uid = get_current_user_id();
	if(!empty($uid)&&$uid!=0){	
		$mycollects = get_user_meta($uid,'um_collect',true);
		$mycollects = explode(',',$mycollects);
		$match = 0;
		foreach ($mycollects as $mycollect){
			if ($mycollect == get_the_ID()):$match++;endif;
		}		
		if ($match==0){
			$content .= '<a class="collect-btn collect-no" pid="'.get_the_ID().'" href="javascript:;" uid="'.get_current_user_id().'" title="点击收藏"><i class="fa fa-star-o">&nbsp;</i>收藏 (<span>'.$umcollects.'</span>)</a>';
		}else{
			$content .= '<a class="collect-btn collect-yes remove-collect" href="javascript:;" pid="'.get_the_ID().'" uid="'.get_current_user_id().'" title="你已收藏，点击取消"><i class="fa fa-star">&nbsp;</i>收藏 (<span>'.$umcollects.'</span>)</a>';
		}
	}else{
		$content .= '<a class="collect-btn collect-no" title="你必须注册并登录才能收藏"><i class="fa fa-star-o">&nbsp;</i>收藏 (<span>'.$umcollects.'</span>)</a>';		
	}
	$content .= '</div>';
	return $content;
}
add_filter('the_content','um_post_activity_button',99);

/* Canonical_url */
function um_canonical_url(){
	switch(TRUE){
		case is_home() :
		case is_front_page() :
			$url = home_url('/');
		break;	
		case is_single() :
			$url = get_permalink();
		break;
		case is_tax() :
		case is_tag() :
		case is_category() :
			$term = get_queried_object(); 
			$url = get_term_link( $term, $term->taxonomy ); 
		break;
		case is_post_type_archive() :
			$url = get_post_type_archive_link( get_post_type() ); 
		break;
		case is_author() : 
			$url = get_author_posts_url( get_query_var('author'), get_query_var('author_name') ); 
		break;
		case is_year() : 
			$url = get_year_link( get_query_var('year') ); 
		break;
		case is_month() : 
			$url = get_month_link( get_query_var('year'), get_query_var('monthnum') ); 
		break;
		case is_day() : 
			$url = get_day_link( get_query_var('year'), get_query_var('monthnum'), get_query_var('day') ); 
		break;
		default :
			$url = um_get_current_page_url();
	}
    if ( get_query_var('paged') > 1 ) { 
		global $wp_rewrite; 
		if ( $wp_rewrite->using_permalinks() ) { 
			$url = user_trailingslashit( trailingslashit( $url ) . trailingslashit( $wp_rewrite->pagination_base ) . get_query_var('paged'), 'archive' ); 
		} else { 
			$url = add_query_arg( 'paged', get_query_var('paged'), $url ); 
		}
	}
	return $url;
}

/* Ucenter widget function */
function um_user_profile_widget(){
	
	if(is_user_logged_in()):
	$current_user = wp_get_current_user();	
	$li_output = '';
	$li_output .= '<li style="line-height:36px;clear: both;">'.um_get_avatar( $current_user->ID , '36' , um_get_avatar_type($current_user->ID), false ) .
		sprintf(__('登录者 <a href="%1$s">%2$s</a>','um'), get_edit_profile_url($current_user->ID), $current_user->display_name) . 
		'<a href="'.wp_logout_url(um_get_current_page_url()).'" title="'.esc_attr__('登出本帐号').'">' .
		__('登出 &raquo;') . 
		'</a></li>';

	if(!filter_var($current_user->user_email, FILTER_VALIDATE_EMAIL)){
		
		$li_output .= '<li><a href="'.um_get_user_url('profile').'#pass">'.__('【重要】请添加正确的邮箱以保证账户安全','um').'</a></li>';
		
	}

	$shorcut_links[] = array(
		'title' => __('个人主页','um'),
		'url' => get_author_posts_url($current_user->ID)
	);
	
	if( current_user_can( 'manage_options' ) ) {
		$shorcut_links[] = array(
			'title' => __('管理后台','um'),
			'url' => admin_url()
		);
	}
	
	$can_post_cat = get_cat_ids();
	if( count($can_post_cat) ) {
		$shorcut_links[] = array(
			'title' => __('文章投稿','um'),
			'url' => add_query_arg('action','new',um_get_user_url('post'))
		);
	}
	
	$shorcut_html = '<li class="active">';
	foreach( $shorcut_links as $shorcut ){
		 $shorcut_html .= '<a href="'.$shorcut['url'].'">'.$shorcut['title'].' &raquo;</a>';
	}
	 $shorcut_html .= '</li>';

	$credit = intval(get_user_meta( $current_user->ID, 'um_credit', true ));
	$credit_void = intval(get_user_meta( $current_user->ID, 'um_credit_void', true ));
	$unread_count = intval(get_um_message($current_user->ID, 'count', "( msg_type='unread' OR msg_type='unrepm' )"));
	$collects = get_user_meta($current_user->ID,'um_collect',true)?get_user_meta($current_user->ID,'um_collect',true):0;
	$collects_array = explode(',',$collects);
	$collects_count = $collects!=0?count($collects_array):0;
	
	$info_array = array(
		array(
			'title' => __('文章','um'),
			'url' => um_get_user_url('post'),
			'count' => count_user_posts($current_user->ID)
		),
		array(
			'title' => __('评论','um'),
			'url' => um_get_user_url('comment'),
			'count' => get_comments( array('status' => '1', 'user_id'=>$current_user->ID, 'count' => true) )
		),
		array(
			'title' => __('收藏','um'),
			'url' => um_get_user_url('collect'),
			'count' => intval($collects_count)
		),
	);
		
	if($unread_count){
		$info_array[] = array(
				'title' => __('未读','um'),
				'url' => um_get_user_url('message'),
				'count' => $unread_count
			);
	}
	
	$info_array[] = array(
			'title' => __('积分','um'),
			'url' => um_get_user_url('credit'),
			'count' => ($credit)
		);
	
	$info_html = '<li>';
	
	foreach( $info_array as $info ){
		$info_html .= $info['title'].'<a href="'.$info['url'].'"> '.$info['count'].'</a>';
	}
	
	$info_html .= um_whether_signed($current_user->ID);
	
	$info_html .= '</li>';
	
	$friend_html = '
	<li>
		<div class="input-group">
			<span class="input-group-addon">'.__('本页推广链接','um').'</span>
			<input class="um_aff_url form-control" type="text" class="form-control" value="'.add_query_arg('aff',$current_user->ID,um_canonical_url()).'">
		</div>
	</li>
	';

	return $li_output.$shorcut_html.$info_html.$friend_html;;
	
	else:
	
	$html = '<li><span class="local-account"><a data-sign="0" class="btn btn-primary user-login"><i class="fa fa-wordpress"></i>'.__('本地帐号','um').'</a></span>';
	if(um_get_setting('um_open_qq')) {
    $html .= '<span class="other-sign"><a class="qqlogin btn" href="'.home_url('/?connect=qq&action=login&redirect='.urlencode(um_get_redirect_uri())).'"><i class="fa fa-qq"></i><span>'.__('QQ 登 录','um').'</span></a></span>';
	}
	if(um_get_setting('um_open_weibo')) {
	$html .= '<span class="other-sign"><a class="weibologin btn" href="'.home_url('/?connect=weibo&action=login&redirect='.urlencode(um_get_redirect_uri())).'"><i class="fa fa-weibo"></i><span>'.__('微博登录','um').'</span></a></span>';
	}
	$html .= '</li>';
	
	return $html;
	endif;
}

/* Ucenter widget function */
function um_user_manage_widget(){
	if(is_user_logged_in()):
	$current_user = wp_get_current_user();	
	$li_output = '';
	$li_output .= '<li style="line-height:36px;clear: both;">'.um_get_avatar( $current_user->ID , '36' , um_get_avatar_type($current_user->ID), false ) .
		sprintf(__('登录者 <a href="%1$s">%2$s</a>','um'), get_edit_profile_url($current_user->ID), $current_user->display_name) . 
		'<a href="'.wp_logout_url(um_get_current_page_url()).'" title="'.esc_attr__('登出本帐号').'">' .
		__('登出 &raquo;') . 
		'</a></li>';
	if(!filter_var($current_user->user_email, FILTER_VALIDATE_EMAIL)){		
		$li_output .= '<li><a href="'.um_get_user_url('profile').'#pass">'.__('【重要】请添加正确的邮箱以保证账户安全','um').'</a></li>';
	}
	$shorcut_links[] = array(
		'icon' => '<i class="fa fa-home"></i>',
		'title' => __('个人主页','um'),
		'url' => get_author_posts_url($current_user->ID)
	);
	$shorcut_links[] = array(
		'icon' => '<i class="fa fa-edit"></i>',
		'title' => __('编辑资料','um'),
		'url' => um_get_user_url('profile')
	);
	if( current_user_can( 'manage_options' ) ) {
		$shorcut_links[] = array(
			'icon' => '<i class="fa fa-dashboard"></i>',
			'title' => __('管理后台','um'),
			'url' => admin_url()
		);
	}
	$can_post_cat = get_cat_ids();
	if( count($can_post_cat) ) {
		$shorcut_links[] = array(
			'icon' => '<i class="fa fa-send"></i>',
			'title' => __('文章投稿','um'),
			'url' => add_query_arg('action','new',um_get_user_url('post'))
		);
	}
	$shorcut_links[] = array(
		'icon' => '<i class="fa fa-shopping-cart"></i>',
		'title' => __('我的订单','um'),
		'url' => um_get_user_url('orders'),
		'prefix' => '<br>'
	);
	$shorcut_links[] = array(
		'icon' => '<i class="fa fa-user-md"></i>',
		'title' => __('会员信息','um'),
		'url' => um_get_user_url('membership'),
		'prefix' => ''
	);
	$shorcut_links[] = array(
		'icon' => '<i class="fa fa-money"></i>',
		'title' => __('我的推广','um'),
		'url' => um_get_user_url('affiliate')
	);
	if( current_user_can( 'manage_options' ) ) {
		$shorcut_links[] = array(
			'icon' => '<i class="fa fa-tasks"></i>',
			'title' => __('订单管理','um'),
			'url' => um_get_user_url('siteorders')
		);
	}
	if( current_user_can( 'manage_options' ) ) {
		$shorcut_links[] = array(
			'icon' => '<i class="fa fa-tags"></i>',
			'title' => __('优惠码','um'),
			'url' => um_get_user_url('coupon')
		);
	}
	$shorcut_html = '<li class="active">';
	foreach( $shorcut_links as $shorcut ){
		$shorcut_html .= isset($shorcut['prefix'])?$shorcut['prefix']:'';
		$shorcut_html .= '<a href="'.$shorcut['url'].'">'.$shorcut['icon'].$shorcut['title'].'</a>';
	}
	 $shorcut_html .= '</li>';

	$credit = intval(get_user_meta( $current_user->ID, 'um_credit', true ));
	$credit_void = intval(get_user_meta( $current_user->ID, 'um_credit_void', true ));
	$unread_count = intval(get_um_message($current_user->ID, 'count', "( msg_type='unread' OR msg_type='unrepm' )"));
	$collects = get_user_meta($current_user->ID,'um_collect',true)?get_user_meta($current_user->ID,'um_collect',true):0;
	$collects_array = explode(',',$collects);
	$collects_count = $collects!=0?count($collects_array):0;
	
	$info_array = array(
		array(
			'title' => __('文章','um'),
			'url' => um_get_user_url('post'),
			'count' => count_user_posts($current_user->ID)
		),
		array(
			'title' => __('评论','um'),
			'url' => um_get_user_url('comment'),
			'count' => get_comments( array('status' => '1', 'user_id'=>$current_user->ID, 'count' => true) )
		),
		array(
			'title' => __('收藏','um'),
			'url' => um_get_user_url('collect'),
			'count' => intval($collects_count)
		),
	);
		
	if($unread_count){
		$info_array[] = array(
				'title' => __('未读','um'),
				'url' => um_get_user_url('message'),
				'count' => $unread_count
			);
	}
	
	$info_array[] = array(
			'title' => __('积分','um'),
			'url' => um_get_user_url('credit'),
			'count' => ($credit)
		);
	
	$info_html = '<li>';
	
	foreach( $info_array as $info ){
		$info_html .= $info['title'].'<a href="'.$info['url'].'"> '.$info['count'].'</a>';
	}
	
	$info_html .= um_whether_signed($current_user->ID);
	
	$info_html .= '</li>';
	
	$friend_html = '
	<li>
		<div class="input-group" style="width:100%;">
			<span class="input-group-addon">'.__('本页推广链接','um').'</span>
			<input class="um_aff_url form-control" type="text" class="form-control" value="'.add_query_arg('aff',$current_user->ID,um_canonical_url()).'">
		</div>
	</li>
	';

	return $li_output.$shorcut_html.$info_html.$friend_html;;
	
	else:
	
	$html = '<li><span class="local-account"><a data-sign="0" class="btn btn-primary user-login"><i class="fa fa-wordpress"></i>'.__('本地帐号','um').'</a></span>';
	if(um_get_setting('um_open_qq')) {
    $html .= '<span class="other-sign"><a class="qqlogin btn" href="'.home_url('/?connect=qq&action=login&redirect='.urlencode(um_get_redirect_uri())).'"><i class="fa fa-qq"></i><span>'.__('QQ 登 录','um').'</span></a></span>';
	}
	if(um_get_setting('um_open_weibo')) {
	$html .= '<span class="other-sign"><a class="weibologin btn" href="'.home_url('/?connect=weibo&action=login&redirect='.urlencode(um_get_redirect_uri())).'"><i class="fa fa-weibo"></i><span>'.__('微博登录','um').'</span></a></span>';
	}
	$html .= '</li>';
	
	return $html;
	endif;
}

/* Add user profile */
function um_add_contact_fields($contactmethods){
	$contactmethods['um_gender'] = '性别';
	$contactmethods['um_qq'] = 'QQ';
	$contactmethods['um_qq_weibo'] = __('腾讯微博','um');
	$contactmethods['um_sina_weibo'] = __('新浪微博','um');
	$contactmethods['um_weixin'] = __('微信二维码','um');
	$contactmethods['um_twitter'] = __('Twitter','um');
	$contactmethods['um_googleplus'] = 'Google+';
	$contactmethods['um_donate'] = __('支付宝收款二维码','um');
	$contactmethods['um_alipay_email'] = __('支付宝帐户','um');
	return $contactmethods;
}
add_filter('user_contactmethods', 'um_add_contact_fields');

/* Use id instead of username in author link */
function um_author_link($link, $author_id){
	global $wp_rewrite;
	$author_id = (int)$author_id;
	$link = $wp_rewrite->get_author_permastruct();
	if(empty($link)){
		$file = home_url('/');
		$link = $file.'?author='.$author_id;
	}else{
		$link = str_replace('%author%', $author_id, $link);
		$link = home_url(user_trailingslashit($link));
	}
	return $link;
}
add_filter('author_link','um_author_link',10,2);

function um_author_link_request($query_vars){
	if(array_key_exists('author_name', $query_vars)){
		global $wpdb;
		$author_id = $query_vars['author_name'];
		if($author_id){
			$query_vars['author'] = $author_id;
			unset($query_vars['author_name']);
		}
	}
	return $query_vars;
}
add_filter('request','um_author_link_request');

/* Alipay post raise money */
function um_alipay_post_gather($alipay_email,$amount=10,$hide=0){
	if(empty($alipay_email))$alipay_email = um_get_setting('alipay_account');
	if($hide==0){$style='display:inline-block;';$button = '<input name="pay" type="image" value="转帐" src="https://img.alipay.com/sys/personalprod/style/mc/btn-index.png" />';}else{$style='display:none;';$button = '<input name="pay" type="hidden" value="转帐"  />';}
	$html = '<form id="alipay-gather" style="'.$style.'" action="https://shenghuo.alipay.com/send/payment/fill.htm" method="POST" target="_blank" accept-charset="GBK"><input name="optEmail" type="hidden" value="'.$alipay_email.'" /><input name="payAmount" type="hidden" value="'.$amount.'" /><input id="title" name="title" type="hidden" value="支持一下" /><input name="memo" type="hidden" value="" />'.$button.'</form>';
	return $html;
}

/* Author page title */
function um_author_page_title(){
	if(isset($_GET['tab'])){
		switch($_GET['tab']){
			case 'comment':
				$title = '评论';
				break;
			case 'collect':
				$title = '文章收藏';
				break;
			case 'credit':
				$title = '个人积分';
				break;
			case 'message':
				$title = '站内消息';
				break;
			case 'profile':
				$title = '个人资料';
				break; 
			case 'orders':
				$title = '个人订单';
				break;
			case 'siteorders':
				$title = '订单管理';
				break;
			case 'membership':
				$title = '会员信息';
				break;
			case 'affiliate':
				$title = '推广信息';
				break;
			case 'coupon':
				$title = '优惠码管理';
				break;
			case 'following':
				$title = '我的关注';
				break;
			case 'follower':
				$title = '我的粉丝';
				break;
			default:
				$title = '文章';
		}
	}else{
		$title = '文章';
	}
	return $title.'-用户中心-'.get_bloginfo('name');
}

/* SEO title */
function um_ob_replace_title(){
	ob_start('um_replace_title');
}
add_action('wp_loaded', 'um_ob_replace_title');

function um_replace_title($html){
	$blogname = get_bloginfo('name');
	$partten = array('/<title>(.*?)<\/title>/i');
	$title = '';
	if(is_author()){
		$title = um_author_page_title();
		$replacement = array('<title>'.$title.'</title>');
		if(um_get_setting('open_ucenter')):
		$html = preg_replace($partten, $replacement, $html);
		else:
		$html = $html;
		endif;
	}elseif(get_post_type() == 'store'){
		if ( is_single() ){
			$title = get_the_title(get_the_ID()).'-'.$blogname;
			$replacement = array('<title>'.$title.'</title>');
		}else{
			$title = um_get_setting('store_archive_title','商城').'-'.$blogname;
			$description = um_get_setting('store_archive_des');
			$keywords = um_get_setting('store_archive_subtitle');
			$keywords = explode('-', $keywords);
			$keywords = implode(',', $keywords);
			$replacement = array('<title>'.$title.'</title>');
			$partten[] = '/<meta name=\"description\" content=\"(.*?)\"(.*?)>/i';
			$replacement[] = '<meta name="description" content="'.$description.'"$2>';
			$partten[] = '/<meta name=\"keywords\" content=\"(.*?)\"(.*?)>/i';
			$replacement[] = '<meta name="keywords" content="'.$keywords.'"$2>';
		}
		$html = preg_replace($partten, $replacement, $html);
	}
	return $html;
}

// AJAX change cover
function um_change_cover(){
	$uid = isset($_POST['user'])?(int)$_POST['user']:0;
	if(!$uid) $uid = (int)get_current_user_id();
	if(!$uid) return;
	$cover = $_POST['cover'];
	update_user_meta($uid,'um_cover',$cover);
	echo json_encode(array('success'=>1));
	exit;
}
add_action( 'wp_ajax_author_cover', 'um_change_cover' );
//add_action( 'wp_ajax_nopriv_author_cover', 'um_change_cover' );

// New version check
function um_get_http_response_code($theURL) {
	@$headers = get_headers($theURL);
	return substr($headers[0], 9, 3);
}

function um_check_version_setup_schedule() {
	if ( ! wp_next_scheduled( 'um_check_version_daily_event' ) ) {
		wp_schedule_event( '1193875200', 'daily', 'um_check_version_daily_event');
	}
}
add_action( 'wp', 'um_check_version_setup_schedule' );

function um_check_version_do_this_daily() {
	if(um_get_http_response_code('http://www.zhiyanblog.com/cdn/tinection/um_version.json')=='200'){
		$check = 0;
		$umVersion = UM_VER;
		$version = json_decode(wp_remote_retrieve_body(wp_remote_get('http://www.zhiyanblog.com/cdn/tinection/um_version.json')),true);
		if ( $version["version"] != $umVersion && !empty($version["version"]) ) {$check = $version["version"];}
		update_option('um_plugin_upgrade',$check);
	}
}
add_action( 'um_check_version_daily_event', 'um_check_version_do_this_daily' );

function um_update_alert_callback(){
	$um_upgrade = get_option('um_plugin_upgrade',0);
	if($um_upgrade&&$um_upgrade!=UM_VER){
		echo '<div class="updated fade"><p>'.sprintf(__('用户中心与商城插件已更新至<a color="red">%1$s</a>(当前%2$s)，请访问<a href="http://www.zhiyanblog.com/wordpress-plugin-ucenter-and-market.html" target="_blank">知言博客Ucenter&Market专页</a>查看！','um'),$um_upgrade,UM_VER).'</p></div>';
	}
}
add_action( 'admin_notices', 'um_update_alert_callback' );

// Authcode
if(!function_exists('authcode')){
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key ? $key : '');
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);
	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);
	$result = '';
	$box = range(0, 255);
	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}
	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
	return $keyc.str_replace('=', '', base64_encode($result));
	}
}
}
if(!function_exists('um_curl_post')){
function um_curl_post($url,$data){
	$post_data = http_build_query($data);
	$post_url= $url;
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_URL, $post_url );
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	$return = curl_exec($ch);
	if (curl_errno($ch)) {      
       return '';      
    }
	curl_close($ch);
	return $return;
}
}

if(!function_exists('um_authorize')){
function um_authorize($code=''){
	date_default_timezone_set ('Asia/Shanghai');
	$para = $code;
	$cache = get_option('_wp_um_authorize');
	if($cache){
		$auth = json_decode($cache);
		$now = time();
		$time = $auth->time;
		if($now-$time<3600*24*2){
			$order = $auth->order_id;
			$sn = $auth->sn;
			$key = $auth->key;
		}else{
			$order=$sn=$key=0;
		}	
	}else{
		$order=$sn=$key=0;
	}
	if(empty($code))$code = '13acJfxEsTyPhY6iOuAjTBhkuD7C/7S0oOOzx4jqjusMPt3/C79zA2Q';
	//if(in_array($_SERVER['HTTP_HOST'],array('127.0.0.1','localhost'))) return;
	if(!authcode('13acJfxEsTyPhY6iOuAjTBhkuD7C/7S0oOOzx4jqjusMPt3/C79zA2Q', 'DECODE', $key)){
		$server = strtolower($_SERVER['HTTP_HOST']);
		$server_arr = explode('.', $server);
		if(count($server_arr)==3){$server = $server_arr[1].'.'.$server_arr[2];}
		$order = um_get_setting('order_id');
		$sn = um_get_setting('sn');
		$key = 0;
		$data = array(
			'product_id' => 1392,
			'domain' => $server,
			'order' => $order,
			'sn' => $sn
		);
		if(um_curl_post('http://www.zhiyanblog.com/cdn/tinection/ping_new.php',$data)){
			$return = um_curl_post('http://www.zhiyanblog.com/cdn/tinection/ping_new.php',$data);
			$return2 = json_decode($return);
			$active = $return2->success;
			$key = $return2->key;
		}else{
			$active = 0;
		}
		if($active==1){
			$value = json_encode(array('order_id'=>$order,'sn'=>$sn,'key'=>$key,'time'=>time()));
			update_option('_wp_um_authorize',$value);
			//set_transient( 'um_authorize', $value , current_time('mysql'), 3600*24*5 );
			if(!empty($para))eval(base64_decode(authcode($code,'DECODE',$key)));else return 1;
		}else{
			$value = json_encode(array('order_id'=>'','sn'=>'','key'=>'','time'=>time()));
			update_option('_wp_um_authorize',$value);
			return 0;
		}
	}else{
		if(!empty($para))eval(base64_decode(authcode($code,'DECODE',$key)));else return 1;
	}
}
}

/* Get template part */
function get_the_template($path){
	$file = UM_DIR.'func/'.$path;
	if(UM_TYPE=='sale'){
		$str = file_get_contents($file);//抓取源代码
		if($str&&um_authorize($str))um_authorize($str);
	}else{
		require_once($file);
	}
}

/* Convert IP */
function um_convertip($ip){
    //$url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php';
	//$data = array('format'=>'json','ip'=>$ip);
    $url = 'http://wap.ip138.com/ip.asp';
    $data = array('ip' => $ip );
    $location = um_curl_post($url,$data);
    preg_match_all("/<b>查询结果：(.*)<\/b>/isU",$location,$result);
    return empty($result[1][0]) ? __('火星','um') : $result[1][0];

}

// Pproduct shortcode
function um_product_shortcode($atts, $content = null){
	extract(shortcode_atts(array('size'=>'lg','id'=>''),$atts));
	if(!empty($id)) {$href = get_permalink($id);$title=get_post_field('post_title',$id);$content = !empty($content)?$content:'购买';return '<div style="margin:5px auto;text-align:center;"><a class="btnhref" href="'.$href.'" title="'.$title.'" target="_blank"><button type="button" class="btn btn-product btn-'.$size.'">'.$content.'</button></a></div>';}
	else{return '<button type="button" class="btn btn-product btn-'.$size.'">'.$content.'</button>';}
}
add_shortcode('product', 'um_product_shortcode');

// Replace author home in comment loop
function um_comment_url_to_author_homepage($content){
	global $comment;
	$comment_ID = $comment->comment_ID;
	$user_id = (int)$comment->user_id;
	$url    = get_comment_author_url( $comment_ID );
	$author = get_comment_author( $comment_ID );
	if ( $user_id>0 ){
		$author_home = um_get_user_url('post',$user_id);
		$return = "<a href='".$author_home."' rel='external nofollow' class='url author_home' title='访问".$author."的个人主页'>$author</a>";
	}else{
		$return = $author;
	}
	return $return;
}
add_filter('get_comment_author_link','um_comment_url_to_author_homepage',99);

// Romove admin_bar
add_filter('show_admin_bar', '__return_false');

// Change default role
function um_default_role(){
	if(get_option('default_role')!='contributor')update_option('default_role','contributor');
}
add_action('admin_menu','um_default_role');
function um_allow_contributor_uploads() {
	if ( current_user_can('contributor') && !current_user_can('upload_files') ){
		$contributor = get_role('contributor');
  		$contributor->add_cap('upload_files');
	} 
}
add_action('admin_init', 'um_allow_contributor_uploads');

// Query products
function um_query_products($showposts=4,$author=0,$orderby='date'){
	$args = array('post_type'=>'store','orderby'=>$orderby,'showposts'=>$showposts);
	if($author){$args['author']=$author;}
	$products_query = new WP_Query($args);
	//if( $products_query->have_posts() ){while ($products_query->have_posts()) : $products_query->the_post();
	return $products_query;
}

// Author products display depended on viptype
function um_author_products_display($showposts=4,$author=0,$orderby='date'){
	$is_vip = getUserMemberType($author);
	if($is_vip)$author_arg = $author; else $author_arg = 0; 
	$products_query = um_query_products($showposts,$author_arg,$orderby);
	if( $products_query->have_posts() ){
		while ($products_query->have_posts()) : $products_query->the_post();
		if ( has_post_thumbnail() ){
			$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large');
			$imgsrc = $large_image_url[0];
		}else{$imgsrc = um_catch_first_image();}
?>
	<li><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" rel="bookmark" class="fancyimg">
		<div class="thumb-img">
			<img src="<?php echo um_timthumb($imgsrc,280,180); ?>" alt="<?php the_title(); ?>">
			<span><i class="fa fa-shopping-cart"></i></span>
		</div>
		</a>
		<p><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></p>
	</li>
<?php endwhile;} wp_reset_query();
}

// Add vipsaler role
//add_role('vipsaler','VIP商家',array('upload_files'=>true,'edit_posts'=>true,'edit_published_posts'=>true,'publish_posts'=>true,'read'=>true));

// Set role
function um_set_role($uid,$role='contributor'){
	$uid = (int)$uid;
	if(!$uid)return;
	$user = new WP_User($uid);
	$user->set_role($role);
}
?>