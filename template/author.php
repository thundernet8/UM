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
<?php
global $wp_query;
// Current author
$curauth = $wp_query->get_queried_object();
$user_name = filter_var($curauth->user_url, FILTER_VALIDATE_URL) ? '<a href="'.$curauth->user_url.'" target="_blank" rel="external">'.$curauth->display_name.'</a>' : $curauth->display_name;
$user_info = get_userdata($curauth->ID);
$posts_count =  $wp_query->found_posts;
$comments_count = get_comments( array('status' => '1', 'user_id'=>$curauth->ID, 'count' => true) );
$collects = $user_info->um_collect?$user_info->um_collect:0;
$collects_array = explode(',',$collects);
$collects_count = $collects!=0?count($collects_array):0;
$credit = intval($user_info->um_credit);
$credit_void = intval($user_info->um_credit_void);
// Current user
$current_user = wp_get_current_user();
// Myself?
$oneself = $current_user->ID==$curauth->ID || current_user_can('edit_users') ? 1 : 0;
// Admin ?
$admin = $current_user->ID==$curauth->ID&&current_user_can('edit_users') ? 1 : 0;
// Tabs
$top_tabs = array(
	'post' => __('文章','um')."($posts_count)",
	'comment' => __('评论','um')."($comments_count)",
	'collect' => __('收藏','um')."($collects_count)",
	'credit' => __('积分','um')."($credit)",
	'message' => __('消息','um')
);

$manage_tabs = array(
	'profile' => __('个人资料','um')
);
if($oneself){$manage_tabs['membership']='会员信息';}
if($oneself)$manage_tabs['orders']='站内订单';
if($admin)$manage_tabs['siteorders']='订单管理';
$manage_tabs['affiliate']='我的推广';
if($admin)$manage_tabs['coupon']='优惠码';

$other_tabs = array(
	'following' => __('关注','um'),
	'follower' => __('粉丝','um')
);

$tabs = array_merge($top_tabs,$manage_tabs,$other_tabs);
foreach( $tabs as $tab_key=>$tab_value ){
	if( $tab_key ) $tab_array[] = $tab_key;
}

// Current tab
$get_tab = isset($_GET['tab']) && in_array($_GET['tab'], $tab_array) ? $_GET['tab'] : 'post';

// 提示
$message = $pages = '';
if($get_tab=='profile' && ($current_user->ID!=$curauth->ID && current_user_can('edit_users')) ) $message = sprintf(__('你正在查看的是%s的资料，修改请慎重！', 'um'), $curauth->display_name);

	// 积分start
	
	if( isset($_POST['creditNonce']) && current_user_can('edit_users') ){
		if ( ! wp_verify_nonce( $_POST['creditNonce'], 'credit-nonce' ) ) {
			$message = __('安全认证失败，请重试！','um');
		}else{
			$c_user_id =  $curauth->ID;
			if( isset($_POST['creditChange']) && sanitize_text_field($_POST['creditChange'])=='add' ){
				$c_do = 'add';
				$c_do_title = __('增加','um');
			}else{
				$c_do = 'cut';
				$c_do_title = __('减少','um');
			}

			$c_num =  intval($_POST['creditNum']);
			$c_desc =  sanitize_text_field($_POST['creditDesc']);
			
			$c_desc = empty($c_desc) ? '' : __('备注','um') . ' : '. $c_desc;

			update_um_credit( $c_user_id , $c_num , $c_do , 'um_credit' , sprintf(__('%1$s将你的积分%2$s %3$s 分。%4$s','um') , $current_user->display_name, $c_do_title, $c_num, $c_desc) );
			
			$message = sprintf(__('操作成功！已将%1$s的积分%2$s %3$s 分。','um'), $user_name, $c_do_title, $c_num);
		}
	}	
	
	//~ 积分end

	// 会员start
	if( isset($_POST['promoteVipNonce']) && current_user_can('edit_users') ){
		if ( ! wp_verify_nonce( $_POST['promoteVipNonce'], 'promotevip-nonce' ) ) {
			$message = __('安全认证失败，请重试！','um');
		}else{
			if( isset($_POST['promotevip_type']) && sanitize_text_field($_POST['promotevip_type'])=='4' ){
				$pv_type = 4;
				$pv_type_title = __('终身会员','um');
			}elseif( isset($_POST['promotevip_type']) && sanitize_text_field($_POST['promotevip_type'])=='3' ){
				$pv_type = 3;
				$pv_type_title = __('年费会员','um');
			}elseif(isset($_POST['promotevip_type']) && sanitize_text_field($_POST['promotevip_type'])=='2'){
				$pv_type = 2;
				$pv_type_title = __('季费会员','um');
			}else{
				$pv_type = 1;
				$pv_type_title = __('月费会员','um');
			}
			$pv_expire_date =  sanitize_text_field($_POST['vip_expire_date']);

			um_manual_promotevip($curauth->ID,$curauth->display_name,$curauth->user_email,$pv_type,$pv_expire_date);
			
			$message = sprintf(__('操作成功！已成功将%1$s提升至%2$s，有效期至 %3$s。','um'), $curauth->display_name, $pv_type_title, date('Y年m月d日 H时i分s秒',strtotime($pv_expire_date)));
			$message .= ' <a href="'.um_get_current_page_url().'">'.__('点击刷新','um').'</a>';
		}
	}
	
	//~ 会员end

	// 优惠码start
	
	if( isset($_POST['couponNonce']) && current_user_can('edit_users') ){
		if ( ! wp_verify_nonce( $_POST['couponNonce'], 'coupon-nonce' ) ) {
			$message = __('安全认证失败，请重试！','um');
		}else{
			if( isset($_POST['coupon_type']) && sanitize_text_field($_POST['coupon_type'])=='once' ){
				$p_type = 'once';
				$p_type_title = __('一次性','um');
			}else{
				$p_type = 'multi';
				$p_type_title = __('可重复使用','um');
			}
			$p_discount =  sprintf('%0.2f',intval($_POST['discount_value']*100)/100);
			$p_expire_date =  sanitize_text_field($_POST['expire_date']);
			$p_code = sanitize_text_field($_POST['coupon_code']);

			add_um_couponcode($p_code,$p_type,$p_discount,$p_expire_date);
			
			$message = sprintf(__('操作成功！已成功添加优惠码%1$s，类型：%2$s 折扣：%3$s 有效期至：%4$s。','um'), $p_code, $p_type_title, $p_discount, date('Y年m月d日 H时i分s秒',strtotime($p_expire_date)));
		}
	}
	
	if( isset($_POST['dcouponNonce']) && current_user_can('edit_users') ){
		if ( ! wp_verify_nonce( $_POST['dcouponNonce'], 'dcoupon-nonce' ) ) {
			$message = __('安全认证失败，请重试！','um');
		}else{
			$coupon_id = intval($_POST['coupon_id']);
			delete_um_couponcode($coupon_id);
			$message = __('操作成功！已成功删除指定优惠码','um');
		}		
	}
	//~ 优惠码end

	//~ 私信start
	$get_pm = isset($_POST['pm']) ? trim($_POST['pm']) : '';
	if( isset($_POST['pmNonce']) && $get_pm && is_user_logged_in() ){
		if ( ! wp_verify_nonce( $_POST['pmNonce'], 'pm-nonce' ) ) {
			$message = __('安全认证失败，请重试！','um');
		}else{
			$pm_title = json_encode(array(
				'pm' => $curauth->ID,
				'from' => $current_user->ID
			));
			if( add_um_message( $curauth->ID, 'unrepm', '', $pm_title, $get_pm ) ) $message = __('发送成功！','um');
		}
	}
	
	//~ 私信end

	//~ 页码start
	$paged = max( 1, get_query_var('page') );
	$number = get_option('posts_per_page', 10);
	$offset = ($paged-1)*$number;
	//~ 页码end

	$item_html = '<li class="tip">'.__('没有找到记录','um').'</li>';

	//~ 个人资料
