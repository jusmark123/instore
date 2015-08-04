<?php

if( ! defined( 'ASBPATH') ) exit(); //exit if accessed directly ?>

<div class="order-detail">
<div class="instore_logo"> <img src="<?php echo esc_attr( Instore()->plugin_url() . '/images/instore-logo.png'); ?>" /> </div>
<div class="item-display">
  <table class="ins-item-display scroll">
    <thead>
    <th class="ins-product-id"> <?php _e( 'Product Id', 'instore');?></th>
      <th class="ins_product_title"><?php _e( 'Product', 'instore'); ?></th>
      <th class="ins_product_price"><?php _e( 'Unit Price', 'instore' ); ?></th>
      <th class="ins_product_quanity"><?php _e( 'Quantity', 'instore' ); ?></th>
      <th class="ins_product_subtotal"><?php _e( 'Total', 'instore'); ?></th>
        </thead>
    <tbody id="order-items-table">
      <?php 
				do_action( 'instore_get_order_items' );
							
			?>
    </tbody>
  </table>
</div>
