<?php if( ! defined( 'ABSPATH') ) exit; //exit if accesed directly
	global $woocommerce;
?>

<div class="payment_form">
    <form action="" method="post">
    	<div class="pay-form form-row payment_fields">
        	<table>
            	<thead>
        			<th colspan="2"><h3 style="text-align:center; font-size:1.5em;"><?php _e( 'Tender Payment', 'instore' ); ?></h3></th>
                </thead>
                <tbody>
                	<tr>
						<td><span class="balance balance_due"><?php _e( 'Balance Due: ', 'instore' ); ?></span></td>
                        <td><span class="balance bal_paid_label"><?php _e( 'Payment Tendered: ', 'instore' ); ?></span><span class="balance" id="bal_paid">$ 0.00</span></td>
                    </tr>
                    <tr>
            			<td><label for="payment_amount"><?php _e( 'Payment Amount: ', 'instore' ); ?></label><input type="text" id="payment_amount" /></td>
            			<td><label for="customer_email">Customer Email: </label><input type="email" id="customer_email" /></td>
                   </tr>
				</tbody>
          	</table>
    	</div>
  		<div class="pay-form form-row" style="text-align:center;">
			<?php wp_nonce_field( 'woocommerce-pay' ); ?>
            
            <li class="add_payment pay_action"><?php _e( 'Add Payment', 'instore' ); ?></li>
            <li class="cancel pay_action"><?php _e( 'Cancel', 'instore' ); ?></li>
            <input type="hidden" name="woocommerce_pay" value="1"
		</div>
    </form>
</div>
	
	
