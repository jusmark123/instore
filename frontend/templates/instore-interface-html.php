<?php if( ! defined( 'ABSPATH' ) ) exit; //exit if accessed directly 
global $woocommerce, $wpdb, $wp_filter;

define( 'WOOCOMMERCE_CHECKOUT',true );			
$woocommerce->cart->calculate_totals();			
$subtotal = $woocommerce->cart->subtotal_ex_tax;
$tax = $woocommerce->cart->tax_total;
$total = $woocommerce->cart->total;
$payment_methods = $woocommerce->payment_gateways->get_available_payment_gateways();
$class = $woocommerce->cart->get_cart_contents_count() ? 'active' : '';

if( $order_id = $woocommerce->session->order_awaiting_payment ) {
	$tendered = get_post_meta( $order_id, 'payment_tendered' ) ?  get_post_meta( $order_id, 'payment_tendered', true ) : '0';
} else {
	$tendered = 0;
}	
//create display header
get_header();
?>
<div class="order_detail">
  <div class="instore_logo"> <img src="<?php echo esc_attr( Instore()->plugin_url() . '/images/instore-logo.png'); ?>" /> </div>
  <div class="item_display">
    <table class="ins_item_display scroll">
      <thead>
      <th class="ins_product_id"> <?php _e( 'Product Id', 'instore');?></th>
        <th class="ins_product_title"><?php _e( 'Product', 'instore'); ?></th>
        <th class="ins_product_price"><?php _e( 'Unit Price', 'instore' ); ?></th>
        <th class="ins_product_quanity"><?php _e( 'Quantity', 'instore' ); ?></th>
        <th class="ins_product_subtotal"><?php _e( 'Total', 'instore'); ?></th>
          </thead>
      <tbody class="order_items_table">
        <?php 
				load_cart_contents();
							
			?>
      </tbody>
    </table>
  </div>
  <div class="totals_display scroll">
    <table class="totals" cols="5">
      <tr>
        <td class="ins_subtotal_label total_display_label" colspan="2">SUB TOTAL</td>
        <td class="ins_subtotal total_display" colspan="3"><?php echo  wc_price($subtotal); ?></td>
      </tr>
      <tr>
        <td class="ins_tax_total_label total_display_label" colspan="2"><?php echo esc_html( strtoupper($woocommerce->countries->tax_or_vat() ) ); ?></td>
        <td class="ins_tax_total total_display" colspan="2"><?php echo wc_price( $tax ); ?></td>
      </tr>
      <?php 		 
		 if( $woocommerce->cart->applied_coupons ) { 
		 	$discounts = '<a href="#" id="get_disc">' . __( 'DISCOUNTS', 'instore' ) .'</a>';
		 } else {
			$discounts = __( 'DISCOUNTS', 'instore' );
		 }
	  ?>
      <tr>
        <td class="ins_disc_label total_display_label" colspan="2"><?php echo $discounts; ?></td>
        <td class="ins_disc_total total_display" colspan="2">(<?php echo wc_price( $woocommerce->cart->discount_cart ); ?>)</td>
      </tr>
      <tr>
        <td class="ins_grand_total_label total_display_label" colspan="2">TOTAL</td>
        <td class="ins_grand_total total_display" colspan="2"><?php echo wc_price( $total ); ?></td>
      </tr>
      <tr class="amount_due_display" style="font-size:1.6em; display:none;">
      	<td class="tendered amount_due_label" colspan="2">Amount Due</td>
        <td class="tendered amount_due" colspan="2">$189.98</td>
    </table>
  </div>
  <div class="payment_tendered scroll">
    <table class="payments_detail" cols="5">
      <tr>
        <td class="tendered tendered_label" colspan="2">Amount Tendered</td>
        <td class="tendered_amount tendered" colspan="2"><?php echo wc_price( $tendered ); ?></td>
      </tr>
    </table>
  </div>
</div>
<div class="product_search">
  <table>
    <tr>
      <td class="product_search_label"><label for="order_search">Search Past Orders:</label></td>
      <td class="order_lookup"><input type="text" class="search" id="order_search" placeholder="Enter Order Number"  /></td>
      <td class="submit_btn"><input type="button" id="order_look_up" class="btn_submit" value="Search"  /></td>
      <td class="product_search_label"><label for="product_search">Product Search:</label></td>
      <td class="search_bar"><select data-placeholder="Enter SKU#, Title or Category" class="search chosen_select" id="product_search"  style="width:20em; text-align:left;">
      	<?php $products   = ins_get_products(); 
			  $categories = ins_get_categories(); ?>
			<optgroup label="Categories">	
      	<?php foreach( $categories as $category ) {	?>
        	
          		<option value="<?php echo esc_attr( $category->term_id ); ?>">Category - <?php echo esc_html( $category->name ); ?></option>
        <?php } ?>
			</optgroup>
            <optgroup label="Products">
        <?php foreach( $products as $product ) { ?>        	
          		<option value="<?php echo esc_attr( $product->id ); ?>">Product - <?php echo esc_html(  $product->get_title() ); ?></option>
		<?php } ?>	
        	</optgroup>
            <optgroup label="SKU">
        <?php foreach( $products as $product ) { 
				if( $product->get_sku() ) { ?>
          		<option value="<?php echo esc_attr( $product->id ); ?>">SKU - <?php echo esc_html(  $product->get_sku() ); ?></option>
		<?php 	}
			} ?>
            </optgroup>
        </select></td>
    </tr>
  </table>
</div>
<div class="product_explorer scroll">
  <?php ins_refresh_products(); ?>
</div>
<div class="payment_options">
  <ul class="payments">
    <?php
		foreach( $payment_methods as $method ) { 
		?>
    <li class="payment_gateway function_button <?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $method->id ); ?>" title="<?php echo esc_attr( $method->title ); ?>"> <span><?php echo esc_html( $method->title ); ?></span> </li>
    <?php 
		} ?>
  </ul>
  <ul class="quick_cash payments">
    <li class="ins_quick_cash payment_button <?php echo esc_attr( $class ); ?>" value="5" title="$5 Quick Cash Payment"> <span>$5</span> </li>
    <li class="ins_quick_cash payment_button <?php echo esc_attr( $class ); ?>" value="10" title="$10 Quick Cash Payment"> <span>$10</span> </li>
    <li class="ins_quick_cash payment_button <?php echo esc_attr( $class ); ?>" value="20" title="$20 Quick Cash Payment"> <span>$20</span> </li>
    <li class="ins_quick_cash payment_button <?php echo esc_attr( $class ); ?>" value="100" title="$100 Quick Cash Payment"> <span>$100</span> </li>
    <li class="coupon_button  function_button" id="add_coupon" title="Add Coupon"><span>Add Coupon</span></li>    
  </ul>  
  <li class="print_button function_button" id="print_order" title="Print Order Detail" style="width: 97.2%;"><span>Print</span></li>
  <li class="order_reset function_button" id="order_reset" title="Clear Order"><span>Clear Order</span></li>
  <li class="lock_console function_button" id="lock_console" title="Lock Instore POS"><span>Lock Console</span></li>
</div>
<div class="product_details">
  <div class="instruction"><span class="placeholder">Click a product button above to display product details here. Double-click product button to add product to order.</span></div>
</div>
<div class="clear"></div>
<?php get_footer();