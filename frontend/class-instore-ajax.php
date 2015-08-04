<?php 

 if( ! defined( 'ABSPATH' ) ) exit; //Exit if accessed directly
 
 if( ! class_exists( 'Instore_AJAX' ) ) :
 
 class Instore_AJAX {
	 	
	public $json_return = array();
	
	protected $settings = array();

	public function __construct() {
		
		$settings = get_option('woocommerce_instore_settings');
		
		$ajax_functions = array( 
			'ins_ajax_lock_console',
			'ins_ajax_instore_security',
			'ins_ajax_request_override',
			'ins_ajax_get_order_item',
			'ins_ajax_add_order_item',
			'ins_ajax_update_order_item',
			'ins_ajax_remove_order_item',
			'ins_ajax_refund_order_item',
			'ins_ajax_refresh_cart',
			'ins_ajax_get_discount',
			'ins_ajax_add_discount',
			'ins_ajax_remove_discount',	
			'ins_ajax_lookup_item',
			'ins_ajax_create_order',
			'ins_ajax_void_order',
			'ins_ajax_refund_order',
			'ins_ajax_get_order_details',
			'ins_ajax_add_payment',
			'ins_ajax_remove_payment',
			'ins_ajax_get_item_stock_count',
			'ins_ajax_get_product',
			'ins_ajax_clear_cart',
			'ins_get_coupons_for_product',
			'ins_get_coupons_for_cart',
		);
		
		foreach( $ajax_functions as $ajax_function ) {
			add_action( 'wp_ajax_' . $ajax_function, array( $this, $ajax_function ) );
			add_action( 'wp_ajax_nopriv_' . $ajax_function, array( $this, $ajax_function ) );
		}
	}
	
	public function ins_ajax_lock_console() {
		global $woocommerce;
		
		//lock instore console
		unset( $woocommerce->session->instore_login );

		$this->json_return = array(
			'success' => true,
			'call' 	  => 'instore_redirect',
			'url'	  => site_url('?page_id=' . ins_set_instore_page_id() ),
		);
		
		echo json_encode( $this->json_return );
		die();
		
	}
	
	public function ins_ajax_instore_security() {
		global $current_user, $woocommerce;
		
		$url = site_url('?page_id=' . ins_set_instore_page_id() );
						
		//verify user capability and user login data
		if( current_user_can( 'use_instore' ) && get_user_meta( $current_user->ID, 'instore_login_pin', true ) == $_POST['pin'] ) {
			
			//set login session variable
			$woocommerce->session->set( 'instore_login', sanitize_text_field( $_POST['pin'] ) );
			$success = true;
			$call = 'instore_redirect';
			$status_message = '';
		} else {
			$success = false;
			$call = 'login_failed';
			$status_message = 'Invalid login pin. Please try again'; 
		}
		
		$this->json_return = array( 
			'success' 		 => $success,
			'call'	 		 => $call,
			'status_message' => $status_message,
			'url'	 		 => $url,
			'html'			 => isset( $template ) ? $template : '',
			'options'		 => isset( $options ) ? $options : '',
		);		
		
		echo json_encode( $this->json_return );
		die();
	}
	
	public function ins_ajax_get_order_item() {
		global $woocommerce;

		//get post variables
		$cart_id = $_POST['cart_id'];
		
		//initialize total discount
		$total_discount = 0;
		
		//get cart item detail	
		$cart_item = $woocommerce->cart->cart_contents[$cart_id];
		
		//get line item product data
		$product = get_product( $cart_item['product_id'] );

		//set data for display 	
		$this->json_return = array(		
			'success'	  	=> true,
			'call'		  	=> 'show_dialog',
			'dialog_method'	=> 'get_line_item',
			'html'			=> $this->ins_get_template( 'instore-edit-item-display-html' ),
			'item'			=> array(
				'cart_id' 		=> $cart_id,
				'product_id'	=> $cart_item['product_id'],
				'title'		  	=> $product->get_title(),
				'sku'	 		=> $product->get_sku(),
				'description'	=> $cart_item['data']->post->post_excerpt,
				'regular_price'	=> $product->get_regular_price(),
				'on_sale'		=> $product->is_on_sale() ? 'Yes' : 'No',
				'sale_price'	=> $product->is_on_sale() ? $product->get_sale_price() : 'N/A',	
				'price'	   	 	=> $product->get_price(),
				'quantity' 		=> $cart_item['quantity'],
				'subtotal'		=> $woocommerce->cart->get_product_subtotal( $product, $cart_item['quantity'] ),
			),
			'options'  		=> array( 
				'modal'    => true,
				'autoOpen' => true,
				'width'	   => 800,
				'title'	   => 'Edit Order Item',
			)
		);
		
		if( $woocommerce->cart->coupons_enabled() && $coupons = $woocommerce->cart->applied_coupons ) {
			foreach( $coupons as $key => $coupons ) {
				$coupon = new WC_Coupon( $coupon );
				
				if( $coupon->is_valid_for_product( $product ) ) {
					$applied_coupons[$coupon->code] = array(
						'amount' => $coupon->amount,
						'type'	 => $coupon->type,
						'total'  => get_discounted_amount( $product->get_price(), $cart_item, $coupon->individual_use ),
					);
				}
			}
		}
		
		if( isset( $applied_coupons ) ) {
			$this->json_return['html'] = $this->ins_get_template( 'instore-discount-item-display-html' );
			$this->json_return['applied_coupons'] = $applied_coupons;	
		}
		
		echo json_encode( $this->json_return );
		die();
	}
	
	public function ins_ajax_add_order_item() {
		global $woocommerce;
		
		if( isset( $_POST['product_id'] ) ) {
			$product_id = $_POST['product_id'];
			$product = get_product( $product_id );
			$quantity = isset( $_POST['quantity'] ) ? $_POST['quantity'] : 1;
			$valid = $product->has_enough_stock( $quantity );
			
			if( $valid ) {
				$woocommerce->cart->add_to_cart( $product_id, $quantity );
				
				$success    = true;
				$call       = 'refresh_display';
				$message    = 'Item added to cart';
				$product_id = $product_id;
				$item_count = $woocommerce->cart->get_cart_contents_count();
				
				$this->ins_ajax_refresh_cart();
			} else {
				$success = false;
		 		$message = 'Product not added to cart. Stock count too low.';
			}
		} else {
			$success = false;
			$message = 'Product not added. No product ID found. Please try again.';
		}
		
		$this->json_return = array(
			'success' 		 => $success,
			'call' 	  		 => $call,
			'status_message' => $message,
			'product_id'	 => $product_id,
			'item_count'     => isset( $item_count ) ? $item_count : '',
		);
		
		echo json_encode( $this->json_return );
		die();
	}
	
	public function ins_ajax_update_order_item() {
		global $woocommerce;	
		
		if( isset( $_POST['quantity'] ) ) {
		
			$quantity = sanitize_text_field( $_POST['quantity'] );
		
			$cart_id = $_POST['cart_id'];
		
			$product = get_product( $_POST['product_id'] );
 		
			$cart_item = $woocommerce->cart->cart_contents[$cart_id];
			
			//Check if quantity has been changed
			if( $quantity != $cart_item['quantity'] ) { 	
				$subtotal  = $woocommerce->cart->get_product_subtotal( $product, $_POST['quantity'] );
			}
			
			$this->json_return = array(
				'success'  => true,
				'call' 	   => 'update_item_display',
				'subtotal' => $subtotal,
			);
			
			echo json_encode( $this->json_return );
			die();
		}
	}
	
	public function ins_ajax_remove_order_item() {
		global $woocommerce;
		
		$cart_id = $_POST['cart_id'];
		
		$woocommerce->cart->set_quantity( $cart_id, 0 );

		$this->ins_ajax_refresh_cart();	
	}
	
	public function update_order() {
		global $woocommerce;
		
		$cart_item = isset( $_POST['cart_id'] ) ? $woocommerce->cart->cart_contents[$_POST['cart_id']] : die();
		
		$product = isset( $_POST['product_id'] ) ? get_product( $_POST['product_id'] ) : die();
	}
	
	public function ins_ajax_refresh_cart() {
		global $woocommerce;
		
		$cart_id = isset( $_POST['cart_id'] ) ? $_POST['cart_id'] : '';
		
		if( isset( $woocommerce->cart->cart_contents[$cart_id] ) ){
		
			$product = isset( $_POST['product_id'] ) ? get_product( $_POST['product_id'] ) : '';
			
			$cart_item = $woocommerce->cart->cart_contents[$cart_id];
		
			if( isset( $_POST['quantity'] ) ) {
				if(	$cart_item['quantity'] != (int)$_POST['quantity'] ) {
					$woocommerce->cart->set_quantity( $_POST['cart_id'], $_POST['quantity'], false );
				}
			}
		}
		
		//get item detail html
		ob_start();
		
		load_cart_contents();
				
		$template = ob_get_clean();
		
		//Define WooCommerce cart for grandtotal calculation
		define( 'WOOCOMMERCE_CART',true );
		
		//Calculate totals for display
		$woocommerce->cart->calculate_totals();
		
		$this->json_return['errors'] = $woocommerce->cart->check_cart_coupons(); 

		//Get cart totals for display
		$subtotal 	= $woocommerce->cart->subtotal_ex_tax;
		$discount 	= $woocommerce->cart->get_cart_discount_total();
		$total_tax 	= $woocommerce->cart->tax_total;		
		$total 		= $woocommerce->cart->total;
		if( $order_id = $woocommerce->session->order_awaiting_payment ) {
			$tendered = get_post_meta( $order_id, 'payment_tendered' ) ?  get_post_meta( $order_id, 'payment_tendered', true ) : '0';
		} else {
			$tendered = 0;
		}
		
		//Load data for AJAX 	
		$this->json_return = array(
			'success'   	 		=> true,
			'call' 			 		=> 'refresh_display',
			'html'			 		=> $template,
			'ins_subtotal'	 		=> wc_price( $subtotal ),
			'ins_disc_total' 		=> '(' . wc_price( $discount ) . ')',
			'ins_tax_total_label'	=> strtoupper( $woocommerce->countries->tax_or_vat() ),
			'ins_tax_total'	 		=> wc_price( $total_tax ),
			'ins_grand_total'		=> wc_price( $total ),
			'tendered_amount'		=> wc_price( $tendered ),
		);

		$products = ins_get_products();
		
		foreach( $products as $product ) {
			$buttons = '';
			ob_start();
			
			ins_refresh_products( $product );
		
			$buttons .= ob_get_clean();
			
			$this->json_return['buttons'] = $buttons;
		}
		//Send data to JS
		echo json_encode( $this->json_return );
		die();
	}
	
	public function ins_ajax_get_discount() {
		global $woocommerce, $current_user;
		
		$total_discount = 0.00;
		
		if( isset( $_POST['discount'] ) ) {
			if( is_array( $_POST['discount'] ) ) {
				$discount = $_POST['discount'];
				$disc_code = $discount['disc_reason'] . time();
				$coupon_code = add_filter( 'woocommerce_get_shop_coupon_data', array( $this, 'ins_get_discount_code' ), $disc_code ); 
			} else {
				$coupon_code = $_POST['discount'];
			}
			
			$coupon = new WC_Coupon( $coupon_code );
			
			
			
			$cart_items = $woocommerce->cart->cart_contents;
			
			if( $coupon->is_valid() ) {
				$total_discount += $coupon->get_discount_amount( $woocommerce->cart->total );
				$post = get_post( $coupon->id );
				$coupon->amount = $coupon->type == 'percent' ? $coupon->amount . '%' : wc_price( $coupon->amount );
				$coupon->minimum_amount = isset( $_POST['call'] ) && $_POST['call'] == 'edit' ? $coupon->minimum_amount : wc_price( $coupon->minimum_amount );
				$coupon->expiry_date = is_long($coupon->expiry_date ) ? date( 'm/d/Y', $coupon->expiry_date ) : $coupon->expiry_date;
				$coupon->usage_count = empty( $coupon->usage_count ) ? 0 : $coupon->usage_count;
				
				$this->json_return = array(
					'success'     => true,
					'call'	      => 'update_item_display',
					'coupon'      => $coupon,
					'discount'    => $total_discount,
					'description' => isset( $post ) ? $post->post_excerpt : $discount['disc_reason'] . ' discount created on ' . date( 'm/d/Y h:m:i a' ) . ' by ' . $current_user->user_login,
					
				);
			
				if( isset( $_POST['call'] ) ) {
					$this->json_return['action'] = $_POST['call'];
				}
				
			} else {
			 	$this->json_return = array(
					'success' 		=> false,
					'coupon_error'  => $coupon->error_message,
				);
			}
		} 
					
		echo json_encode( $this->json_return );
		die();
	}
	
	public function ins_ajax_add_discount() {
		global $woocommerce;
		
		if( isset( $_POST['discount'] ) ) {
				
		} else {
			$this->json_return = array(
				'success' => true,
				'call'	=> 'show_dialog',
				'html'  => $this->ins_get_template( 'instore-add-disc-html' ),
				'options'  		=> array( 
					'modal'     => true,
					'autoOpen'  => true,
					'width'	    => 800,
					'maxHeight' => 800, 
					'title'	    => 'Add Coupon/Discount',
				),
			);	
		}
		echo json_encode( $this->json_return );
		die();
	}
	
	public function ins_ajax_remove_discount() {
		global $woocommerce;
		
		$discounts = $_POST['discounts'];
		
		foreach( $discounts as $key => $discount )  {
			
			$woocommerce->cart->remove_coupon( $discount );
			
			if( is_numeric( substr($discount, strrpos( $discount, '_', 1 ) + 1 ) ) ) {
				
				$coupon = new WC_Coupon( $discount );
			
				wp_delete_post( $coupon->id, true ); 
			}
		}
		
		$this->json_return['success'] = true;
		$this->json_return['call'] = 'update_item_display';
		$this->ins_ajax_get_discount();
	}
	
	public function ins_ajax_void_order_item() {
	
	}
	
	public function ins_ajax_return_order_item() {
	
	}
	
	public function ins_ajax_exhange_order_item() {
	
	}
	
	public function ins_ajax_refund_order_item() {
	
	}
	
	public function ins_ajax_lookup_item() {
	
	}
	
	public function ins_ajax_create_order() {
	
	}
	
	public function ins_ajax_void_order() {
	
	}
	
	public function ins_ajax_refund_order() {
	
	}
	
	public function ins_ajax_get_order_details() {
	
	}
	
	public function ins_get_payment_form() {
		global $woocommerce;
		
		define( 'WOOCOMMERCE_CHECKOUT', true );
		
		$template = $this->ins_get_template( 'instore-payment-form-html');
		
		$order_id = isset( $_POST['order_id'] ) ? $_POST['order_id'] : '';
		
		$this->json_return['html'] = $template;
		
		$gateway = $_POST['gateway'];
		
		$gateways = $woocommerce->payment_gateways->get_available_payment_gateways();
		
		$gateway = $gateways[$gateway];
		
		if( $gateway->has_fields() ) {
			
			ob_start();
			
			$gateway->payment_fields();	
			
			$fields = ob_get_clean();
			
			$this->json_return['fields'] = $fields;
		}
		
		$woocommerce->cart->calculate_totals();
		
		$payment_tendered = get_post_meta( $order_id, 'payment_tendered');
		
		$amount_due = isset( $_POST['amount_due'] ) ? (float)$_POST['amount_due'] : $woocommerce->cart->total; 
		
		$this->json_return['success'] = true;
		$this->json_return['call'] 	  = 'process_payment';
		$this->json_return['gateway'] = $gateway;
		$this->json_return['amount_due'] = wc_price( $amount_due );
		
		
		echo json_encode( $this->json_return );
		die(); 
	}
	
	public function ins_add_order_note() {
		
	}
	
	public function ins_ajax_add_payment() {
		global $woocommerce, $current_user;
		
		//define( 'WOOCOMMERCE_CHECKOUT', true );
		
		if( ! isset( $_POST['payment_amount'] ) && isset( $_POST['gateway'] ) ) {
			$this->ins_get_payment_form();
		} else { 
			//get selected payment gateway
			$gateway = $_POST['gateway'];		
		
			$payment_amount = $_POST['payment_amount'];
		
			//retrieve order by id if passed otherwise create new order 
			if( ! isset( $_POST['order_id'] ) ) {
			
				$user = get_user_meta( $current_user->ID,'',false );
				$woocommerce->customer->set_to_base();
				$checkout = $woocommerce->checkout;
				$checkout->must_create_account = false;
				
				$required_data = array(
					'payment_method'  => stripslashes( $gateway ),
					'shipping_method' 			=> 'local_pickup',
					'ship_to_different_address'	=> false,
					'billing_first_name'		=> $user['first_name'][0],
					'billing_last_name'			=> $user['last_name'][0],
					'billing_address_1'			=> $user['billing_address_1'][0],
					'billing_city'				=> $user['billing_city'][0],
					'billing_state'				=> $user['billing_state'][0],
					'billing_postcode'			=> $user['billing_postcode'][0],
					'billing_country'			=> $user['billing_country'][0],
					'billing_email'				=> isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : 'justinwalker@jwalkerdzines.com',
					'billing_phone'				=> $user['billing_phone'][0],
					'shipping_first_name'		=> $user['first_name'][0],
					'shipping_last_name'		=> $user['last_name'][0],
					'shipping_address_1'		=> $user['shipping_address_1'][0],
					'shipping_city'				=> $user['shipping_city'][0],
					'shipping_state'			=> $user['shipping_state'][0],
					'shipping_postcode'			=> $user['shipping_postcode'][0],
					'shipping_country'			=> $user['shipping_country'][0],
				);
			
				foreach( $required_data as $key => $value ) {
					$_POST[$key] = $value;	
				}
			
				$woocommerce->cart->calculate_totals();
			
				$order_id = $woocommerce->checkout->process_checkout();
			}
		}
	}
	
	private function ins_create_order_from_cart() {
		global $woocommerce;
		
		$woocommerce->cart->calculate_totals();
		
		$order = $woocommerce->checkout->create_order();
		
		return $order;
	}
	
	public function ins_ajax_remove_payment() {
		
	}
	
	public function ins_refresh_products( $product ) {
		
		if( $product->managing_stock() ) {
			$class = $product->is_in_stock() ? 'in-stock' : 'not-in-stock' ;
		} else {
			$class = 'in-stock';
		}
		
		$this->json_return['products'][$product->id] = array(
			 'product_title' => $product->get_title,	
			 'stock'		 => $stock,
			 'class' 		 => $class,
		);
		
		return;
		
	}
 	
	public function ins_ajax_get_product() {
		global $woocommerce;
		
		if( isset( $_POST['product_id'] ) ) {	
			
			$status_message = '';
			
			$product = get_product( $_POST['product_id'] );
			
			ob_start();
			
			instore_load_template( 'instore-product-display-html' );
			
			$template = ob_get_clean();
			
			if( $product->managing_stock() ) {
				$stock = $product->get_availability();
				$stock = $stock['availability'];
			} else {
				$stock = 'Not Managing Stock';
			}
			
			if( $product->exists() ) {
				$this->json_return = array (
					'success' 			=> true,
					'call' 	  			=> $_POST['call'],
					'html'    			=> $template,
					'product_id'		=> $product->id,
					'sku'				=> $product->get_sku(),
					'post_data' 		=> $product->get_post_data(),
					'formated_title' 	=> $product->get_formatted_name(),
					'title'				=> $product->get_title(),
					'availibility'	  	=> $product->is_in_stock(),
					'stock_count' 		=> $stock,
					'regular_price '	=> $product->get_regular_price(),
					'price'				=> $product->get_price(),
					'on_sale' 			=> $product->is_on_sale() ? 'YES' : 'NO',
					'sale_price'		=> $product->get_sale_price(),
					'thumbnail'			=> $product->get_image(),
					'gallery_img_ids'	=> $product->get_gallery_attachment_ids(),
				);
				
				if( $product->has_attributes() ) {
					$this->json_return['attributes'] 	= $product->get_attributes();
				}
				$this->json_return['product_type'] 		= ucwords($product->product_type);
			} else {
				$status_message = 'Product not found';
			}
		} else {
			$this->json_return['success'] = false;
			$status_message = 'Missing product id';
		}
			
		$this->json_return['status_message'] = $status_message;

		echo json_encode( $this->json_return );
		die();	
	}
	
	public function ins_ajax_clear_cart() {
		global $woocommerce;
		
		$woocommerce->cart->empty_cart();
		
		$this->json_return['success'] 			= true;
		$this->json_return['call'] 				= 'clear_cart';
		$this->json_return['status_message'] 	= 'Order has been cleared.';
		$this->json_return['line_items']		= 0;
			
		echo json_encode( $this->json_return );
		die();
	}
	
	public function ins_ajax_override_needed() {
			
	}
	
	public function ins_get_discount_code( $discount ) {
		global $woocommerce, $current_user;
		
		$coupon_array = $_POST['discount'];
				
		//if discount amount passed process and add to order
		$disc_type = $coupon_array['disc_type'];
		$disc_reason = $coupon_array['disc_reason'] + ' applied on ' . date( 'm/d/Y h:m:I' ) . ' by ' . $current_user->user_login; 
		$disc_desc = isset( $coupon_array['disc_desc'] ) ? sanitize_text_field( $coupon_array['disc_desc'] ) : $disc_reason;
		
		$disc_code = $coupon_array['disc_reason'] . time();
				
		$code = array(
			'id'					 => '20',
			'code'    		 	   	 => $disc_code,
			'type' 	 			     => $coupon_array['disc_type'],
			'amount' 		       	 => $coupon_array['disc_amount'],
			'individual_use' 	     => $coupon_array['individual_use'] ? 'yes' : 'no',
			'product_ids'    	   	 => isset( $coupon_array['include_products'] ) ? $coupon_array['include_products'] : array(),
			'exclude_product_ids'  	 => isset( $coupon_array['exclude_products'] ) ? $coupon_array['exclude_products'] : array(),
			'usage_limit'		   	 => isset( $coupon_array['usage_limit'] ) ? $coupon_array['usage_limit'] : 1,
			'limit_usage_to_x_items' => isset( $coupon_array['limit_usage_to_x_items'] ) ? $coupon_array['limit_usage_to_x_items'] : 0,
			'usage_count'			 =>	isset( $coupon_array['usage_count'] ) ? $coupon_array['usage_count'] : 0,
			'expiry_date'			 => isset( $coupon_array['expiry_date'] ) ? strtotime( $coupon_array['expiry_date'] ) : strtotime( 'tomorrow' ),
			'apply_before_tax' 		 => $coupon_array['apply_before_tax'] ? 'yes' : 'no',
			'product_categories'	 => isset( $coupon_array['include_catss'] ) ? $coupon_array['include_catss'] : array(),
			'exclude_product_categories' => isset( $coupon_array['exclude_cats'] ) ? $coupon_array['exclude_cats'] : array(),
			'exclude_sale_items'	 => $coupon_array['exclude_sale_items'] ? 'yes' : 'no',
			'free_shipping'			 => 'no',
			'customer_email'		 => '',
			'minimum_amount'		 => isset( $coupon_array['minimum_amount'] ) ? $coupon_array['minimum_amount'] : 0,
		);
		
		return $code;
	}
	
	public function ins_get_template( $template ) {
		ob_start();
		
		instore_load_template( $template );
		
		return ob_get_clean();
	}
 }
 
 new Instore_AJAX();
 
 endif;