if( $oneself ){
	$user_id = $curauth->ID;
	$avatar = $user_info->um_avatar;
	$qq = um_is_open_qq();
	$weibo = um_is_open_weibo();
	if( isset($_POST['update']) && wp_verify_nonce( trim($_POST['_wpnonce']), 'check-nonce' ) ) {
		$message = __('没有发生变化','um');	
		$update = sanitize_text_field($_POST['update']);
		if($update=='info'){
			$update_user_id = wp_update_user( array(
				'ID' => $user_id, 
				'nickname' => sanitize_text_field($_POST['display_name']),
				'display_name' => sanitize_text_field($_POST['display_name']),
				'user_url' => esc_url($_POST['siteurl']),
				'description' => $_POST['description'],
				'um_gender' => $_POST['um_gender']
			 ) );
			if (($_FILES['file']['error'])==0&&!empty($_FILES['file'])) {
				define( 'AVATARS_PATH', ABSPATH.'/wp-content/uploads/avatars/' );
				$filetype=array("jpg","gif","bmp","jpeg","png");
    			$ext = pathinfo($_FILES['file']['name']);
    			$ext = strtolower($ext['extension']);
    			$tempFile = $_FILES['file']['tmp_name'];
    			$targetPath   = AVATARS_PATH;
    			if( !is_dir($targetPath) ){
        			mkdir($targetPath,0755,true);
    			}
    			$new_file_name = 'avatar-'.$user_id.'.'.$ext;
    			$targetFile = $targetPath . $new_file_name;
    			if(!in_array($ext, $filetype)){
    				$message = __('仅允许上传JPG、GIF、BMP、PNG图片','um');
    			}else{
    				move_uploaded_file($tempFile,$targetFile);
    				if( !file_exists( $targetFile ) ){
	        			$message = __('图片上传失败','um');
    				} elseif( !$imginfo=um_getImageInfo($targetFile) ) {
        				$message = __('图片不存在','um');
    				} else {
        				$img = $new_file_name;
        				um_resize($img);
        				$message = __('头像上传成功','um');
        				$update_user_avatar = update_user_meta( $user_id , 'um_avatar', 'customize');
						$update_user_avatar_img = update_user_meta( $user_id , 'um_customize_avatar', $img);
   	 				}
   	 			}
			} else {
	    		$update_user_avatar = update_user_meta( $user_id , 'um_avatar', sanitize_text_field($_POST['avatar']) );
				if ( ! is_wp_error( $update_user_id ) || $update_user_avatar ) $message = __('基本信息已更新','um');	
			}
		}
		if($update=='info-more'){
			$update_user_id = wp_update_user( array(
				'ID' => $user_id, 
				'um_sina_weibo' => $_POST['um_sina_weibo'],
				'um_qq_weibo' => $_POST['um_qq_weibo'],
				'um_twitter' => $_POST['um_twitter'],
				'um_googleplus' => $_POST['um_googleplus'],
				'um_weixin' => $_POST['um_weixin'],
				'um_donate' => $_POST['um_donate'],
				'um_qq' => $_POST['um_qq'],
				'um_alipay_email' => $_POST['um_alipay_email']
			 ) );
			if ( ! is_wp_error( $update_user_id ) ) $message = __('扩展资料已更新','um');
		}	
		if($update=='pass'){
			$data = array();
			$data['ID'] = $user_id;
			$data['user_email'] = sanitize_text_field($_POST['email']);
			if( !empty($_POST['pass1']) && !empty($_POST['pass2']) && $_POST['pass1']===$_POST['pass2'] ) $data['user_pass'] = sanitize_text_field($_POST['pass1']);
			$user_id = wp_update_user( $data );
			if ( ! is_wp_error( $user_id ) ) $message = __('安全信息已更新','um');
		}
		
		$message .= ' <a href="'.um_get_current_page_url().'">'.__('点击刷新','um').'</a>';
		
		$user_info = get_userdata($curauth->ID);
	}
}
//~ 个人资料end
	
	
//~ 投稿start

if( isset($_GET['action']) && in_array($_GET['action'], array('new', 'edit')) && $oneself ){
	
	if( isset($_GET['id']) && is_numeric($_GET['id']) && get_post($_GET['id']) && intval(get_post($_GET['id'])->post_author) === get_current_user_id() ){
		$action = 'edit';
		$the_post = get_post($_GET['id']);
		$post_title = $the_post->post_title;
		$post_content = $the_post->post_content;
		foreach((get_the_category($_GET['id'])) as $category) { 
			$post_cat[] = $category->term_id; 
		}
	}else{
		$action = 'new';
		$post_title = !empty($_POST['post_title']) ? $_POST['post_title'] : '';
		$post_content = !empty($_POST['post_content']) ? $_POST['post_content'] : '';
		$post_cat = !empty($_POST['post_cat']) ? $_POST['post_cat'] : array();
	}

	if( isset($_POST['action']) && trim($_POST['action'])=='update' && wp_verify_nonce( trim($_POST['_wpnonce']), 'check-nonce' ) ) {
		
		$title = sanitize_text_field($_POST['post_title']);
		$content = $_POST['post_content'];
		$cat = (!empty($_POST['post_cat'])) ? $_POST['post_cat'] : '';
		
		if( $title && $content ){
			
			if( mb_strlen($content,'utf8')<140 ){
				
				$message = __('提交失败，文章内容至少140字。','um');
				
			}else{
				
				$status = sanitize_text_field($_POST['post_status']);
				
				if( $action==='edit' ){

					$new_post = wp_update_post( array(
						'ID' => intval($_GET['id']),
						'post_title'    => $title,
						'post_content'  => $content,
						'post_status'   => ( $status==='pending' ? 'pending' : 'draft' ),
						'post_author'   => get_current_user_id(),
						'post_category' => $cat
					) );

				}else{

					$new_post = wp_insert_post( array(
						  'post_title'    => $title,
						  'post_content'  => $content,
						  'post_status'   => ( $status==='pending' ? 'pending' : 'draft' ),
						  'post_author'   => get_current_user_id(),
						  'post_category' => $cat
						) );

				}
				
				if( is_wp_error( $new_post ) ){
					$message = __('操作失败，请重试或联系管理员。','um');
				}else{
					
					//update_post_meta( $new_post, 'um_copyright_content', htmlspecialchars($_POST['post_copyright']) );
					
					wp_redirect(um_get_user_url('post'));
				}

			}
		}else{
			$message = __('投稿失败，标题和内容不能为空！','um');
		}
	}
}
//~ 投稿end

?>
<!-- Header -->
<?php get_header(); ?>

