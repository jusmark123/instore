<?php 
if( ! defined( 'ABSPATH' ) ) exit; //exit if accessed directly

if( ! class_exists( 'Instore_Interface' ) ) :

class Instore_Interface {
	
	protected $settings;
	
	public function __construct() {
		$settings = get_option('woocommerce_instore_settings');
		
		add_filter( 'template_include', array( $this, 'security_check' ) );
	}
	
	public function load_includes() {
		wp_enqueue_style( 'frontend-styles', Instore()->plugin_url() . '/css/frontend-styles.css' );
		wp_enqueue_style( 'ui_smoothness', "//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" );
		wp_enqueue_script( 'frontend-scripts', Instore()->plugin_url() . '/js/instore-frontend.js', array( 'jquery', 'jquery-ui-button' ) );		
		wp_enqueue_script( 'instore-ajax', Instore()->plugin_url() . '/js/instore-ajax.js', array( 'jquery', 'jquery-ui-dialog', 'jquery-ui-button', 'jquery-ui-tabs', 'jquery-ui-datepicker', 'chosen') );
		wp_localize_script( 'instore-ajax', 'instore_ajax_params', array('ajax_url' => admin_url( 'admin-ajax.php') ) );	
	}
	
	public function security_check( $template ) {
		global $woocommerce, $current_user;
		//var_dump( Instore_Reports::get_report_data() );
		//make sure we are on console page
		if( is_page( ins_set_instore_page_id() ) ) {
			//load scripts and style
			$this->load_includes();
			
			//check if user is logged in
			if( is_user_logged_in() ) {
				
				//if instore login set display console
				if( isset( $woocommerce->session->instore_login ) ) {
					$template = self::load_environment();
				} else {
					//if user locked out, logout and redirect to wp_login and display error message, otherwise prompt for instore login pin
					if( ! isset( $settings['lockout'] ) || ! in_array( $current_user->ID, $setting['lockout'] ) ) {
						$template = self::instore_login();	
					} else {
						ob_start();
						add_filter( 'login_message', 'ins_login_message' ); 
						wp_redirect( wp_logout_url( get_permalink() ) );	
					}
				}	
			//user not logged in redirect to login page
			} else {
				ob_start();
				wp_redirect( wp_login_url( get_permalink() ) );
			}
		}
		return $template;
	}
	
	public static function instore_login() {
		
		//load instore login template
		return instore_load_template( 'instore-login-html' ); 
		 
	}
	
	public static function load_environment() {
		
		//load instore interface
		return instore_load_template( 'instore-interface-html');
	}
}

new Instore_Interface();

endif;