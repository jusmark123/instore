<?php
/**
 * Plugin Name:		In-Store Point of Sale for WooCommerce
 * Plugin URI:		http://jwalkerdzines.com/plugins/instore
 * Description:		Easily sell your woocommerce products directly to your customers from this easy to use Point of Sale interface.
 * Version:			1.0.0
 * Author:			JwalkerDzines LLC
 * Copyright:		2014 JwalkerDzines LLC
 * License:			GPL3
 * License URI:		http://www.gnu.org/licenses/gpl-3.0.html
 */
 
 if( ! defined( 'ABSPATH' ) ) exit;
 
 if( ! class_exists( 'Instore' ) ) :
 
 class Instore {
	 
	 protected static $_instance = null;
	 
	 public static function instance() {
		if( is_null( self::$_instance ) ){
			self::$_instance = new self(); 
		}
		
		return self::$_instance;
	 }
	 
	 public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );	
		add_action( 'init', array( $this, 'load_instore' ) );
		
		//add instore link to admin bar for easy navigation
		if( is_admin() ) {
			add_action( 'wp_before_admin_bar_render', array( $this, 'add_admin_bar_link' ) );
		}
	 }
	 
	 public function init() {
		include_once( 'admin/instore-admin-functions.php' );
		include_once( 'frontend/instore-template-functions.php' );
		include_once( 'frontend/class-instore-ajax.php' );	
		include_once( 'frontend/class-instore-interface.php' );
		
		if( class_exists( 'WC_Integration' ) ) {
			include_once( 'admin/class-instore-admin.php' );
			
			add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
		} else {
			
		}
		
		if( class_exists( 'WC_Payment_Gateways' ) ) {
			include_once( 'gateways/class-instore-gateway-cash.php' );
			
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_payment_gateways' ) );
		}
		
		$this->create_pages();
		$this->create_roles();
	 }
	 
	 public function load_instore() {
		wp_enqueue_script( 'chosen', $this->plugin_url() . '/js/chosen/chosen.jquery.min.js', array( 'jquery' ) );
		wp_enqueue_style( 'chosen', $this->plugin_url() . '/js/chosen/chosen.css' );	 	
		wp_enqueue_style( "jquery-ui", 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css');
	 }
	 
	 public function add_integration() {
		 
		$integrations[] = 'Instore_Admin';
		
		return $integrations; 
	
	 }
	 
	 public function add_payment_gateways( $methods ) {
		 
		$methods[] = 'Instore_Gateway_Cash';
		
		return $methods;
	
	 }
	 
	 public function create_pages() {		
		$pages = apply_filters( 'woocommerce_create_pages', array(
			'instore' => array( 
		 		'name'	  => _x( 'instore', 'Page slug', 'instore' ),
				'title'   => _x( 'Instore', 'Page title', 'instore'),
				'content' => '',
			)
		) ); 
		
		foreach( $pages as $key => $page ) {
			if( ! get_option( 'instore_' . $key . '_page_id' ) ){
				ins_create_page( esc_sql( $page['name'] ), 'instore_' . $key . '_page_id', $page['title'], $page['content'], '' );  
			}
		}
	 }
	
	 public function create_roles() {
		global $wp_roles;
		
		if( class_exists( 'WP_Roles' ) ) {
			if( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();	
			}
		}
		 
		if( is_object ($wp_roles ) ) {
			
			add_role( 'associate', __( 'Instore Associate', 'instore' ), array( 
				'edit_posts'             => true,
				'edit_published_posts'   => true,
				'read' 						=> true,
				'publish_posts'          => true,
			) );
			
			$wp_roles->add_cap( 'associate', 'use_instore' );
			
			$capabilities = array( 
				'use_instore',
				'manage_instore',
				'view_instore_reports'
			);
			
			foreach( $capabilities as $capability ) {
				$wp_roles->add_cap( 'shop_manager', $capability );
				$wp_roles->add_cap( 'administrator', $capability );
			}
		}
	 }
	 
	 public function add_admin_bar_link() {
		global $wp_admin_bar;

		$url = get_permalink( ins_set_instore_page_id() );
		$wp_admin_bar->add_menu( array(
			'parent' => false,
			'id'	 => 'instore',
			'title'	 => __( 'Instore', 'instore' ),
			'href'	 => $url,
		) );	
	 } 
	 
	 public function plugin_url() {
 		return plugins_url() . '/instore';
	 }
	 
	 public function plugin_path() {
		return plugin_dir_path( __FILE__ );
	 }
 }
 
 function instore() {
	 return Instore::instance();
 }
 
 $WC_Integration_Demo = new Instore( __FILE__ );
 
 endif;
 
