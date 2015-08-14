<?php
/*
	Ucenter and Market Widget
	
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
	
	Copyright: (c) 2015 知言博客 - http://www.zhiyanblog.com
	
		@package Ucenter and Market
		@version 1.0
*/
?>
<?php
class umucenter extends WP_Widget {
/*  Widget
/* ------------------------------------ */
	function __construct(){
		parent::__construct(false,'Um-用户中心',array( 'description' => '用户中心，展示当前登录用户的管理链接以及推广链接等' ,'classname' => 'widget_umucenter form-inline'));
	}

	function widget($args,$instance){
		extract($args);
	?>
		<?php echo $before_widget; ?>
        <?php if($instance['title'])echo $before_title.$instance['title']. $after_title; ?>
		<?php echo um_user_profile_widget(); ?>		
		<?php echo $after_widget; ?>
	<?php }

	function update($new_instance,$old_instance){
		return $new_instance;
	}

	function form($instance){
		$title = esc_attr($instance['title']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('标题：','um'); ?><input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
	<?php
	}
}
add_action('widgets_init',create_function('', 'return register_widget("umucenter");'));?>