<!-- Main Wrap -->
<div id="main-wrap">
	<div class="bd clx" id="author-page">
	<!-- Cover -->
	<div id="cover">
		<img src="<?php if(get_user_meta($curauth->ID,'um_cover',true)) echo get_user_meta($curauth->ID,'um_cover',true); else echo UM_URI.'static/img/cover/1.jpg'; ?>" alt="个人封面">
			<?php if($current_user->ID==$curauth->ID){ ?><a href="#" id="custom-cover">自定义封面</a><?php } ?>
	</div>
	<!-- Cover change -->
	<div id="cover-change">
		<div id="cover-c-header"><strong>自定义封面</strong><a href="#" id="cover-close">X</a></div>
		<div id="cover-list">
			<div id="cover-change-inner">
				<ul class="clx">
					<li><a href="#" class="basic"><img src="<?php echo UM_URI.'static/img/cover/1-small.jpg'; ?>" width="240" height="64"></a></li>
					<li><a href="#" class="basic"><img src="<?php echo UM_URI.'static/img/cover/2-small.jpg'; ?>" width="240" height="64"></a></li>
					<li><a href="#" class="basic"><img src="<?php echo UM_URI.'static/img/cover/3-small.jpg'; ?>" width="240" height="64"></a></li>
					<li><a href="#" class="basic"><img src="<?php echo UM_URI.'static/img/cover/4-small.jpg'; ?>" width="240" height="64"></a></li>
					<li><a href="#" class="basic"><img src="<?php echo UM_URI.'static/img/cover/5-small.jpg'; ?>" width="240" height="64"></a></li>
					<li><a href="#" class="basic"><img src="<?php echo UM_URI.'static/img/cover/6-small.jpg'; ?>" width="240" height="64"></a></li>
					<?php if(get_user_meta($curauth->ID,'um_cover',true)){ ?>
					<li><a href="#" id="uploaded-cover" class="basic"><img src="<?php echo get_user_meta($curauth->ID,'um_cover',true); ?>" width="240" height="64"></a></li>
					<?php } ?>
					<?php if($current_user->ID==$curauth->ID){ ?><li><a href="#" id="upload-cover"><span>+</span></a></li>
					<?php } ?>
				</ul>
				<div id="cover-c-footer">
					<a href="#" id="cover-sure" curuserid="<?php echo $current_user->ID; ?>">确定</a>
					<a href="#" id="cover-cancle">取消</a>
				</div>
				<script type="text/javascript">var default_cover = "<?php echo UM_URI.'static/img/cover/1.jpg'; ?>";</script>
			</div>
		</div>	
	</div>
	<!-- Author info -->
	<div id="ai">
		<div id="avatar-wrap">
			<?php echo um_get_avatar( $curauth->ID , '140' , um_get_avatar_type($curauth->ID) ); ?>
			<div id="num-info">
				<div><span class="num"><?php echo um_following_count($curauth->ID); ?></span><span class="text">关注</span></div>
				<div><span class="num"><?php echo um_follower_count($curauth->ID); ?></span><span class="text">粉丝</span></div>
				<div><span class="num"><?php echo $posts_count; ?></span><span class="text">文章</span></div>
			</div>
		</div>
		<div class="name"><?php echo $curauth->display_name; ?><?php if($curauth->um_gender=='female') echo '<i class="img-icon icon_female"></i>'; else echo '<i class="img-icon icon_male"></i>'; ?><?php echo um_member_icon($curauth->ID); ?></div>
		<div class="des"><?php $description = $curauth->description;echo $description ? $description : __('这家伙很懒，什么也没有留下','um'); ?></div>
		<?php if($curauth->ID!=$current_user->ID){ ?>
		<div class="fp-btns">
		<?php echo um_follow_button($curauth->ID); ?>
		<span class="pm-btn"><a href="<?php echo add_query_arg('tab', 'message', get_author_posts_url( $curauth->ID )); ?>" title="发送私信">私信</a></span>
		</div>
		<?php }else{ ?>
		<?php if($current_user->ID){ ?><a href="<?php echo um_get_user_url('profile'); ?>" class="edit-btn" title="编辑个人资料">编辑个人资料</a><?php } ?>
		<?php } ?>
	</div>
	<!-- Main content -->
	<div id="mc">
		<div id="mc-body">
			<div id="mc-bdinner">
			<?php if(!isset($_GET['action'])||($_GET['action']!='edit'&&$_GET['action']!='new')) $cls = 'part'; else $cls = 'full'; ?>
				<div id="mc-body-box" class="clx <?php echo $cls; ?>">
					<!-- Left content -->
					<div id="lc">
						<div id="tab-bar">
							<ul class="clx">
								<li class="<?php if(!isset($_GET['tab'])||(isset($_GET['tab'])&&$_GET['tab']=='post')) echo 'current'; ?>"><a class="tab-post" href="<?php echo um_get_user_url('post',$curauth->ID); ?>" title="文章">文章</a></li>
								<li class="<?php if(isset($_GET['tab'])&&$_GET['tab']=='comment') echo 'current'; ?>"><a class="tab-comment" href="<?php echo um_get_user_url('comment',$curauth->ID); ?>" title="评论">评论</a></li>
								<li class="<?php if(isset($_GET['tab'])&&$_GET['tab']=='collect') echo 'current'; ?>"><a class="tab-collect" href="<?php echo um_get_user_url('collect',$curauth->ID); ?>" title="收藏">收藏</a></li>
								<li class="<?php if(isset($_GET['tab'])&&$_GET['tab']=='message') echo 'current'; ?>"><a class="tab-msg" href="<?php echo um_get_user_url('message',$curauth->ID); ?>" title="消息">消息</a></li>							
								<li class="<?php if(isset($_GET['tab'])&&$_GET['tab']=='credit') echo 'current'; ?>"><a class="tab-credit" href="<?php echo um_get_user_url('credit',$curauth->ID); ?>" title="积分">积分</a></li>
								<div class="clear"></div>
							</ul>
						</div>
						<div id="tab-content">
						<!-- Page global message -->
						<?php if($message) echo '<div class="alert alert-success">'.$message.'</div>'; ?>
						<!-- Tab-post -->

						<?php if( $get_tab=='post' ) {
							$can_post_cat = get_cat_ids()?get_cat_ids():0;
							$cat_count = $can_post_cat!=0?count($can_post_cat):0;
							if( isset($_GET['action']) && in_array($_GET['action'], array('new', 'edit')) && $cat_count && is_user_logged_in() && $oneself && current_user_can('edit_posts') ){
								echo '<ul class="user-msg"><li class="tip">'.__('请发表你自己的文章','um').'</ul></li>';
						?>
						<article class="panel panel-default <?php if(!isset($_GET['action'])) echo 'archive'; ?>" role="main">
							<div class="panel-body" style="padding:0;">
							<h3 class="page-header"><?php _e('投稿','um');?> <small><?php _e('POST NEW','um');?></small></h3>
							<form role="form" method="post">
								<div class="form-group">
									<input type="text" class="form-control" name="post_title" placeholder="<?php _e('在此输入标题','um');?>" value="<?php echo $post_title;?>" aria-required='true' required>
								</div>
								<div class="form-group">
								<?php wp_editor(  wpautop($post_content), 'post_content', array('media_buttons'=>true, 'quicktags'=>true, 'editor_class'=>'form-control', 'editor_css'=>'<style>.wp-editor-container{border:1px solid #ddd;}.switch-html, .switch-tmce{height:25px !important}</style>' ) ); ?>
								</div>
								<div class="form-group">
								<?php
									$can_post_cat = get_cat_ids();
									if($can_post_cat){
										$post_cat_output = '<p class="help-block">'.__('选择文章分类', 'um').'</p>';
										$post_cat_output .= '<select name="post_cat[]" class="form-control">';
										foreach ( $can_post_cat as $term_id ) {
											$category = get_category( $term_id );
											//~ if( (!empty($post_cat)) && in_array($category->term_id,$post_cat)) 
											$post_cat_output .= '<option value="'.$category->term_id.'">'.$category->name.'</option>';
										}
										$post_cat_output .= '</select>';
										echo $post_cat_output;
									}
								?>
								</div>
								<div class="form-group text-right">
									<select name="post_status">
										<option value ="pending"><?php _e('提交审核','um');?></option>
										<option value ="draft"><?php _e('保存草稿','um');?></option>
									</select>
									<input type="hidden" name="action" value="update">
									<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce( 'check-nonce' );?>">
									<button type="submit" class="btn btn-success" style="margin-top:5px;"><?php _e('确认操作','um');?></button>
								</div>	
							</form>
							</div>
			 			</article>
					<?php }else{
					if($cat_count){
							$item_html = sprintf( __('现有%s个分类接受投稿。', 'um'), $cat_count );
							if( is_user_logged_in() && !current_user_can('edit_posts') ){
								$item_html .= __('遗憾的是，你现在登录的账号没有投稿权限！', 'um');	
						}else{
							$item_html .= '<a href="'.( is_user_logged_in() ? add_query_arg(array('tab'=>'post','action'=>'new'), get_author_posts_url($current_user->ID)) : wp_login_url() ).'">'.__('点击投稿', 'um').'</a>';
						}
					}else{
						if( have_posts() ) $item_html = sprintf( __('发表了 %s 篇文章', 'um'), $posts_count );
					}
					echo '<ul class="user-msg"><li class="tip">'.$item_html.'</ul></li>';
					global $wp_query;
					$args = is_user_logged_in() ? array_merge( $wp_query->query_vars, array( 'post_status' => array( 'publish', 'pending', 'draft' ) ) ) : $wp_query->query_vars;
					query_posts( $args );
					while ( have_posts() ) : the_post();
						include(UM_DIR.'template/content-archive.php');
					endwhile; // end of the loop. 
					um_paginate();
					wp_reset_query();
				}
			}
			?>
		<!-- End Tab-post -->
		<!-- Tab-comment -->
		<?php 
		if( $get_tab=='comment' ) {
			$comments_status = $oneself ? '' : 'approve';
			$all = get_comments( array('status' => '', 'user_id'=>$curauth->ID, 'count' => true) );
			$approve = get_comments( array('status' => '1', 'user_id'=>$curauth->ID, 'count' => true) );
			$pages = $oneself ? ceil($all/$number) : ceil($approve/$number);
			$comments = get_comments(array('status' => $comments_status,'order' => 'DESC','number' => $number,'offset' => $offset,'user_id' => $curauth->ID));
		if($comments){
			$item_html = '<li class="tip">' . sprintf(__('共有 %1$s 条评论，其中 %2$s 条已获准， %3$s 条正等待审核。','um'),$all, $approve, $all-$approve) . '</li>';
		foreach( $comments as $comment ){
			$item_html .= ' <li>';
			if($comment->comment_approved!=1) $item_html .= '<small class="text-danger">'.__( '这条评论正在等待审核','um' ).'</small>';
			$item_html .= '<div class="message-content">'.$comment->comment_content . '</div>';
			$item_html .= '<a class="info" href="'.htmlspecialchars( get_comment_link( $comment->comment_ID) ).'">'.sprintf(__('%1$s  发表在  %2$s','um'),$comment->comment_date,get_the_title($comment->comment_post_ID)).'</a>';
			$item_html .= '</li>';
		}
		if($pages>1) $item_html .= '<li class="tip">'.sprintf(__('第 %1$s 页，共 %2$s 页，每页显示 %3$s 条。','um'),$paged, $pages, $number).'</li>';
		}
		echo '<ul class="user-msg">'.$item_html.'</ul>';
		echo um_pager($paged, $pages);
		}
		?>
		<!-- End Tab-comment -->
		<!-- Tab-collect -->
		<?php 
			if( $get_tab=='collect'){
			$item_html = '<li class="tip">'.__('共收藏了','um').$collects_count.'篇文章</li>';
			echo '<ul class="user-msg"><li class="tip">'.$item_html.'</ul></li>';
			//global $wp_query;
			//$args = array_merge( $wp_query->query_vars, array( 'post__in' => $collects_array, 'post_status' => 'publish' ) );
			query_posts( array( 'post__not_in'=>get_option('sticky_posts'), 'post__in' => $collects_array, 'post_status' => 'publish' ) );
			while ( have_posts() ) : the_post();
			include(UM_DIR.'template/content-archive.php');
			endwhile; // end of the loop. 
			um_paginate();
			wp_reset_query();
			}

		?>
		<!-- End Tab-collect -->
		<!-- Tab-message -->
		<?php
			if( $get_tab=='message' ) {

	if($current_user->ID==$curauth->ID){
		$all_sql = "( msg_type='read' OR msg_type='unread' OR msg_type='repm' OR msg_type='unrepm' )";

		$all = get_um_message($curauth->ID, 'count', $all_sql);
		
		$pages = ceil($all/$number);
		

		$mLog = get_um_message($curauth->ID, '', $all_sql, $number,$offset);

		$unread = intval(get_um_message($curauth->ID, 'count', "msg_type='unread' OR msg_type='unrepm'"));
		
		if($mLog){
			$item_html = '<li class="tip">' . sprintf(__('共有 %1$s 条消息，其中 %2$s 条是新消息（绿色标注）。','um'), $all, $unread) . '</li>';
			foreach( $mLog as $log ){
				$unread_tip = $unread_class = '';
				if(in_array($log->msg_type, array('unread', 'unrepm'))){
					$unread_tip = '<span class="tag">'.__('新！', 'um').'</span>';
					$unread_class = ' class="unread"';
					update_um_message_type( $log->msg_id, $curauth->ID , ltrim($log->msg_type, 'un') );
				}
				$msg_title =  $log->msg_title;
				if(in_array($log->msg_type, array('repm', 'unrepm'))){
					$msg_title_data = json_decode($log->msg_title);
					$msg_title = get_the_author_meta('display_name', intval($msg_title_data->from));
					$msg_title = sprintf(__('%s发来的私信','um'), $msg_title).' <a href="'.add_query_arg('tab', 'message', get_author_posts_url(intval($msg_title_data->from))).'#'.$log->msg_id.'">'.__('查看对话','um').'</a>';
				}
				$item_html .= '<li'.$unread_class.'><div class="message-content">'.htmlspecialchars_decode($log->msg_content).' </div><p class="info">'.$unread_tip.'  '.$msg_title.'  '.$log->msg_date.'</p></li>';
			}
			if($pages>1) $item_html .= '<li class="tip">'.sprintf(__('第 %1$s 页，共 %2$s 页，每页显示 %3$s 条。','um'),$paged, $pages, $number).'</li>';
		}
		
	}else{
		
		if( is_user_logged_in() ){
			
			$item_html = '<li class="tip">'.sprintf(__('与 %s 对话','um'), $user_info->display_name).'</li><li><form id="pmform" role="form" method="post"><input type="hidden" name="pmNonce" value="'.wp_create_nonce( 'pm-nonce' ).'" ><p><textarea class="form-control" rows="3" name="pm" required></textarea></p><p class="clearfix"><a class="btn btn-link pull-left" href="'.add_query_arg('tab', 'message', get_author_posts_url($current_user->ID)).'">'.__('查看我的消息','um').'</a><button type="submit" class="btn btn-primary pull-right">'.__('确定发送','um').'</button></p></form></li>';
			
			$all = get_um_pm( $curauth->ID, $current_user->ID, true );
			$pages = ceil($all/$number);
			
			$pmLog = get_um_pm( $curauth->ID, $current_user->ID, false, false, $number, $offset );
			if($pmLog){
				foreach( $pmLog as $log ){
					$pm_data = json_decode($log->msg_title);
					if( $pm_data->from==$curauth->ID ){
						update_um_message_type( $log->msg_id, $curauth->ID , 'repm' );
					}
					$item_html .= '<li id="'.$log->msg_id.'"><div class="message-content clearfix"><a class="'.( $pm_data->from==$current_user->ID ? 'pull-right' : 'pull-left' ).'" href="'.get_author_posts_url($pm_data->from).'">'.um_get_avatar( $pm_data->from , '34' , um_get_avatar_type($pm_data->from), false ).'</a><div class="pm-box"><div class="pm-content'.( $pm_data->from==$current_user->ID ? '' : ' highlight' ).'">'.htmlspecialchars_decode($log->msg_content).'</div><p class="pm-date">'.date_i18n( get_option( 'date_format' ).' '.get_option( 'time_format' ), strtotime($log->msg_date)).'</p></div></div></li>';
				}
			}
			
			if($pages>1) $item_html .= '<li class="tip">'.sprintf(__('第 %1$s 页，共 %2$s 页，每页显示 %3$s 条。','um'),$paged, $pages, $number).'</li>';

		}else{
			$item_html = '<li class="tip">'.sprintf(__('私信功能需要<a href="%s">登录</a>才可使用！','um'), wp_login_url() ).'</li>';
		}
	}
	
	echo '<ul class="user-msg">'.$item_html.'</ul>'.um_pager($paged, $pages);

}
		?>
		<!-- End Tab-message -->
		<!-- Tab-credit -->
		<?php
			if( $get_tab=='credit' ) {

	//~ 积分变更
	if ( current_user_can('edit_users') ) {

	?>
	<div class="panel panel-danger">
		<div class="panel-heading"><?php echo $curauth->display_name.__('积分变更（仅管理员可见）','um');?></div>
		<div class="panel-body">
			<form id="creditform" role="form"  method="post">
				<input type="hidden" name="creditNonce" value="<?php echo  wp_create_nonce( 'credit-nonce' );?>" >
				<p>
					<label class="radio-inline"><input type="radio" name="creditChange" value="add" aria-required='true' required checked=""><?php _e('增加积分','um');?></label>
					<label class="radio-inline"><input type="radio" name="creditChange" value="cut" aria-required='true' required><?php _e('减少积分','um');?></label>
				</p>
				<div class="form-inline">
					<div class="form-group">
						<div class="input-group" style="width:220px;">
							<div class="input-group-addon"><?php _e('积分','um');?></div>
							<input class="form-control" type="text" name="creditNum" aria-required='true' required>
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon"><?php _e('备注','um');?></div>
							<input class="form-control" type="text" name="creditDesc" aria-required='true' required>
						</div>
					</div>
					<button class="btn btn-default" type="submit"><?php _e('提交','um');?></button>
				</div>
				<p class="help-block"><?php _e('请谨慎操作！积分数只能填写数字，备注将显示在用户的积分记录中。','um');?></p>
			</form>
		</div>
	</div>

	<?php
	} 
	
	//~ 积分充值
	if ( $current_user->ID==$curauth->ID ) {

	?>
	<div class="panel panel-success">
		<div class="panel-heading"><?php echo __('积分充值（仅自己可见）','um');?></div>
		<div class="panel-body">
			<form id="creditrechargeform" role="form"  method="post" action="<?php echo UM_URI.'alipay/alipayapi.php'; ?>" onsubmit="return false;">
				<input type="hidden" name="creditrechargeNonce" value="<?php echo  wp_create_nonce( 'creditrecharge-nonce' );?>" >
				<input type = "hidden" id="order_id" name="order_id" readonly="" value="0">
				<input type = "hidden" id="product_id" name="product_id" readonly="" value="-5">
				<p>
					<label><?php echo sprintf(__('当前积分兑换比率为：1元 = %1$s 积分','um'),um_get_setting('exchange_ratio',100));?></label>
				</p>
				<div class="form-inline">
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon"><?php _e('积分*100','um');?></div>
							<input class="form-control" type="text" name="creditrechargeNum" value="10" aria-required='true' required>
						</div>
					</div>
					<button class="btn btn-default" type="submit" id="creditrechargesubmit"><?php _e('充值','um');?></button>
				</div>
				<p class="help-block"><?php _e('积分数以100为单位起计算,请填写整数数值，如填1即表明充值100积分，所需现金根据具体兑换比率计算。','um');?></p>
			</form>
		</div>
	</div>

	<?php
	} 
	
	$item_html = '<li class="tip">' . sprintf(__('共有 %1$s 个积分，其中 %2$s 个已消费， %3$s 个可用。','um'), ($credit+$credit_void), $credit_void, $credit) ;
	if($current_user->ID==$curauth->ID){$item_html .= '&nbsp;(&nbsp;每日签到：'.um_whether_signed($current_user->ID).'&nbsp;)';}
	$item_html .= '</li>';

	if($oneself){
		$all = get_um_message($curauth->ID, 'count', "msg_type='credit'");
		$pages = ceil($all/$number);
		
		$creditLog = get_um_credit_message($curauth->ID, $number,$offset);

		if($creditLog){
			foreach( $creditLog as $log ){
				$item_html .= '<li>'.$log->msg_date.' <span class="message-content" style="background:transparent;">'.$log->msg_title.'</span></li>';
			}
			if($pages>1) $item_html .= '<li class="tip">' . sprintf(__('第 %1$s 页，共 %2$s 页，每页显示 %3$s 条。','um'),$paged, $pages, $number). '</li>';
		}
	}
	
	echo '<ul class="user-msg">'.$item_html.'</ul>';
	
	if($oneself) echo um_pager($paged, $pages);

	?>
    <table class="table table-bordered credit-table">
      <thead>
        <tr class="active">
          <th><?php _e('积分方法','um');?></th>
          <th><?php _e('一次得分','um');?></th>
          <th><?php _e('可用次数','um');?></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?php _e('注册奖励','um');?></td>
          <td><?php printf( __('%1$s 分','um'), um_get_setting('new_reg_credit','50'));?></td>
          <td><?php _e('只有 1 次','um');?></td>
        </tr>
        <tr>
          <td><?php _e('文章投稿','um');?></td>
          <td><?php printf( __('%1$s 分','um'), um_get_setting('contribute_credit','50'));?></td>
          <td><?php printf( __('每天 %1$s 次','um'), um_get_setting('contribute_credit_times','5'));?></td>
        </tr>
        <tr>
          <td><?php _e('评论回复','um');?></td>
          <td><?php printf( __('%1$s 分','um'), um_get_setting('comment_credit','5'));?></td>
          <td><?php printf( __('每天 %1$s 次','um'), um_get_setting('comment_credit_times','50'));?></td>
        </tr>
        <tr>
          <td><?php _e('访问推广','um');?></td>
          <td><?php printf( __('%1$s 分','um'), um_get_setting('aff_visit_credit','5'));?></td>
          <td><?php printf( __('每天 %1$s 次','um'), um_get_setting('aff_visit_credit_times','50'));?></td>
        </tr>
        <tr>
          <td><?php _e('注册推广','um');?></td>
          <td><?php printf( __('%1$s 分','um'), um_get_setting('aff_reg_credit','50'));?></td>
          <td><?php printf( __('每天 %1$s 次','um'), um_get_setting('aff_reg_credit_times','5'));?></td>
        </tr>
        <tr>
          <td><?php _e('每日签到','um');?></td>
          <td><?php printf( __('%1$s 分','um'), um_get_setting('daily_sign_credit','10'));?></td>
          <td><?php _e('每天 1 次','um');?></td>
        </tr>
        <tr>
          <td><?php _e('文章互动','um');?></td>
          <td><?php printf( __('%1$s 分','um'), um_get_setting('like_article_credit','10'));?></td>
          <td><?php printf( __('每天 %1$s 次','um'), um_get_setting('like_article_credit_times','5'));?></td>
        </tr>
		<tr>
          <td><?php _e('发布资源','um');?></td>
          <td><?php printf( __('%1$s 分','um'), um_get_setting('source_download_credit','5'));?></td>
          <td><?php _e('不限次数,收费资源额外返还价格100%积分','um');?></td>
        </tr>
		<tr>
          <td><?php _e('积分兑换','um');?></td>
          <td colspan="2"><?php printf( __('兑换比率：1 元 = %1$s 积分','um'), um_get_setting('exchange_ratio','100'));?></td>
        </tr>
      </tbody>
    </table>
	<?php

}
		?>
		<!-- End Tab-credit -->
		<!-- Tab-profile -->
		<?php
			if( $get_tab=='profile' ) {

		$avatar_type = array(
			'default' => __('默认头像', 'um'),
			'qq' => __('腾讯QQ头像', 'um'),
			'weibo' => __('新浪微博头像', 'um'),
			'customize' => __('自定义头像', 'um'),
		);
		
		$author_profile = array(
			__('头像来源:','um') => $avatar_type[um_get_avatar_type($user_info->ID)],
			__('昵称:','um') => $user_info->display_name,
			__('站点:','um') => $user_info->user_url,
			__('个人说明:','um') => $user_info->description
		);
		
		$profile_output = '';
		foreach( $author_profile as $pro_name=>$pro_content ){
			$profile_output .= '<tr><td class="title">'.$pro_name.'</td><td>'.$pro_content.'</td></tr>';
		}
		
		$days_num = round(( strtotime(date('Y-m-d')) - strtotime( $user_info->user_registered ) ) /3600/24);
		
		echo '<ul class="user-msg"><li class="tip">'.sprintf(__('%s来%s已经%s天了', 'um') , $user_info->display_name, get_bloginfo('name'), ( $days_num>1 ? $days_num : 1 ) ).'</li></ul>'.'<table id="author-profile"><tbody>'.$profile_output.'</tbody></table>';
		
	if( $oneself ){
		
	?>

<form id="info-form" class="form-horizontal" role="form" method="POST" action="">
	<input type="hidden" name="update" value="info">
	<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'check-nonce' );?>">
			<div class="page-header">
				<h3 id="info"><?php _e('基本信息','um');?> <small><?php _e('公开资料','um');?></small></h>
			</div>

	<div class="form-group">
		<label class="col-sm-3 control-label"><?php _e('头像','um');?></label>
		<div class="col-sm-9">

<div class="radio">
<?php echo um_get_avatar( $user_info->ID , '40' , um_get_avatar_type($user_info->ID) ); ?>
  <label>
	<input type="radio" name="avatar"  value="default" <?php if( ($avatar!='qq' || um_is_open_qq($user_info->ID)===false) && ($avatar!='weibo' || um_is_open_weibo($user_info->ID)===false) ) echo 'checked';?>><?php _e('默认头像','um'); ?>
  </label>
  <label id="edit-umavatar"><?php _e('(上传头像)','um'); ?></label>
</div>

<div id="upload-input">    
    <input name="file" type="file"  value="<?php _e('浏览','um'); ?>" >              
    <span id="upload-umavatar"><?php _e('上传','um'); ?></span>   
</div>
<p id="upload-avatar-msg"></p>

<?php if(um_is_open_qq($user_info->ID)){ ?>
<div class="radio">
<?php echo um_get_avatar( $user_info->ID , '40' , 'qq' ); ?>
  <label>
    <input type="radio" name="avatar" value="qq" <?php if($avatar=='qq') echo 'checked';?>> <?php _e('QQ头像', 'um');?>
  </label>
</div>
<?php } ?>

<?php if(um_is_open_weibo($user_info->ID)){ ?>
<div class="radio">
<?php echo um_get_avatar( $user_info->ID , '40' , 'weibo' ); ?>
  <label>
    <input type="radio" name="avatar" value="weibo" <?php if($avatar=='weibo') echo 'checked';?>> <?php _e('微博头像', 'um');?>
  </label>
</div>
<?php } ?>

		</div>
	</div>
	
	<div class="form-group">
		<label for="display_name" class="col-sm-3 control-label"><?php _e('性别','um');?></label>
		<div class="col-sm-9">
			<select name="um_gender">
				<option value ="male" <?php if($user_info->um_gender=='male') echo 'selected = "selected"'; ?>><?php _e('男','um');?></option>
				<option value ="female" <?php if($user_info->um_gender=='female') echo 'selected = "selected"'; ?>><?php _e('女','um');?></option>
			</select>
		</div>
	</div>

	<div class="form-group">
		<label for="display_name" class="col-sm-3 control-label"><?php _e('昵称','um');?></label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="display_name" name="display_name" value="<?php echo $user_info->display_name;?>">
		</div>
	</div>

	<div class="form-group">
		<label for="url" class="col-sm-3 control-label"><?php _e('站点','um');?></label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="url" name="siteurl" value="<?php echo $user_info->user_url;?>">
		</div>
	</div>
	
	<div class="form-group">
		<label for="description" class="col-sm-3 control-label"><?php _e('个人说明','um');?></label>
		<div class="col-sm-9">
			<textarea class="form-control" rows="3" name="description" id="description"><?php echo $user_info->description;?></textarea>
		</div>
	</div>
	
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-9">
			<button type="submit" class="btn btn-primary"><?php _e('保存更改','um');?></button>
		</div>
	</div>
	
</form>

<form id="info-more-form" class="form-horizontal" role="form" method="post">
	<input type="hidden" name="update" value="info-more">
	<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'check-nonce' );?>">
			<div class="page-header">
				<h3 id="info"><?php _e('扩展资料','tin');?> <small><?php _e('社会化信息等','tin');?></small></h>
			</div>
	<div class="form-group">
		<label class="col-sm-3 control-label"><?php _e('新浪微博','tin');?></label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="um_sina_weibo" name="um_sina_weibo" value="<?php echo $user_info->um_sina_weibo;?>">
			<span class="help-block"><?php _e('请填写新浪微博账号','tin');?></span>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label"><?php _e('腾讯微博','tin');?></label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="um_qq_weibo" name="um_qq_weibo" value="<?php echo $user_info->um_qq_weibo;?>">
			<span class="help-block"><?php _e('请填写腾讯微博账号','tin');?></span>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label"><?php _e('腾讯QQ','tin');?></label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="um_qq" name="um_qq" value="<?php echo $user_info->um_qq;?>">
			<span class="help-block"><?php _e('请填写腾讯QQ账号，方便发起在线会话','tin');?></span>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label"><?php _e('Twitter','tin');?></label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="um_twitter" name="um_twitter" value="<?php echo $user_info->um_twitter;?>">
			<span class="help-block"><?php _e('请填写Twitter账号','tin');?></span>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label"><?php _e('Google +','tin');?></label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="um_googleplus" name="um_googleplus" value="<?php echo $user_info->um_googleplus;?>">
			<span class="help-block"><?php _e('请填写Google+主页的完整Url','tin');?></span>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label"><?php _e('微信二维码','tin');?></label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="um_weixin" name="um_weixin" value="<?php echo $user_info->um_weixin;?>">
			<span class="help-block"><?php _e('请填写微信账号二维码图片的Url地址','tin');?></span>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label"><?php _e('支付宝收款二维码','tin');?></label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="um_donate" name="um_donate" value="<?php echo $user_info->um_donate;?>">
			<span class="help-block"><?php _e('请填写支付宝收款二维码图片的Url地址','tin');?></span>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label"><?php _e('支付宝收款账号','tin');?></label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="um_donate" name="um_alipay_email" value="<?php if(user_can($curauth->ID,'edit_users')){echo um_get_setting('alipay_account');}else{echo $user_info->um_alipay_email;}?>">
			<span class="help-block"><?php _e('请填写支付宝收款账号','tin');?></span>
		</div>
	</div>
	
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-9">
			<button type="submit" class="btn btn-primary"><?php _e('提交资料','tin');?></button>
		</div>
	</div>
	
