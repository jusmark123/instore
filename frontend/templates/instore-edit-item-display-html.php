<?php if( ! defined( 'ABSPATH' ) ) exit; //Exit if accessed directly.
	global $woocommerce;
 ?>

<div class="edit_line_item" id="item_details">
  <form action="#" method="post" class="edit_cart_item">
    <table>
      <tbody class="item_details">
        <tr>
          <td class="item_detail">ID: <span class="line_item_product_id" id="product_id"></span></td>
          <td class="item_detail">SKU: <span class="line_item_sku"></span></td>
          <td class="item_detail">Title: <span class="line_item_title" colspan="3"></span></td>
          <td/>
        </tr>
        <tr>
          <td>Description: </td>
        </tr>
        <tr>
          <td class="item_detail" colspan="3"><span class="line_item_description"></span></td>
        </tr>
        <tr>
          <td class="item_detail">Regular Price: <span class="line_item_regular_price"></td>
          <td class="item_detail">On Sale: <span class="line_item_on_sale"></td>
          <td class="item_detail">Sale Price: <span class="line_item_sale_price"></td>
        </tr>
        <tr>
          <td class="item_detail">Current Price: <span class="line_item_price"></td>
          <td class="item_detail">Quantity:
            <input type="number" class="line_item_quantity form_edit" id="item_quantity" min="1" step="1" style="width:50px;" /></td>
          <td class="item_detail">Sub Total: <span class="line_item_subtotal"></td>
        </tr>
      </tbody>
    </table>
    <?php if( $woocommerce->cart->coupons_enabled() ) {?>
	 <table id="discount_details" style="display:none">
      	<thead>
            <th class="discount_edit_head"><input type="checkbox" class="discount_edit" /></th>
        	<th class="discount_edit_head"><?php echo __('Discount Code', 'instore' ); ?></th>
        	<th class="discount_edit_head"><?php echo __('Discount Type', 'instore' ); ?></th>
        	<th class="discount_edit_head"><?php echo __('Discount Amount', 'Instore' ); ?></th>
        	<th class="discount_edit_head"><?php echo __('Discount Total', 'Instore' ); ?></th>
         </thead>
      <tbody>
      </tbody>
      <tfoot>
      	<td colspan="4">
        	<button class="disc_action" id="remove_selected">Remove Discount</button>
        	<button class="disc_action" id="edit_selected">Adjust Discount</button>
        	<button class="disc_action" id="view_discount">View Disount Details</button>
        <td/>
        <td/>
      </tfoot>
    </table>
    <?php } ?>
    <div class="edit_actions">
      <li class="edit_action instore_dialog_btn" id="update_item">Update Line Item</li>
      <li class="edit_action instore_dialog_btn" id="delete_item">Delete Line Item</li>
      <li class="edit_action instore_dialog_btn" id="cancel">Cancel</li>
      <input type="hidden" id="cart_id"  />
    </div>
  </form>
  <script type="text/javascript">
  (function($) {
	$(document).ready(function(e) {	
     	$('#item_quantity').bind( 'input', function(e) {
			
			if( $(this).val() > 0 ) {	
				var data = {
					action :	'ins_ajax_update_order_item',
					cart_id: 	$('.edit_cart_item').attr('id'),
					product_id: $('#product_id').text(),
					quantity: 	$('#item_quantity').val(),
				}
			
				ajax_request( data );
			}
		});  	 
    });

	$('.edit_action, .disc_action').click( function(e) {	
		e.preventDefault();
		var data = {
			cart_id: 	$('.edit_cart_item').attr('id'),
			product_id: $('#product_id').text(),
			quantity: 	$('#item_quantity').val(),
		}
		switch( $(this).attr('id') ) {
			case 'update_item':				
				data['action']  = 'ins_ajax_refresh_cart';
				break;	
			case 'delete_item':
				data['action'] = 'ins_ajax_remove_order_item';
				break;
			case 'cancel':	
				break;
		}	
		close_dialog();
		ajax_request( data );
	})
  })(jQuery);
  </script>
  <style type="text/css">
  
  </style>
</div>
