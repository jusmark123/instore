<?php
/**
 * File Name: 	Instore Admin Functions
 * Version:	 	1.0.0
 * Admin specific functions
 **/
 
add_action( 'show_user_profile', 'add_instore_custom_profile_field' );
add_action( 'edit_user_profile', 'add_instore_custom_profile_field' );

add_action( 'personal_options_update', 'ins_save_custom_profile_field' );
add_action( 'edit_user_profile_update', 'ins_save_custom_profile_field' );

 function ins_get_coupons() {
	global $wpdb, $woocommerce;
	
	$coupons = array();
	$coupons['default'] = __( 'Select Coupon', 'instore' );
	
	$query = $wpdb->get_results( "SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'shop_coupon' AND post_status = 'publish'");
	
	foreach( $query as $row ) {
		$coupons[$row->ID] = __( $row->post_title, 'instore' );
	}

	return $coupons;
 }
 
 function get_payment_gateway_fields( $gateway_id ) {
	global $woocommerce;
	
	$gateways = $woocommerce->payment_gateways->get_available_payment_gateways();
	
	foreach( $gateways as $key => $method ) {
		
		if( $method->id == $gateway_id ) {
			switch( $method->title ) {
				case 'Credit Card':
					break;
				case 'Cheque Payment':
					break;
				case 'Bank Transfer':
					break;
				case 'Cash':
					break;
			}
		}
	}
 }
 
 function ins_get_payment_methods() {
	global $woocommerce;
	
	$payment_methods = array();
	$payment_methods['default'] = __( 'Select Payment Method', 'instore' );
	
	if( class_exists( 'WC_Payment_Gateways' ) ) {
		$gateways = $woocommerce->payment_gateways->get_available_payment_gateways();
				
		foreach( $gateways as $key => $gateway ) {
			$payment_methods[$key] = __( $gateway->title, 'instore' );
		}
	}
	
	return $payment_methods;
 }
 
 function ins_set_instore_page_id() {
	global $wpdb;
	
	if( ! get_option( 'instore_instore_page_id' ) ) {
		$id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s", 'Instore' ) );
		
		if( $id ) {
			return update_option( 'instore_instore_page_id', $id );
		} else { 
			return false;
		}
	} else {
		return get_option( 'instore_instore_page_id' );
	}
  }
  
 function add_instore_custom_profile_field( $user ) { 
 		$pin = get_user_meta( $user->ID, 'instore_login_pin', true );
 ?>
		<h3><?php _e( 'Instore Login Pin', 'instore' ); ?></h3>
        
        <table class="form-table">
        	<tr>
            	<th><label for"instore_login_pin"><?php _e( 'Instore Login Pin', 'instore' ); ?></label></th>
                <td><input type="password" name="instore_login_pin" id="instore_login_pin" value="<?php echo esc_attr( isset( $pin ) ? $pin : '' ); ?>" class="regular-text"  /></td> 
            </tr>
        </table>         
 <?php
 }
 
 function ins_save_custom_profile_field( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) )
		return FALSE;
	
	update_user_meta( $user_id, 'instore_login_pin', $_POST['instore_login_pin'] );
 }
 
 function ins_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {
    global $wpdb;

    $option_value = get_option( $option );

    if ( $option_value > 0 && get_post( $option_value ) )
        return -1;

    $page_found = null;

    if ( strlen( $page_content ) > 0 ) {
        // Search for an existing page with the specified page content (typically a shortcode)
        $page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
    } else {
        // Search for an existing page with the specified page slug
        $page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_name = %s LIMIT 1;", $slug ) );
    }

    if ( $page_found ) {
        if ( ! $option_value )
            update_option( $option, $page_found );
		
		return $page_found;
    }

    $page_data = array(
        'post_status'       => 'publish',
        'post_type'         => 'page',
        'post_author'       => 1,
        'post_name'         => $slug,
        'post_title'        => $page_title,
        'post_content'      => $page_content,
        'post_parent'       => $post_parent,
        'comment_status'    => 'closed'
    );
    $page_id = wp_insert_post( $page_data );

    if ( $option )
        update_option( $option, $page_id );

    return $page_id;
}
 