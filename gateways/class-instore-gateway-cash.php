<?
/**
 * Class Name:		Instore Shop Metabox
 * Version:			1.0.0
 * Author:			JwalkerDzines LLC
 */
 
 if( ! defined( 'ABSPATH' ) ) exit; //Exit if accessed directly
 
 If( ! class_exists( 'Instore_Gateway_Cash' ) ) :
 
 class Instore_Gateway_Cash extends WC_Payment_Gateway {
	 
	 public function __construct() {
		$this->id = 'instore_cash_payment';
		$this->has_fields = false;
	 	$this->method_title = 'Instore Cash Payment';
		$this->method_description = "This payment gateways provides a method completing cash transactions with the Instore Point of Sale(POS) and WooCommerce Shop Order pages. On the Instore POS, simple enter in a dollar amount, select cash in the select payment box or simple click on a quick pay option. On the WooCommerce Shop Order page, use the instore metabox on the top right corner of the screen. Select Cash from the dropdown and enter the amount tendered and click the process payment button.";
		
		
		$this->title = $this->get_option( 'title' );
		$this->init_form_fields();
		$this->init_settings();
		
		foreach( $this->settings as $key => $setting ) {
			$this->$key = $setting;
		}
		
		if( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}
	 }
	 
	 public function init_form_fields() {
		 $this->form_fields = array(
		 	'enabled'	=> array( 
				'title'			=> __( 'Enable/Disable', 'instore' ),
				'type'			=> 'checkbox',
				'label' 		=> __( 'Enable Instore Cash Payment', 'instore' ),
				'default' 		=> 'yes',
			),
			'title'		=> array( 
				'title' 		=> __( 'Title', 'instore' ),
				'type'			=> 'text',
				'description'	=> __( 'This controls the title displayed on the Instore POS and Shop Order page.', 'instore' ),
				'default'		=> __( 'Cash', 'instore' ),
				'desc_tip'		=> true,
			), 
			'min_payment_allowed'	=> array(
				'title'			=> __( 'Max Total Allowed', 'instore' ),
				'type'			=> 'text',
				'description'	=> __( 'Specified the min payment amount allowed with this payment type.', 'instore' ),
				'default'		=> 0,
				'desc_tip'		=> true,
			),
			'enable_partial_payment' => array(
				'title'			=> __( 'Enable/Disable', 'instore' ),
				'type'			=> 'checkbox',
				'label'			=> __( 'Allow partial/combination payment with this payment type.', 'instore' ),
				'description'	=> __( 'Partial/combination payment are payments transacted through two or more payment methods for the same order, i.e ( apply cash first then apply the remaining total to a credit card.', 'instore' ),
				'default'		=> 'yes',
				'desc_tip'		=> true,
			),	
		);
	 }
	 
	 public function process_payment( $order_id ) {
		global $woocommerce;
		
		$order = new WC_Order( $order_id );
		
		//get payment tendered amount
		$payment_amount = $_POST['payment_amount'];
		
		//get order total
		$order_total = $order->get_total();
		
		//check for previous payments tendered and add to total payment
		$tendered = get_post_meta( $order->id, 'payment_tendered', true );
		$payment_amount = (float)$payment_amount + (float)$tendered;
			
		//calculate balance after payment applied	
		$balance = $order_total - $payment_amount;
		
		//if payment still needed
		if( $balance > 0 ) {
			
			//document transaction and create order note
			update_post_meta( $order->id, 'payment_tendered', $payment_amount );
			$order->add_order_note( __( '$ ' . round( $payment_amount, 2 ) . ' payment tendered. $ ' . round( $balance, 2 ) . ' remaining.', 'instore' ) );
			$order->update_status('pending', __( 'Awaiting additional payment', 'instore' ) );	
			$payment_complete = false;
			
		} else {
			
			//resuce stock and add order note
			$order->reduce_order_stock();
			$order->add_order_note( __( '$ ' . round( $payment_amount, 2 ) . ' payment Tendered. Change Due $ ' . round( $balance, 2 ) . ' .', 'instore' ) );
			
			//complete order
			$order->payment_complete();
			$payment_complete = true;
			
			//empty cart
			$woocommerce->cart->empty_cart();
		};
		
		//create return array
		$results = array(
			'result'		    => 'success',
			'order_id'		    => $order_id,
			'order_total'	    => $order_total,
			'balance' 		    => $balance,
			'balance_formatted' => wc_price( -$balance ),
			'payment_tendered'  => wc_price( $payment_amount ),
			'payment_complete'  => $payment_complete,
		);
		
		//return results
		return $results;
			
	 }
 }
 
 endif;
 
 