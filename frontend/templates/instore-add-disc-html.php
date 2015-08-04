<? if( ! defined( 'ABSPATH' ) ) exit; //Exit if accessd directly
global $woocommerce, $wpdb;
$coupons = $wpdb->get_results( $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE post_type = %s", "shop_coupon") );
$categories = ins_get_categories();
$products = ins_get_products();
$cart_items = $woocommerce->cart->cart_contents;
?>

<div class="add_discount" style="padding-top:1em; border-collapse:unset; font-size: 12px;">
  <h2 class="label">Add A Coupon</h2>
  <table id="coupons" style="border-bottom:1px solid #333;">
    <tr>
      <td class="coupon"><label for="coupon_select">Select a Coupon</label></td>
      <td class="coupon"><select class="chosen_select" style="width:200px;" id="coupon_select" data-placeholder="Select a Coupon" onblur="get_discount_info();" style="width:300px;">
        <option></option>
        <?php 
	if( $coupons ) {
		foreach( $coupons as $coupon ) {
			$coupon = new WC_Coupon( $coupon->post_title );
			
			if( ! $woocommerce->cart->has_discount( $coupon->code ) && $coupon->is_valid() ) { ?>
        <option value="<?php echo esc_attr( $coupon->code );?>"><?php echo esc_html( $coupon->code );?></option>
        <?php		} else {
			var_dump( $coupon->error_message );
		}
	}
} ?>
        </select>
        <li class="edit_coupon instore_dialog_btn" style="display:none;">Edit Coupon</li></td>
    </tr>
  </table>
  <h2 class="label discounts">Add Custom Discount</h2>
  <span class="coupon_error" style="color:red; font-weight:bold; text-shadow:2px 2px #fff"></span>
  <table id="options" style="border-bottom:1px solid #333;">
    <tr class="options">
      <td class="options" style="padding-bottom: 10px;"><label for="coupon_amount">Discount Amount</label></td>
      <td class="options"><input type="text" id="coupon_amount" name="coupon_amount" placeholder="0" /></td>
      <td class="options"><label for="coupon_description">Description</label></td>
      <td class="options"><input type="text" id="coupon_description"  /></td>
    </tr>
    <tr>
      <td class="options"><label for="coupon_type">Discount Type</label></td>
      <td class="options"><select class="chosen_select" id="coupon_type" data-placeholder="Select Discount Type" >
          <option/>
          <option value="fixed_product">Fixed Product</option>
          <option value="percent_product">Percentage Product</option>
          <option value="fixed_cart">Fixed Cart</option>
          <option value="percent_cart">Percent Cart</option>
        </select></td>
      <td class="options coupon_reason"><label for="coupon_reason">Discount Reason</label></td>
      <td class="options coupon_reason"><select class="chosen_select" id="coupon_reason" data-placeholder="Select Discount Reason" >
        <option/>
        <option value="manager_comp">Manager Comp</option>
        <option value="damaged_item">Damaged Item Discount</option>
        <option value="preffered_customer">Preferred Customer Discount</option>
        <option value="custom">Other</option>
        </select></td>
    </tr>
    <tr>
      <td class="options"><label for="coupon_usage_limit">Usage Limit</label></td>
      <td class="options"><input type="number" id="coupon_usage_limit" min="0" step="1" style="width:135px;" placeholder="0" /></td>
      <td class="options"><label for="coupon_limit_usage_to_x_items">Limit Usage To x Items</label></td>
      <td class="options"><input type="number" id="coupon_limit_usage_to_x_items" min="0" step="1"  style="width:135px;" placeholder="0" /></td>
    </tr>
    <tr>
      <td class="options"><label for="coupon_usage_limit_per_user">Limit Usage Per User</label></td>
      <td class="options"><input type="number" id="coupon_usage_limit_per_user" min="0" step="1" style="width:135px;" placeholder="0" /></td>
      <td class="options"><label for="coupon_expiry_date">Expiry Date</label></td>
      <td class="options"><input class="datepicker" id="coupon_expiry_date" type="text" min="0" step="1" style="width:135px;" value="<?php echo date( 'm/d/Y', strtotime( 'tomorrow' ) ); ?>" /></td>
    </tr>
    <tr>
      <td class="options"><label for="coupon_minimum_amount">Minimum Spend</label>
      <td class="options"><input type="text" id="coupon_minimum_amount" placeholder="0.00" /></td>
      <td class="options"><label for="coupon_exclude_sale_items">Exclude Sale Items</label>
      <td class="options"><input type="checkbox" id="coupon_exclude_sale_items" /></td>
    </tr>
    <tr>
      <td class="options"><label for="coupon_individual_use">Individual Use:</label>
      <td class="options"><input type="checkbox" id="coupon_individual_use" /></td>
      <td class="options"><label for="apply_before_tax">Apply Before Tax:</label>
      <td class="options"><input type="checkbox" id="coupon_apply_before_tax" /></td>
    </tr>
    <tr>
      <td class="options" colspan="2"><label for="include_products">Include Products</label></td>
      <td class="options" colspan="2"><label for="exclude_product">Exclude Products</label></td>
    </tr>
    <tr>
      <td class="options" colspan="2"><select class="chosen_select_multi" id="coupon_products_ids" multiple="multiple">
          <?php	foreach( $cart_items as $cart_item ) { ?>
          <option value="<?php echo esc_attr( $cart_item['product_id'] ); ?>"><?php echo esc_html( $cart_item['data']->post->post_title ); ?></option>
          <?php	} ?>
        </select>
      <td class="options" colspan="2"><select class="chosen_select_multi" id="coupon_exclude_product_ids" multiple="multiple">
          >
          <?php	foreach( $cart_items as $cart_item ) { ?>
          <option value="<?php echo esc_attr( $cart_item['product_id'] ); ?>"><?php echo esc_html( $cart_item['data']->post->post_title ); ?></option>
          <?php	} ?>
        </select>
    </tr>
    <tr>
      <td class="options" colspan="2"><label for="coupon_product_categories">Include Categories</label></td>
      <td class="options" colspan="2"><label for="coupon_exclude_product_categories">Exclude Categories</label></td>
    </tr>
    <tr>
      <td class="options" colspan="2"><?php
		if( count( $categories ) > 1 ) { ?>
        <select class="chosen_select_multi" id="coupon_product_categories" multiple="multiple">
          <?php 	foreach( $categories as $category ) { ?>
          <option value="<?php echo esc_attr( $category->term_id ); ?>"><?php echo esc_html( $category->name); ?></option>
          <?php 	} ?>
        </select>
        <?php	} else { 
		  $key = array_keys( $categories ); ?>
        <span><?php echo esc_html( $categories[$key[0]]['data']->post->post_title );?></span>
        <input type="hidden" class="include_category" value="<?php echo esc_attr( $categories[$key[0]]->term_id); ?>"  />
        <?php	} ?></td>
      <td class="options" colspan="2"><select class="chosen_select_multi" id="coupon_exclude_product_categories" multiple="multiple">
          <?php foreach( $categories as $category) {?>
          <option value="<?php echo esc_attr( $category->term_id ); ?>"><?php echo esc_html( $category->name ); ?></option>
          <?php } ?>
        </select></td>
    </tr>
    <tr>
      <td colspan="2"><li class="instore_dialog_btn" id="options_update">Create Discount</li></td>
    </tr>
  </table>
  </td>
  </tr>
  </table>
  <table id="coupon_details" style="display:none; font-size: 14px; border-bottom:1px solid #333;">
    <tr>
      <td class="coupon_description_label">Coupon Description:</td>
     </tr>
     <tr>
      <td class="coupon_description" colspan="4" style="padding-bottom:10px;"></td>
    </tr>
    <tr>
      <td class="coupon_amount_label">Amount: </td>
      <td class="coupon_amount"></td>
      <td class="coupon_type_label">Type: </td>
      <td class="coupon_type"></td>
    </tr>
    <tr>
      <td class="coupon_individual_use_label">Single Use: </td>
      <td class="coupon_individual_use"></td>
      <td class="coupon_expiry_date_label">Expires On: </td>
      <td class="coupon_expiry_date"></td>
    </tr>
    <tr>
      <td class="coupon_usage_limit_label">Usage Limit: </td>
      <td class="coupon_usage_limit"></td>
      <td class="coupon_usage_limit_per_user_label">Usage Limit Per User: </td>
      <td class="coupon_usage_limit_per_user"></td>
    </tr>
    <tr>
      <td class="coupon_limit_usage_to_x_items_label">Item Limit: </td>
      <td class="coupon_limit_usage_to_x_items"></td>
      <td class="coupon_usage_count_label">Usage: </td>
      <td><span class="coupon_usage_count"></span>/<span class="coupon_usage_limit"></span></td>
    </tr>
    <tr>
      <td class="coupon_apply_before_tax_label">Apply Before Tax: </td>
      <td class="coupon_apply_before_tax"></td>
      <td class="coupon_minimum_amount_label">Minimum Amount: </td>
      <td class="coupon_minimum_amount"></td>
    </tr>
    <tr>
      <td class="coupon_product_includes" colspan="2">Included Products</td>
      <td class="coupon_product_excludes" colspan="2">Excluded Products</td>
    </tr>
    <tr>
      <td class="coupon_product_ids" colspan="2"><select disabled="disabled"  class="coupon_product_ids  chosen_select_multi" multiple="multiple" data-placeholder="Include All" >
          <?php 
	  	foreach( $products as $product ) { ?>
          <option value="<?php echo esc_attr( $product->id );?>"><?php echo esc_html( $product->get_title() ); ?></option>
          <?php 
		} ?>
        </select></td>
      <td class="coupon_product_ids" colspan="2"><select disabled="disabled"  class="coupon_exclude_product_ids  chosen_select_multi" multiple="multiple" data-placeholder="Exclude None" >
          <?php 
	  	foreach( $products as $product ) { ?>
          <option value="<?php echo esc_attr( $product->id );?>"><?php echo esc_html( $product->get_title() ); ?></option>
          <?php 
		} ?>
        </select></td>
    </tr>
    <tr>
      <td class="coupon_product_includes" colspan="2">Included Categories</td>
      <td class="coupon_product_excludes" colspan="2">Excluded Categories</td>
    </tr>
    <tr>
      <td class="coupon_product_categories" colspan="2"><select disabled="disabled" class="coupon_product_categories chosen_select_multi" multiple="multiple" data-placeholder="Include All" >
          <?php 
      foreach( $categories as $category ) {?>
          <option value="<?php echo esc_attr( $category->term_id ); ?>"><?php echo esc_html( $category->name ); ?></option>
          <?php
	  } ?>
        </select></td>
      <td class="coupon_product_ids" colspan="2"><select disabled="disabled" class="coupon_exclude_product_categories chosen_select_multi" multiple="multiple" data-placeholder="Exclude None" >
          <?php 
      foreach( $categories as $category ) {?>
          <option value="<?php echo esc_attr( $category->term_id ); ?>"><?php echo esc_html( $category->name ); ?></option>
          <?php
	  } ?>
        </select></td>
    </tr>
    <tr style="padding:10px;">
      <td/>
      <td/>
      <td class="coupon_total_discount_label" style="padding:10px;">Total Discount: </td>
      <td class="total_discount">$0.00</td>
    </tr>
    <tr>
      <td><li class="instore_dialog_btn" id="discount_action">Add Coupon</li></td>
    </tr>
  </table>
  <table id="discount_details" style="display:none; border-bottom:1px solid #333;">
    <thead>
    <th class="discount_edit_head"><input type="checkbox" class="discount_edit" /></th>
      <th class="discount_edit_head"><?php echo __('Discount Code', 'instore' ); ?></th>
      <th class="discount_edit_head"><?php echo __('Discount Type', 'instore' ); ?></th>
      <th class="discount_edit_head"><?php echo __('Discount Amount', 'Instore' ); ?></th>
      <th class="discount_edit_head"><?php echo __('Discount Total', 'Instore' ); ?></th>
        </thead>
    <tbody/>
    
    <tfoot>
    
      <td colspan="4"><button class="disc_action" id="remove_selected">Remove Discount</button>
        <button class="disc_action" id="edit_selected">Adjust Discount</button>
        <button class="disc_action" id="view_discount">View Disount Details</button>
      <td/>
      <td/>
        </tfoot>
  </table>
  <div class="edit_actions" style="margin-top:1em;">
    <li class="edit_action instore_dialog_btn" id="apply_discounts" disabled="disabled">Apply Discounts</li>
    <li class="edit_action instore_dialog_btn" id="cancel" onclick="close_dialog();">Cancel</li>
    <li class="edit_action instore_dialog_btn" id="back" onclick="reset_dialog();" style="display:none;">&laquo;Go Back</li>
  </div>
  <script type="text/javascript">
	(function($) {
		$(document).ready(function(e) {
            $('.chosen_select').chosen({width:"225px"}).trigger('chosen:updated');
			$('.chosen_select_multi').chosen({width:"350px"}).trigger('chosen:updated');
			
			if( $('#coupon_select option').length == 1 ) {
				$('#coupon_select').attr('data-placeholder', 'No Applicable Coupons').attr('disabled','disabled').trigger('chosen:updated');	
			}
			
			$('#add_desc').on('click', function(e) {
				$('.add_desc').toggle();
			});
			
			$('.datepicker').datepicker();
			
			$('#coupon_select').on('change', function(e) {
				var data = {
					action: 'ins_ajax_get_discount',
					discount: $(this).val(),
				}
				
				ajax_request(data);
				$('.edit_coupon').show();
				$('#options').hide();
				$('#coupon_details').show();
				$('.discounts').hide();
				$('#back').show();
				$('.discount_action').button( "option", "label", "Add Coupon" );
			});
			
			$('.edit_coupon').on('click', function(e) {
				var data = {
					action: 'ins_ajax_get_discount',
					call: 'edit',
					discount: $('#coupon_select').val(),
				}
				
				$('.coupon_select').off();
				$('.discount_reason').hide();
				$('#coupon_details').hide();
				$( "#options_update" ).button( "option", "label", "Update Coupon" );
			
				$('.dialog').position({
					my: 'center',
					at: 'center',
					collision: 'fit',
				});
				ajax_request(data);
				$('#options').show();
			});
			
			$('.instore_dialog_btn').button();
			
			$('#options_update').click(function() {
				var data = {
					action: 'ins_ajax_get_discount',
					discount: {	
						disc_amount: $('#coupon_amount').val(),
						disc_type: $('#coupon_type').val(),
						disc_reason: $('#coupon_reason').val(),
						disc_desc: $('#coupon_description').val(),
						minimum_amount: $('#coupon_minimum_amount').val(),
						limit_usage_to_x_items: $('#limit_usage_to_x_items').val(),
						usage_limit: $('#coupon_usage_limit').val(),
						usage_limit_per_user: $('#coupon_usage_limit_per_user').val(),				
						apply_before_tax: $('#apply_before_tax').is('checked'),
						exclude_sale_items: $('#coupon_exclude_sale_items').is('checked'),
						individual_use: $('#coupon_individual_use').is('checked'),
						expiry_date: $('#coupon_expiry_date').val(),
						include_products: $('#coupon_products_ids').val(),
						exclude_products: $('#coupon_exclude_product_ids').val(),
						include_catss: $('#coupon_product_categories').val(),
						exclude_cats: $('#coupon_exclude_product_categories').val(),
					}
				}
				
				ajax_request(data);
			});		
			
			$('.cancel_update').on('click', function(e) {
				$('#coupon_select').on('click', function(e) {
					var data = {
						action: 'ins_ajax_get_discount',
						discount: $(this).val(),
					}
				
					$('#edit_coupon').show();
					ajax_request(data);
					$('#add_discounts').removeAttr('disabled');	
				});
				
				$('#coupon_options').hide();
				$('#coupon_details').show();	
			});
			
			reset_dialog = function() {
				$('#coupon_details').hide();
				$('#coupon_options').hide();	
				$('#discount_details').hide();
				$('#back').hide();
				$('#coupon_select option:selected').removeAttr('selected').trigger('chosen:updated');	
				$('.edit_coupon').hide();			
				$('#add_discounts').attr('disabled','disabled').trigger('chosen:updated');
				$('.discounts').show();
				$('#options').show();
				$('#coupons').show();
				$('#options_update').button('option', 'label', 'Create Discount');
			}
        });
	})(jQuery);
</script>
  <style type="text/css">
	#coupon_details, #cust_disc {
		border-collapse:unset;
	}

	.coupon_product_includes a, .coupon_product_excludes a, .discount_edit_label a {
 		color:#0000FF !important;	
	}
	#coupon_details td {
		padding:.8em 0 0 .5em;
	}
	
	.chosen-container-single .chosen-default {
		color:#000 !important;
	}

	.add_discount table tr td {
		padding: .5em 0;	
	}
	
	#discount_options {
		width:100%;
		margin:10px auto;
	}
	
	.label {
		font-size:16px;
		font-weight:bold;
		margin:10px 0;
	}
</style>
</div>
