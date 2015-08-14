<?php
/**
 * Main Template of Ucenter & Market WordPress Plugin
 *
 * @package   Ucenter & Market
 * @version   1.0
 * @date      2015.4.9
 * @author    Zhiyan <chinash2010@gmail.com>
 * @site      Zhiyanblog <www.zhiyanblog.com>
 * @copyright Copyright (c) 2015-2015, Zhiyan
 * @license   http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link      http://www.zhiyanblog.com/wordpress-plugin-ucenter-and-market.html
**/

?>
<?php
function um_order_popup(){
    if(is_single()&&get_post_type()=='post'){
        $currency = get_post_meta(get_the_ID(),'pay_currency',true);
        $discount_arr = product_smallest_price(get_the_ID());
?>
            <div id="order" class="popupbox">
                <form id="alipayment" name="alipayment" action="<?php echo UM_URI.'alipay/alipayapi.php'; ?>" method="post">
                    <div id="pay">
                        <div class="part-order">
                            <ul>
                                <h3><?php _e('订单信息','um'); ?><span><?php _e('（价格单位：','um'); ?><?php if($currency==1)echo '元'; else echo '积分';?><?php _e('）','um'); ?></span></h3>
                                <input type="hidden" name="order_nonce" value="<?php echo wp_create_nonce( 'order-nonce' );?>" >
                                <input type = "hidden" id="product_id" name="product_id" readonly="" value="<?php echo get_the_ID(); ?>">
                                <input type = "hidden" id="order_id" name="order_id" readonly="" value="0">
                                <li><label for="order_name"><small>*</small><?php _e('商品名称：','um'); ?></label><input id="order_name" name="order_name" readonly="" value="<?php the_title();?>"></li>
                                <li><label for="order_price"><small>*</small><?php _e('商品单价：','um'); ?></label><input id="order_price" readonly="" value="<?php echo $discount_arr[5]; ?>"></li>
                                <li><label for="order_quantity"><small>*</small><?php _e('商品数量：','um'); ?></label><input id="order_quantity" name="order_quantity" value="1" maxlength="8" title="<?php _e('请输入购买量','um'); ?>" onkeydown="if(event.keyCode==13)return false;"></li>
                            </ul>
                            <ul>
                                <h3><?php _e('收货信息','um'); ?><span><?php _e('商店','um'); ?><?php _e('（虚拟商品除邮箱外可不填）','um'); ?></span></h3>
                                <?php $autofill = get_user_autofill_info();?>
                                <li><label for="receive_name"><?php _e('收货姓名：','um'); ?></label><input id="receive_name" name="order_receive_name" value="<?php echo $autofill['user_name']; ?>" onkeydown="if(event.keyCode==13)return false;"></li>
                                <li><label for="receive_address"><?php _e('收货地址：','um'); ?></label><input id="receive_address" name="order_receive_address" value="<?php echo isset($autofill['user_address'])?$autofill['user_address']:''; ?>" onkeydown="if(event.keyCode==13)return false;"></li>
                                <li><label for="receive_zip"><?php _e('收货邮编：','um'); ?></label><input id="receive_zip" name="order_receive_zip" value="<?php echo isset($autofill['user_zip'])?$autofill['user_zip']:''; ?>" onkeydown="if(event.keyCode==13)return false;"></li>
                                <li><label for="receive_email"><?php _e('用户邮箱：','um'); ?></label><input id="receive_email" name="order_receive_email" value="<?php echo $autofill['user_email']; ?>" onkeydown="if(event.keyCode==13)return false;"></li>
                                <li><label for="receive_phone"><?php _e('电话号码：','um'); ?></label><input id="receive_phone" name="order_receive_phone" value="<?php echo isset($autofill['user_phone'])?$autofill['user_phone']:''; ?>" onkeydown="if(event.keyCode==13)return false;"></li>
                                <li><label for="receive_mobile"><?php _e('手机号码：','um'); ?></label><input id="receive_mobile" name="order_receive_mobile" value="<?php echo isset($autofill['user_cellphone'])?$autofill['user_cellphone']:''; ?>" onkeydown="if(event.keyCode==13)return false;"></li>
                                <li><label for="body"><?php _e('留言备注：','um'); ?></label><input name="order_body" value="" onkeydown="if(event.keyCode==13)return false;"></li>
                            </ul>
                        </div>
                        <div class="checkout">
                        <?php if($currency==1&&get_post_meta(get_the_ID(),'product_coupon_code_support',true)==1){ ?>
                            <div id="coupon">
                                <input id="coupon_code" value="" onkeydown="if(event.keyCode==13)return false;">
                                <span id="coupon_code_apply"><?php _e('使用优惠码','um'); ?></span>
                            </div>
                        <?php } ?>
                            <button id="pay-submit" type="submit"><?php _e('立即付款','um'); ?></button>
                            <div id="total-price"><?php _e('总金额：','um'); ?><strong><?php if($currency==1) echo '￥'; else echo '<i class="fa fa-gift"></i>';?>1.00</strong><?php if($currency==1)echo ' 元'; else echo ' 积分';?></div>
                        </div>
                        <div>
                        </div>
                        <a class="popup-close"><i class="fa fa-times"></i></a>
                    </div>
                </form>
            </div>
<?php
    }
}
add_action('wp_footer','um_order_popup');
?>