</form>


<?php if($current_user&&$current_user->ID==$curauth->ID) { ?>
<form id="aff-form" class="form-horizontal" role="form">
	<div class="page-header">
		<h3 id="open"><?php _e('推广链接','tin');?> <small><?php _e('可赚取积分','tin');?></small></h>
	</div>
	<div class="form-group">
		<label for="aff" class="col-sm-3 control-label"><?php _e('推广链接','tin');?></label>
		<div class="col-sm-9">
			<input type="text" class="form-control um_aff_url" value="<?php echo get_bloginfo('url').'/?aff='.$current_user->ID; ?>">
		</div>
	</div>
</form>
<?php } ?>
<?php if( $qq || $weibo ) { ?>
<form id="open-form" class="form-horizontal" role="form" method="post">
			<div class="page-header">
				<h3 id="open"><?php _e('绑定账号','tin');?> <small><?php _e('可用于直接登录','tin');?></small></h>
			</div>
			
	<?php if($qq){ ?>
		<div class="form-group">
			<label class="col-sm-3 control-label"><?php _e('QQ账号','tin');?></label>
			<div class="col-sm-9">
		<?php  if(um_is_open_qq($user_info->ID)) { ?>
			<span class="help-block"><?php _e('已绑定','tin');?> <a href="<?php echo home_url('/?connect=qq&action=logout'); ?>"><?php _e('点击解绑','tin');?></a></span>
			<?php echo um_get_avatar( $user_info->ID , '100' , 'qq' ); ?>
		<?php }else{ ?>
			<a class="btn btn-primary" href="<?php echo home_url('/?connect=qq&action=login&redirect='.urlencode(get_edit_profile_url())); ?>"><?php _e('绑定QQ账号','tin');?></a>
		<?php } ?>
			</div>
		</div>
	<?php } ?>

	<?php if($weibo){ ?>
		<div class="form-group">
			<label class="col-sm-3 control-label"><?php _e('微博账号','tin');?></label>
			<div class="col-sm-9">
		<?php if(um_is_open_weibo($user_info->ID)) { ?>
			<span class="help-block"><?php _e('已绑定','tin');?> <a href="<?php echo home_url('/?connect=weibo&action=logout'); ?>"><?php _e('点击解绑','tin');?></a></span>
			<?php echo um_get_avatar( $user_info->ID , '100' , 'weibo' ); ?>
		<?php }else{ ?>
			<a class="btn btn-danger" href="<?php echo home_url('/?connect=weibo&action=login&redirect='.urlencode(get_edit_profile_url())); ?>"><?php _e('绑定微博账号','tin');?></a>
		<?php } ?>
			</div>
		</div>
	<?php } ?>
</form>
<?php } ?>
<form id="pass-form" class="form-horizontal" role="form" method="post">
	<input type="hidden" name="update" value="pass">
	<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'check-nonce' );?>">
			<div class="page-header">
				<h3 id="pass"><?php _e('账号安全','tin');?> <small><?php _e('仅自己可见','tin');?></small></h>
			</div>
	<div class="form-group">
		<label for="email" class="col-sm-3 control-label"><?php _e('电子邮件 (必填)','tin');?></label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="email" name="email" value="<?php echo $user_info->user_email;?>" aria-required='true' required>
		</div>
	</div>
	<div class="form-group">
		<label for="pass1" class="col-sm-3 control-label"><?php _e('新密码','tin');?></label>
		<div class="col-sm-9">
			<input type="password" class="form-control" id="pass1" name="pass1" >
			<span class="help-block"><?php _e('如果您想修改您的密码，请在此输入新密码。不然请留空。','tin');?></span>
		</div>
	</div>
	<div class="form-group">
		<label for="pass2" class="col-sm-3 control-label"><?php _e('重复新密码','tin');?></label>
		<div class="col-sm-9">
			<input type="password" class="form-control" id="pass2" name="pass2" >
			<span class="help-block"><?php _e('再输入一遍新密码。 提示：您的密码最好至少包含7个字符。为了保证密码强度，使用大小写字母、数字和符号（例如! " ? $ % ^ & )）。','tin');?></span>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-9">
			<button type="submit" class="btn btn-primary"><?php _e('保存更改','tin');?></button>
		</div>
	</div>
