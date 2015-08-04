<?php
/**
 * Class Name:		Instore Shop Metabox
 * Version:			1.0.0
 * Author:			JwalkerDzines LLC
 */

 if( ! defined( 'ABSPATH' ) ) exit;
 
 if( ! class_exists( 'Instore_Shop_Metabox' ) ) :
 
 class Instore_Shop_Metabox {
	
	public function __construct() {
		//add metabox function
		add_action( 'add_meta_boxes', array( $this, 'add_shop_order_metabox' ) );
	}
	
	//define and assign metabox location
	public function add_shop_order_metabox() {
		add_meta_box( 'instore', __( 'Instore for Woocommerce', 'instore' ), array( $this, 'meta_box' ), 'shop_order', 'side', 'high' );
	}
	
	function meta_box() {
		global $woocommerce, $post;
		
		$order = new WC_Order( $post->ID );
		
		$order_discount = $order->get_total_discount();
		
		$order_tax = $order->get_total_tax();
		
		$order_subtotal = $order->get_subtotal_to_display();
		
		$order_total =  $order->get_formatted_order_total();
		
		get_payment_gateway_fields( 'paypal' );
				
		woocommerce_wp_select( array( 
			'id'	  		=> 'payment_type',
			'label'			=> '',
			'value'			=> 'default',
			'options' 		=> ins_get_payment_methods(),
			'class'			=> 'chosen-select',
		) );
		
		
		woocommerce_wp_select( array( 
			'id'		=> 'add_coupon',
			'label'		=> '',
			'value'		=> 'default',
			'options'  	=> ins_get_coupons(),
			'class'		=> 'chosen-select',
		) );
		
		woocommerce_wp_text_input( array(
			'id'			=> 'sub_total',
			'label' 		=> __( 'Order Subtotal', 'instore' ),
			'placeholder'	=> 0.00,
			'value'			=> $order_subtotal,
		) );	
		woocommerce_wp_text_input( array(
			'id'			=> 'order_tax',
			'label'			=> __( 'Order Tax', 'instore' ),
			'placeholder'	=> 0.00,
			'value'			=> $order_tax,
		) );
		woocommerce_wp_text_input( array(
			'id'			=> 'applied_discount',
			'label'			=>  __( 'Applied Discount', 'instore' ),
			'placeholder'	=> 0.00,
			'value'			=> $order_discount,
		) );
		
		
	}
 }
 
 new Instore_Shop_Metabox();
 
 endif;