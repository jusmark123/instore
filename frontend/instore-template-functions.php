<?php 
if( ! defined( "ABSPATH" ) ) exit; //Exit is accessed directly

add_filter( 'woocommerce_cart_needs_shipping', 'ins_adjust_shipping' );

function ins_adjust_shipping( $needs_shipping ) {
	
	$needs_shipping = false;
	
	return $needs_shipping;
}

function instore_load_template( $template ) {
	
	$file = Instore()->plugin_path() . 'frontend/templates/' . $template . '.php';
	
	include( $file );	
}

function load_cart_contents() { 
	global $woocommerce;
	
	$cart = $woocommerce->cart->cart_contents;

	if( sizeof( $cart > 0 ) ) {
	
	foreach( $cart as $cart_item_key => $cart_item ) {
		$woocommerce->cart->calculate_totals();
		$product_id = $cart_item['product_id'];
		$product 	= $cart_item['data'];
		$coupons	= $woocommerce->cart->applied_coupons; 
		$price		= $product->get_price();
		$quantity	= $cart_item['quantity'];
		
		$subtotal = $price * $quantity;
		
		if( $coupons ) {
			foreach( $coupons as $key => $coupon ) {
				$coupon = new WC_Coupon( $coupon );
				if( $coupon->is_valid_for_product( $product ) ) {
					$discount = $coupon->get_discount_amount( $price, $cart_item );
					$subtotal = ( $price * $quantity ) - $discount;
				}
			}
		}
	?>
<tr class="selectable ins_order_item" id="<?php echo esc_attr( $cart_item_key ); ?>">
  <td class="ins_product_id"><?php echo esc_html( $product_id ); ?></td>
  <td class="ins_product_title"><?php echo $product->get_title(); ?></td>
  <td class="ins_product_price"><?php echo wc_price( $price ); ?></td>
  <td class="ins_product_quantity"><?php echo $quantity; ?></td>
  <td class="ins_product_subtotal"><?php echo wc_price( $subtotal ); ?></td>
  <?php 
 	$coupons = $woocommerce->cart->applied_coupons;
	
  	if(	$coupons ) { 
		foreach( $coupons as $coupon ) {
			$coupon = new WC_Coupon( $coupon );
		
   			if( $coupon->is_valid_for_product( $cart_item['data'] ) ) { ?>
<tr class="ins_item_discounts">
  <td style="text-align:center;"><?php echo __( 'Discount', 'instore'); ?></td>
  <td class="disc_code" style="text-align:center;"><?php echo esc_html( $coupon->code ); ?></td>
  <td class="regular_price" style="text-align:center;"> Reg price <?php echo wc_price( $product->get_price() ); ?></td>
  <td class="disc_amount" style="text-align:right;">- <?php echo wc_price( $discount ); ?></td>
</tr>
<?php 		}
		}
	}
  ?>
</tr>
<?php
		}
	}
}

function ins_refresh_products() {
	global $wpdb, $woocommerce;
	
	$products = ins_get_products();

	?>
	
	    <ul class="product_views">
    <?php     
	foreach( $products as $product ) { ?>
	

		<li class="instore_button instore_product_btn" id="<?php echo esc_attr( $product->id ); ?>" title="<?php echo esc_html( $product->get_title() ); ?>">
    
	<?php 
		if( $product->is_on_sale() ) {
	?>
    		<span class="sale">SALE</span>
    <?php 
	  	} 
	?>
    		<span class="product_title"><?php echo $product->post->post_title; ?></span>
        </li>
    <?php
    }
	?>
    </ul>
    <?php
}

function ins_get_products() {
	global $woocommerce, $wpdb;
    
    $product_ids = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", 'product' ) );
					
	foreach( $product_ids as $id ) { 
		$product = get_product( $id->ID );
		$products[] = $product;
	}
	
	return $products;
}

function ins_get_categories() {
	$args = array( 
		'taxonomy'	 	=> 'product_cat',
		'orderby'	 	=> 'name',
		'show_count' 	=> 1,
			'pad_counts' 	=> 1,
			'hierarchical'	=> 1,
			'title_li'		=> '',
			'hide_empty'	=> 0,
	);
	
	$categories = get_categories( $args );	
	
	return $categories;
}

function instore_filter_gateways() {
	global $woocommerce;
	
	add_filter('woocommerce_available_payment_gateways', 'ins_get_gateways', 10, 1 );
}

add_action( 'woocommerce_review_order_before_payment', 'instore_filter_gateways' );

function ins_get_gateways( $_available_gateways ) {

	unset( $_available_gateways['instore_cash_payment'] );
	
	return $_available_gateways;
}

function ins_login_message( $message ) {
	if( $empty( $message ) ) {
		return '<p class="error">Too many login attempts. Please login to Wordpress and try again.</p>';
	} else {
		return $message;
	}
}