</form>
	<?php
	}
}
		?>
		<!-- End Tab-profile -->
		<!-- Tab-membership -->
		<?php
			if( $get_tab=='membership'&&$oneself ) {
	echo '<div style="margin-top: 50px;"></div>';
	if($oneself){
?>
<?php $member = getUserMemberInfo($curauth->ID); $member_info = array('会员类型'=>$member['user_type'],'会员状态'=>$member['user_status'],'开通时间'=>$member['startTime'],'到期时间'=>$member['endTime']);$member_info_output='';foreach($member_info as $member_info_name=>$member_info_content) {$member_info_output .='<label class="col-sm-3 control-label">'.$member_info_name.'</label><div class="col-sm-9"><p class="form-control-static">'.$member_info_content.'</p></div>'; }?>
<div class="page-header">
	<h3 id="membership-info"><?php _e('会员信息','um'); ?></h3>
</div>
<div class="form-horizontal">
	<div class="form-group">
	<?php echo $member_info_output; ?>
	</div>
</div>

<?php
}

?>

<?php if(current_user_can('edit_users')){ ?>
	<div class="panel panel-danger">
		<div class="panel-heading"><?php echo __('会员操作（本选项卡及内容仅管理员可见）','um');?></div>
		<div class="panel-body">
			<form id="promotevipform" role="form"  method="post">
				<input type="hidden" name="promoteVipNonce" value="<?php echo  wp_create_nonce( 'promotevip-nonce' );?>" >
				<p>
					<label class="radio-inline"><input type="radio" name="promotevip_type" value="1" aria-required='true' required checked><?php _e('月费会员','um');?></label>
					<label class="radio-inline"><input type="radio" name="promotevip_type" value="2" aria-required='true' required><?php _e('季费会员','um');?></label>
					<label class="radio-inline"><input type="radio" name="promotevip_type" value="3" aria-required='true' required><?php _e('年费会员','um');?></label>
					<label class="radio-inline"><input type="radio" name="promotevip_type" value="4" aria-required='true' required><?php _e('终身会员','um');?></label>
				</p>
				<div class="form-inline">
					<div class="form-group">
						<div class="input-group" style="margin-top:5px;">
							<div class="input-group-addon"><?php _e('会员截止有效期','um');?></div>
							<input class="form-control" type="datetime-local" name="vip_expire_date" aria-required='true' required>
						</div>
					</div>
					<button class="btn btn-default" id="promotevipform-submit" type="submit"><?php _e('确认操作','um');?></button>
				</div>
				<p class="help-block"><?php _e('请谨慎操作！会员截止有效期格式2015-01-01','um');?></p>
			</form>
		</div>
	</div>
<?php } ?>
<!--
<div class="page-header">
	<h3 id="membership-rights">会员权益</h3>
</div>
-->
<div class="page-header">
	<h3 id="membership-join"><?php _e('加入会员','um'); ?> <small><?php _e('加入、续费','um'); ?></small></h3>
</div>
<div class="panel">
	<div class="panel-body">
		<form id="joinvip" role="form" method="post" action="<?php echo UM_URI.'alipay/alipayapi.php'; ?>" onsubmit="return false;">
			<p>
				<input type="hidden" name="vipNonce" value="<?php echo wp_create_nonce( 'vip-nonce' );?>" >
				<input type = "hidden" id="order_id" name="order_id" readonly="" value="0">
				<label class="radio-inline"><input type="radio" name="product_id" value="-1" aria-required="true" required="" checked=""><?php _e('月费会员','um'); ?>(<?php echo um_get_setting('monthly_mb_price',5).'元/月'; ?>)</label>
				<label class="radio-inline"><input type="radio" name="product_id" value="-2" aria-required="true" required=""><?php _e('季费会员','um'); ?>(<?php echo um_get_setting('quarterly_mb_price',12).'元/季'; ?>)</label>
				<label class="radio-inline"><input type="radio" name="product_id" value="-3" aria-required="true" required=""><?php _e('年费会员','um'); ?>(<?php echo um_get_setting('annual_mb_price',45).'元/年'; ?>)</label>
				<label class="radio-inline"><input type="radio" name="product_id" value="-4" aria-required="true" required=""><?php _e('终身会员','um'); ?>(<?php echo um_get_setting('life_mb_price',120).'元/年'; ?>)</label>
				<button class="btn btn-primary" id="joinvip-submit" type=""><?php _e('确认开通','um'); ?></button>
			</p>
			<p class="help-block" style="font-size:12px;"><?php _e('提示:若已开通会员则按照选择开通的类型自动续费,若会员已到期,则按重新开通计算有效期','um'); ?></p>
		</form>
	</div>

</div>

<?php if($oneself){ $vip_orders = getUserMemberOrders($curauth->ID); ?>
<div class="page-header">
	<h3 id="membership-records"><?php _e('会员记录','um'); ?> <small><?php _e('会员订单','um'); ?></small></h3>
</div>
<div class="wrapbox">
		<div class="membership-history order-history">
			<table width="100%" border="0" cellspacing="0">
				<thead>
					<tr>
						<th scope="col"><?php _e('订单号','um'); ?></th>
						<th scope="col"><?php _e('支付时间','um'); ?></th>
						<th scope="col"><?php _e('支付金额','um'); ?></th>
						<th scope="col"><?php _e('开通类型','um'); ?></th>	
						<th scope="col"><?php _e('交易状态','um'); ?></th>
					</tr>
				</thead>
				<tbody class="the-list">
				<?php foreach($vip_orders as $vip_order){ ?>
                    <tr>
						<td><?php echo $vip_order['order_id']; ?></td>
						<td><?php echo $vip_order['order_success_time']; ?></td>
						<td><?php echo $vip_order['order_total_price']; ?></td>
						<td><?php echo output_order_vipType($vip_order['product_id']*(-1)); ?></td>
						<td><?php echo output_order_status($vip_order['order_status']); ?></td>
						</tr>
				<?php } ?>
                </tbody>
			</table>
		</div>
</div>
<?php } ?>
<?php
}
		?>
		<!-- End Tab-membership -->
		<!-- Tab-orders -->
		<?php
			if( $get_tab=='orders' ) {
	if($oneself){
		$oall = get_um_orders($curauth->ID, 'count');
		$pages = ceil($oall/$number);
		$oLog = get_um_orders($curauth->ID, '', '', $number,$offset);
		//$order_records = get_user_order_records(0,$curauth->ID);
?>
<ul class="site-order-list">
<div class="shop" style="margin-top: 50px;">
	<div id="history" class="wrapbox">
		<form id="continue-pay" name="continue-pay" action="<?php echo UM_URI.'alipay/alipayapi.php'; ?>" method="post" style="height:0;">
			<input type = "hidden" id="product_id" name="product_id" readonly="" value="">
            <input type = "hidden" id="order_id" name="order_id" readonly="" value="0">
            <input type = "hidden" id="order_name" name="order_name" readonly="" value="0">
		</form>
		<li class="contextual" style="background:#ceface;color:#44a042;"><?php echo sprintf(__('与 %1$s 相关订单记录（该栏目仅自己和管理员可见）。','um'), $curauth->display_name); ?></li>
		<div class="pay-history">
			<table width="100%" border="0" cellspacing="0" class="table table-bordered orders-table">
				<thead>
					<tr>
						<th scope="col" style="width:20%;"><?php _e('商品名','um'); ?></th>
						<th scope="col"><?php _e('订单号','um'); ?></th>
						<th scope="col"><?php _e('购买时间','um'); ?></th>
						<th scope="col"><?php _e('数量','um'); ?></th>
						<th scope="col"><?php _e('价格','um'); ?></th>
						<th scope="col"><?php _e('总价','um'); ?></th>
						<th scope="col"><?php _e('交易状态','um'); ?></th>
					</tr>
				</thead>
				<tbody class="the-list">
				<?php if($oLog)foreach($oLog as $order_record){ ?>
                    <tr>
						<td><?php if($order_record->product_id>0){echo '<a href="'.get_permalink($order_record->product_id).'" target="_blank" title="'.$order_record->product_name.'">'.$order_record->product_name.'</a>';}else{echo $order_record->product_name;} ?></td>
						<td><?php echo $order_record->order_id; ?></td>
						<td><?php echo $order_record->order_time; ?></td>
						<td><?php echo $order_record->order_quantity; ?></td>
						<td><?php echo $order_record->order_price; ?></td>
						<td><?php echo $order_record->order_total_price; ?></td>
						<td><?php if($order_record->order_status==1){echo '<a href="javascript:" data-id="'.$order_record->id.'" class="continue-pay">继续付款</a>';}else{echo output_order_status($order_record->order_status);}; ?></td>
						</tr>
				<?php } ?>
                </tbody>
			</table>
		</div>
	</div>	
</div>
</ul>
<?php echo um_pager($paged, $pages); ?>
<?php
	}
}
		?>
		<!-- End Tab-orders -->
		<!-- Tab-siteorders -->
		<?php
			if( $get_tab=='siteorders' ) {
if(current_user_can('edit_users')){ ?>
<ul class="site-order-list">
<?php
	$oall = get_um_orders(0, 'count');
	$pages = ceil($oall/$number);
	$oLog = get_um_orders(0, '', '', $number,$offset);
	if($oLog){
		$item_html = '<li class="contextual" style="background:#f2dede;color:#a94442;">' . sprintf(__('全站共有 %1$s 条订单记录（该栏目仅管理员可见）。','um'), $oall) . '</li>';
		$item_html .= '<div class="site-orders">
			<table width="100%" border="0" cellspacing="0" class="table table-bordered orders-table">
				<thead>
					<tr>
						<th scope="col" style="width:20%;">'.__('商品名','um').'</th>
						<th scope="col">'.__('订单号','um').'</th>
						<th scope="col">'.__('买家','um').'</th>
						<th scope="col">'.__('购买时间','um').'</th>
						<th scope="col">'.__('总价','um').'</th>
						<th scope="col">'.__('交易状态','um').'</th>
						<th scope="col">'.__('操作','um').'</th>
					</tr>
				</thead>
				<tbody class="the-list">';
				foreach($oLog as $Log){
					$item_html .= '
                    <tr>
						<td>'.$Log->product_name.'</td>
						<td>'.$Log->order_id.'</td>
						<td>'.$Log->user_name.'</td>
						<td>'.$Log->order_time.'</td>
						<td>'.$Log->order_total_price.'</td>
						<td>';
					if($Log->order_status){$item_html .= output_order_status($Log->order_status);}
					$item_html .= '</td><td>';
					if($Log->order_status==1)$item_html .= '<a class="close-order" href="javascript:" title="关闭过期交易" data="'.$Log->id.'">关闭</a>';
					$item_html .= '</td></tr>';
				}
				$item_html .= '</tbody>
			</table>
		</div>';
		if($pages>1) $item_html .= '<li class="tip">'.sprintf(__('第 %1$s 页，共 %2$s 页，每页显示 %3$s 条。','um'),$paged, $pages, $number).'</li>';
	}
	echo $item_html.'</ul>';
	echo um_pager($paged, $pages);

?>
<?php }
}
		?>
		<!-- End Tab-siteorders -->
		<!-- Tab-coupon -->
		<?php
			if( $get_tab=='coupon' ) {
	if ( current_user_can('edit_users') ) {
?>
	<div class="panel panel-danger">
		<div class="panel-heading"><?php echo __('添加优惠码（本选项卡及内容仅管理员可见）','um');?></div>
		<div class="panel-body">
			<form id="couponform" role="form"  method="post">
				<input type="hidden" name="couponNonce" value="<?php echo  wp_create_nonce( 'coupon-nonce' );?>" >
				<p>
					<label class="radio-inline"><input type="radio" name="coupon_type" value="once" aria-required='true' required checked><?php _e('一次性','um');?></label>
					<label class="radio-inline"><input type="radio" name="coupon_type" value="multi" aria-required='true' required><?php _e('重复使用','um');?></label>
				</p>
				<div class="form-inline">
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon"><?php _e('优惠码','um');?></div>
							<input class="form-control" type="text" name="coupon_code" aria-required='true' required>
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon"><?php _e('折扣','um');?></div>
							<input class="form-control" type="text" name="discount_value" aria-required='true' required>
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon"><?php _e('截止有效期','um');?></div>
							<input class="form-control" type="text" name="expire_date" aria-required='true' required>
						</div>
					</div>
					<button class="btn btn-default" type="submit"><?php _e('添加','um');?></button>
				</div>
				<p class="help-block"><?php _e('请谨慎操作！折扣只能填写0~1之间的数字并精确到2位小数点，有效期格式2015-01-01 10:20:30。','um');?></p>
			</form>
		</div>
	</div>

	<table class="table table-bordered coupon-table">
	  <input type="hidden" name="dcouponNonce" value="<?php echo  wp_create_nonce( 'dcoupon-nonce' );?>" >
      <thead>
        <tr class="active">
          <th><?php _e('优惠码','um');?></th>
          <th><?php _e('类型','um');?></th>
          <th><?php _e('折扣','um');?></th>
		  <th><?php _e('截止有效期','um');?></th>
		  <th><?php _e('操作','um');?></th>
        </tr>
      </thead>
      <tbody>
	  <?php $pcodes=output_um_couponcode(); 
		foreach($pcodes as $pcode){
	  ?>
        <tr>
		  <input type="hidden" name="coupon_id" value="<?php echo $pcode['id']; ?>" >
		 	<td><?php echo $pcode['coupon_code'];?></td>
			<td><?php if($pcode['coupon_type']=='once')echo '一次性'; else echo '可重复'; ?></td>
			<td><?php echo $pcode['discount_value'];?></td>
			<td><?php echo date('Y年m月d日 H时i分s秒',strtotime($pcode['expire_date'])) ;?></td>
			<td class="delete_couponcode"><a><?php _e('删除','um');?></a></td>
        </tr>
	  <?php
		}
	  ?>
      </tbody>
    </table>	
<?php
	}
}
		?>
		<!-- End Tab-coupon -->
		<!-- Tab-following -->


		<!-- End Tab-following -->
		<!-- Tab-follower -->


		<!-- End Tab-follower -->
		<!-- Tab-affiliate -->
				<?php
			if( $get_tab=='affiliate' ) {
if(current_user_can('edit_users')){ ?>
<div class="affiliate-tab">
<?php
	if(isset($_GET['currency'])&&in_array($_GET['currency'], array('cash','credit')))$currency=$_GET['currency']; else $currency =  'cash';
	$aLog = get_um_aff_sum_orders( $curauth->ID , $currency, $number, $offset );
	$aall = $aLog?count($aLog):0;
	$pages = ceil($aall/$number);
?>
	<p id="aff">你的永久推广链接为 <input class="um_aff_url" type="text" name="spreadurl" style="min-width:240px;width:60%;" value="<?php echo get_bloginfo('url').'/?aff='.$current_user->ID; ?>" disabled="disabled"></p>
<?php
	$sum_rewards=get_um_aff_sum_money($curauth->ID);$withdrawed = um_get_withdraw_sum($curauth->ID,'withdrawed');$withdrawing = um_get_withdraw_sum($curauth->ID,'withdrawing');$left = $sum_rewards-$withdrawed-$withdrawing;
	if($currency=='cash'){$type='现金';$otype='积分';$link = um_get_user_url('affiliate',$curauth->ID).'&currency=credit';}else{$type='积分';$otype='现金';$link = um_get_user_url('affiliate',$curauth->ID).'&currency=cash';}
		$item_html = '<li class="contextual" style="background:#ceface;color:#44a042;">' . sprintf(__('全站共有 %1$s 条%2$s推广用户记录，点击查看<a href="%3$s">%4$s</a>推广用户记录。','um'), $aall,$type,$link,$otype) . '</li>';
		$item_html .= '<div class="site-orders">
			<table width="100%" border="0" cellspacing="0" class="table table-bordered orders-table">
				<thead>
					<tr>
						<th scope="col" style="width:20%;">'.__('用户ID','um').'</th>
						<th scope="col">'.__('注册时间','um').'</th>
						<th scope="col">'.__('消费金额','um').'</th>
						<th scope="col">'.__('推广提成','um').'</th>
					</tr>
				</thead>
				<tbody class="the-list">';
				if($aLog){$total_value=0;foreach($aLog as $Log){
					$total_value += $Log->total_cost;
					$item_html .= '
                    <tr>
						<td>'.$Log->user_id.'</td>
						<td>'.date( 'Y年m月d日', strtotime( get_userdata($Log->user_id)->user_registered ) ).'</td>
						<td>'.$Log->total_cost.'</td>
						<td>'.$Log->total_rewards.'</td>
					</tr>';
				}
					$item_html .= '
					<tr>
						<td colspan=2 style="text-align:right;font-weight:bold;color:#000;">合计：</td>
						<td>'.sprintf('%0.2f',$total_value).'</td>
						<td>'.sprintf('%0.2f',$sum_rewards).'</td>
					</tr>';
					if($currency=='cash'):
					$item_html .= '
					<tr>
						<td colspan=3 style="text-align:right;font-weight:bold;color:#000;">提现中：</td>
						<td>-'.sprintf('%0.2f',$withdrawing).'</td>
					</tr>';
					$item_html .= '
					<tr>
						<td colspan=3 style="text-align:right;font-weight:bold;color:#000;">已提现：</td>
						<td>-'.sprintf('%0.2f',$withdrawed).'</td>
					</tr>';
					$item_html .= '
					<tr>
						<td colspan=3 style="text-align:right;font-weight:bold;color:#000;">推广余额：</td>
						<td>'.sprintf('%0.2f',$left).'</td>
						<input type="hidden" value="'.sprintf('%0.2f',$left).'" name="balance" id="balance">
					</tr>';
					endif;
				}else{$item_html .= '<tr><td colspan=4 style="text-align:left;">没有推广记录</td></tr>';}
				$item_html .= '</tbody>
			</table>';
			if($currency=='cash'){
				$item_html .= '<div id="withdraw-records"><p style="color:#000;">提现记录</p>';
				if(current_user_can('edit_users'))$th='<th scope="col">'.__("操作","um").'</th>';else $th='';
				$item_html .= '<table width="100%" border="0" cellspacing="0" class="table table-bordered orders-table">
				<thead>
					<tr>
						<th scope="col">'.__('申请时间','um').'</th>
						<th scope="col">'.__('金额','um').'</th>
						<th scope="col">'.__('余额','um').'</th>
						<th scope="col">'.__('状态','um').'</th>'.$th.'
					</tr>
				</thead>
				<tbody class="the-list">';
				$records = um_withdraw_records($curauth->ID);
				if($records){
					foreach($records as $record){
					$item_html .= '
                    <tr>
						<td>'.$record->time.'</td>
						<td>'.$record->money.'</td>
						<td>'.$record->balance.'</td>
						<td>'.um_withdraw_status_output($record->status,$record->id).'</td>
					</tr>';
					}

				}else{
					$item_html .= '<tr><td colspan=4 style="text-align:left;">没有提现记录</td></tr>';
				}

				$item_html .= '</tbody></table></div>';
			}
			if($currency=='cash'&&$curauth->ID==$current_user->ID){
				$item_html .= '<div id="withdraw">';
				if($left<um_get_setting('aff_discharge_lowest',100)){
					$item_html .= '<p>'.$curauth->display_name.'，你当前账户推广余额低于'.um_get_setting('aff_discharge_lowest',100).'元最低提现值，暂不能申请提现</p>';
				}else{
					$item_html .= '<div class="form-inline"><div class="form-group"><div class="input-group"><div class="input-group-addon">提现数额</div><input class="form-control" type="text" name="withdrawNum" id="withdrawNum" value="'.sprintf('%0.2f',$left).'" aria-required="true" required=""></div></div><button class="btn btn-default" type="submit" id="withdrawSubmit" style="margin-left:10px;">申请提现</button></div>';
				}
				$item_html .= '</div>';
			}
		$item_html .= '</div>';
		if($pages>1) $item_html .= '<li class="tip">'.sprintf(__('第 %1$s 页，共 %2$s 页，每页显示 %3$s 条。','um'),$paged, $pages, $number).'</li>';
	echo $item_html.'</div>';
	echo um_pager($paged, $pages);

?>
<?php }
}
		?>

		<!-- End Tab-affiliate -->
						</div>
					</div>
					<!-- Sidebar -->
					<?php if(!isset($_GET['action'])||($_GET['action']!='edit'&&$_GET['action']!='new')) { ?>
					<div id="rb">
						<div id="rb-inner">
						<!-- Follow widget -->
							<div class="um-widget follow-widget">
								<div class="widget-header clx">
									<?php echo um_get_avatar( $curauth->ID , '40' , um_get_avatar_type($curauth->ID) ); ?>
									<h4>关注/粉丝</h4>
									<p class="widget-p">TA的关注和粉丝</p>
								</div>
								<div class="widget-body">
									<div class="item">
										<fieldset class="fieldset clx">
											<legend class="legend">关注<span>(<?php echo um_following_count($curauth->ID); ?>)</span></legend>
										</fieldset>
										<ul class="flowlist following-list clx">
											<?php echo um_follow_list($curauth->ID,20,'following'); ?>
										</ul>
									</div>
									<div class="item">
										<fieldset class="fieldset">
											<legend class="legend">粉丝<span>(<?php echo um_follower_count($curauth->ID); ?>)</span></legend>
										</fieldset>
										<ul class="flowlist followers-list clx">
											<?php echo um_follow_list($curauth->ID,20); ?>
										</ul>
									</div>
								</div>
							</div>
						<!-- Manage menu widget -->
							<div class="um-widget follow-widget">
								<div class="widget-header clx">
									<div class="icon"><i class="fa fa-globe"></i></div>
									<h4>名片</h4>
									<p class="widget-p">TA的个人信息</p>
								</div>
								<div class="widget-body">
									<div class="user-time">
										<?php $days_num = round(( strtotime(date('Y-m-d')) - strtotime( $user_info->user_registered ) ) /3600/24); $days_num = $days_num>1?$days_num:1;echo '<p><span>'.__('注册 :','um').'</span>'.date( 'Y年m月d日', strtotime( $user_info->user_registered ) ).' ( '.$days_num.'天 )</p>';
				 						if($current_user&&$current_user->ID==$curauth->ID&&!empty($user_info->um_latest_ip_before)) {echo '<p><span>'.__('上次登录 :','um').'</span>'.date( 'Y年m月d日 H时i分s秒', strtotime( $user_info->um_latest_login_before ) ).'</p>';/*.$user_info->um_latest_ip_before.' '.convertip($user_info->um_latest_ip_before).'<span>'.'&nbsp;IP&nbsp;'.'</span>';*/}else{
				 						if($user_info->um_latest_login) echo '<p><span>'.__('最后登录 :','um').'</span>'.date( 'Y年m月d日 H时i分s秒', strtotime( $user_info->um_latest_login ) ).'</p>';}
				 						?>
									</div>
									<div class="item">
										<fieldset class="fieldset">
											<legend class="legend">网络<span></span></legend>
										</fieldset>
										<ul class="sociallist clx">
											<?php if(!empty($user_info->user_url)){ ?>
											<span><a class="as-img as-home" href="<?php echo $user_info->user_url; ?>" title="<?php _e('用户主页','um'); ?>"><i class="fa fa-home"></i></a></span>
											<?php } ?>
											<?php if(!empty($user_info->um_donate)){ ?>
											<span><a class="as-img as-donate" href="#" title="<?php _e('打赏TA','tinection'); ?>"><i class="fa fa-coffee"></i>
												<div id="as-donate-qr" class="as-qr"><img src="<?php echo $user_info->um_donate; ?>" title="<?php _e('手机支付宝扫一扫打赏TA','um'); ?>" /><div>手机支付宝扫一扫打赏TA</div></div></a><?php echo um_alipay_post_gather($user_info->um_alipay_email,10,1); ?></span>
											<?php } ?>
											<?php if(!empty($user_info->um_sina_weibo)){ ?>
											<span><a class="as-img as-sinawb" href="http://weibo.com/<?php echo $user_info->um_sina_weibo; ?>" title="<?php _e('微博','um'); ?>"><i class="fa fa-weibo"></i></a></span>
											<?php } ?>
											<?php if(!empty($user_info->um_qq_weibo)){ ?>
											<span><a class="as-img as-qqwb" href="http://t.qq.com/<?php echo $user_info->um_qq_weibo; ?>" title="<?php _e('腾讯微博','um'); ?>"><i class="fa fa-tencent-weibo"></i></a></span>
											<?php } ?>
											<?php if(!empty($user_info->um_twitter)){ ?>
											<span><a class="as-img as-twitter" href="https://twitter.com/<?php echo $user_info->um_twitter; ?>" title="Twitter"><i class="fa fa-twitter"></i></a></span>
											<?php } ?>
											<?php if(!empty($user_info->um_googleplus)){ ?>
											<span><a class="as-img as-googleplus" href="<?php echo $user_info->um_googleplus; ?>" title="Google+"><i class="fa fa-google-plus"></i></a></span>
											<?php } ?>
											<?php if(!empty($user_info->um_weixin)){ ?>
											<span><a class="as-img as-weixin" href="#" id="as-weixin-a" title="<?php _e('微信','tinection'); ?>"><i class="fa fa-weixin"></i>
												<div id="as-weixin-qr" class="as-qr"><img src="<?php echo $user_info->um_weixin; ?>" title="<?php _e('微信扫描二维码加我为好友并交谈','um'); ?>" /><div>微信扫描二维码加我为好友并交谈</div></div></a></span>		
											<?php } ?>
											<?php if(!empty($user_info->um_qq)){ ?>
											<span><a class="as-img as-qq" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $user_info->um_qq; ?>&site=qq&menu=yes" title="<?php _e('QQ交谈','um'); ?>"><i class="fa fa-qq"></i></a></span>
											<?php } ?>
											<span><a class="as-img as-email" href="mailto:<?php echo $user_info->user_email; ?>" title="<?php _e('给我写信','um'); ?>"><i class="fa fa-envelope"></i></a></span>
										</ul>
									</div>
								</div>
							</div>
						<!-- Manage menu widget -->
							<div class="um-widget manage-widget">
								<div class="widget-header clx">
									<div class="icon" style="font-size:32px;padding-top:3px;"><i class="fa fa-gears"></i></div>
									<h4>管理菜单</h4>
									<p class="widget-p">站内功能管理</p>
								</div>
								<div class="widget-body form-inline">
									<?php echo um_user_manage_widget(); ?>
								</div>
							</div>

						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	</div>
</div>

<!-- Footer -->
<?php get_footer(); ?>














