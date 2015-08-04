<?php
/**
 * Class Name:		Instore Admin
 * Version:			1.0.0
 * Author:			JwalkerDzines LLC
 */
 
 if( ! defined( 'ABSPATH' ) ) exit; //exit if accessed directly
 
 if( ! class_exists( 'Instore_Admin' ) ) :
 
 class Instore_Admin extends WC_Integration {
	 
	//constructor
	public function __construct() {
		$this->id 			= 'instore';
		$this->method_title = __( 'In-Store for Woocommerce', 'instore' );
		$this->description  = __( 'Easily sell your woocommerce products directly to your customers from this easy to use Point of Sale interface.', 'instore' );
		$this->init();
	}
	
	public function init() {		
		//Load Settings
		$this->load_scripts();
		$this->init_form_fields();
		$this->init_settings();
		
		include_once( 'class-instore-admin-shop-metabox.php' );
		
		add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'process_admin_options' ) );
	}
	
	public function load_scripts() {
		if( is_admin() ) {
			wp_enqueue_script( 'instore-admin', plugins_url( 'js/instore-admin.js', dirname( __FILE__ ) ), array( 'jquery' ) );
		}
	}
	
	public function init_form_fields() {
		global $woocommerce;
				
		$this->form_fields = array( 
			//Enable In-Store Point of Sale
			'enabled' 			=> array(
				'title'			=> __( 'Enable In-Store Point of Sale', 'instore' ),
				'type'			=> 'checkbox',
				'label'			=> __( 'Enable/Disable', 'instore' ),
				'default'		=> 'no',
			),
			
			//Business Address Settings
			'title'				=> array(
				'title'			=> __( 'Business Address Settings', 'instore' ),
				'type'			=> 'title',
				'description'	=> __( 'Enter business address information for reports and receipts', 'instore' ),
			),
			'business_name'		=> array(
				'title'			=> __( 'Business Name', 'instore' ),
				'type'			=> 'text',
				'description'	=> __( 'Enter legal business name, event title, etc', 'instore' ),
			),
			'business_address_1'	=> array(
				'title'			=> __( 'Business Address 1', 'instore' ),
				'type'			=> 'text',
				'description'	=> __( 'Street Address, PO Box', 'instore' ),
			),
			'business_address_2'	=> array(
				'title'			=> __( 'Business Address 2', 'instore' ),
				'type'			=> 'text',
				'description'	=> __( 'Suite, Unit, Apt', 'instore' ),
			),
			'business_city'		=> array(
				'title'			=> __( 'Business City', 'instore' ),
				'type'			=> 'text',
				'description'	=> '',
			),
			'business_state'		=> array(
				'title'			=> __( 'Business State', 'instore' ),
				'type'			=> 'text',
				'description'	=> '',
			),
			'business_zipcode'		=> array(
				'title'			=> __( 'Business City', 'instore' ),
				'type'			=> 'text',
				'description'	=> '',
			),
			'business_phone'		=> array(
				'title'			=> __( 'Business Phone', 'instore' ),
				'type'			=> 'text',
				'description'	=> __( 'Phone number will be formatted automatically', 'instore' ),
			),
			'business_email'		=> array(
				'title'			=> __( 'Business Email', 'instore' ),
				'type'			=> 'email',
				'description'	=> __( 'Enter a valid email address', 'instore' ),
			),
			
			//Security Settings
			'title'				=> array(
				'title'			=> __( 'Security Settings', 'instore' ),
				'type'			=> 'title',
				'description'	=> __( 'The following settings determine Point of Sale security options', 'instore' )
			),			
			'timeout_enabled'	=> array(
				'title'			=> __( 'Enable Security Timeout', 'instore' ),
				'type'			=> 'checkbox',
				'label'			=> __( 'Recommended: Enable this option to lock out the Point of Sale page when inactive for a specified period of time.', 'instore' ),
				'default'		=> 'yes',
			),
			'timeout'			=> array(
				'title'			=> __( 'Security Timeout Settings', 'instore' ),
				'type'			=> 'select',
				'description'	=> __( 'Select maximum period of inactivity before timeout', 'instore'),
				'default'		=> '10min',
				'options'		=> array(
					'5min'		=> __( '5 minutes', 'instore' ),
					'10min'		=> __( '10 minutes', 'instore' ),
					'15min'		=> __( '15 minutes', 'instore' ),
					'20min'		=> __( '20 minutes', 'instore' ),
					'30min'		=> __( '30 minutes', 'instore' ),
					'1hour'		=> __( '1 hour', 'instore' ),
					'manual'	=> __( 'Manual', 'instore' ),			
				),
			),
			'login_method'		=> array(
				'title'			=> __( 'Login Method', 'instore' ),
				'type'			=> 'select',
				'default'		=> 'login_password',
				'options'		=> array(
					'login_password'	=> __( 'WordPress Login' ),
					'password'			=> __( 'Wordpress Password' ),
					'user_pin'			=> __( 'User Pin' ), 
				),
			),	
			'force_ssl' 		=> array( 
				'title'			=> __( 'Force SSL Encryption', 'instore' ),
				'type'			=> 'checkbox',
				'default'		=> 'no',
				'description'	=> __( 'Forces Secure Socket Layer Security on Point of Sale page for added security and data transmission. Requires SSL Certificate.', 'instore' ),
			),		
			
			
			//Sales Receipt Format Settings
			'address_on_receipt' 		=> array( 
				'title'			=> __( 'Address On Receipt', 'instore' ),
				'type'			=> 'checkbox',
				'default'		=> 'no',
				'description'	=> __( 'Determines whether address in added to top of sales receipts', 'instore' ),
			),	
			'return_policy'		=> array(
				'title'			=> __( 'Add Return Policy', 'instore' ),
				'type'			=> 'textarea',
				'description'	=> __( 'Add your return/exchange policy here to be displayed on receipt footer.', 'instore' ),
				'default'		=> '',
			),
			'receipt_message'		=> array(
				'title'			=> __( 'Add Special Message', 'instore' ),
				'type'			=> 'textarea',
				'description'	=> __( 'Add a special message for your customers. Great for holidays and store specials.', 'instore' ),
				'default'		=> '',
			),			
			'logo_on_receipt'	=> array(
				'title'			=> __( 'Receipt Logo', 'instore' ),
				'type'			=> 'file',
				'description'	=> __( 'Click to add your logo to the reciept header.', 'instore' ),
				'default'		=> '',
			),
			'user_on_receipt'		=> array(
				'title'			=> __( 'Include Cashier on Receipt', 'instore' ),
				'type'			=> 'checkbox',
				'label'	=> __( 'Check if you would like cashier first name and last initial to appear on receipt.', 'instore' ),
				'default'		=> 'yes',
			),
			'print_coupons'		=> array(
				'title'			=> __( 'Print Coupons', 'instore' ),
				'type'			=> 'checkbox',
				'label'	=> __( 'Check to print selected coupon with each sale.', 'instore' ),
				'default'		=> '',
			),
			'current_coupon'	=> array( 
				'title'			=> __( 'Select Coupon', 'instore' ),
				'type'			=> 'select',
				'description'	=> __( 'Select the desired coupon you would like to print', 'instore' ),
				'options'		=> ins_get_coupons(),
			),
			
			//System Device Settings
			'authorized_devices' => array(
				'type' => 'device_registration',
			),
		);
	}
	
	public function generate_device_registration_html() {
		ob_start();
		?>
        <tr valign="top" id="device_options">
        	<th scope="row" class="titledesc"><?php _e( 'Device Registration', 'instore' ); ?></th>
           	<td class="forminp">
            	<table class="instore-devices">
                	<thead>
                    	<tr>
                        	<th class="check-column"><input type="checkbox" /></th>
                            <th><?php _e( 'Device Name', 'instore' ); ?></th>
                            <th><?php _e( 'Device MAC Address', 'instore' ); ?></th>
                            <th><?php _e( 'Cash Drawer', 'instore' ); ?></th>
							<th><?php _e( 'Reciept Printer', 'instore' ); ?></th>
                            <th><?php _e( 'Barcode Scanner', 'instore' ); ?></th>
                        </tr>
                     </thead>
                     <tfoot>
                     	<tr>
                        	<th colspan="3">
                            	<a href="#" class="button plus insert"><?php _e( 'Add Device', 'instore' ); ?></a>
                                <a href="#" class="button minus remove"><?php _e( 'Remove selected device(s)', 'instore' ); ?></a>
                            </th>
                            <th colspan="4">
                            	<small class="description"><?php _e( 'For added security only registered devices may access secure frontend POS page. Additional selected hardware accessories (printer, scanner) will be detected after parent device is registered.', 'instore' ); ?></small>
                            </th>
                        </tr>
                   	</tfoot>
                    <tbody id="devices">
                    	<?php
							$devices = $this->get_option( 'authorized_devices', null );
							if( $devices ) {
								foreach( $devices as $key => $device ) {
									?>
                                    <tr>
                                    	<td class="check-column"><input type="checkbox" /></td>
                                        <td><input type="text" name="device_name[<?php echo $key; ?>]" value="<?php echo esc_attr( $device['device_name'] ); ?>" /></td>
                                        <td><input type="text" name="device_mac_id[<?php echo $key; ?>]" value="<?php echo esc_attr( $device['device_mac_id'] ); ?>" /></td>
                                        <td><input type="checkbox" name="cash_drawer[<?php echo $key; ?>]" <?php checked( isset( $device['cash_drawer'] ) && $device['cash_drawer'] == true, true ); ?> /></td>
                                        <td><input type="checkbox" name="receipt_printer[<?php echo $key; ?>]" <?php checked( isset( $device['receipt_printer'] ) && $device['receipt_printer'] == true, true ); ?> /></td>
                                        <td><input type="checkbox" name="barcode_scanner[<?php echo $key; ?>]" <?php checked( isset( $device['barcode_scanner'] ) && $device['barcode_scanner'] == true, true ); ?> /></td>
                                    </tr>
                                 	<?php
								}
							}
						?>
                    </tbody>
               	</table>
                <script type="text/javascript">
				
				</script>
		<?php
		return ob_get_clean();
	}
	
	public function generate_file_html() {
		
	}
 }
 
 endif;