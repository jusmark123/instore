(function($){
	var $this;
	var ajaxurl = (instore_ajax_params.ajax_url );
	
	$(document).ready(function(e) {	
		$( '.selectable' ).bind( 'click', function(e) {
			$(this).addClass('selected');
			
			var data = {
				action:	 	'ins_ajax_get_order_item',
				cart_id: 	$(this).attr('id'),
				product_id: $(this).find('.ins_product_id').text(),
			}
			
			ajax_request( data );
		});
			
		$('.instore_product_btn').bind('click', function(e) {
			var $this = $(this);
			get_product_details( $this );		
		});
	
		
		$('.function_button').on('click', function(e) {
			var $this = $(this);
			var data = {};
			
			switch( $(this).attr('id') ) {
				case 'order_reset':
					clear_cart( $this );
					break;
				case 'reg_functions':
					
					break;
				case 'mgr_functions':
					ajax_request({ action:'ins_ajax_request_override'});	
					break;
				case 'lock_console':
					data = {
						action: 'ins_ajax_lock_console',
					}
					break;
				case 'add_coupon':
					data = {
						action: 'ins_ajax_add_discount',
					}
			}
			ajax_request( data );
		});
		
		$('.payment_button').on('click', function(e) {
			var payment;
			var gateway;
			var data = {
				action: 'ins_ajax_add_payment',
				_wpnonce: 'woocommerce-checkout'
			}
				
			if( $(this).hasClass('active') ){
				if( $(this).hasClass('ins_quick_cash') ) {
					data['payment_amount'] = $(this).val();
					gateway = 'instore_cash_payment';	
				} else {
					gateway = $(this).attr('id');	
				}
				
				data['gateway'] = gateway;
				ajax_request( data );
					
			} else {
				var status_message = 'Payment options not available for empty cart.'
				error_handler( status_message );
			}
		});
	});
	
	ajax_request = function( data ) {		
		$.post( ajaxurl, data, function( response ) {
			// Get the valid JSON only from the returned string
			if ( response.indexOf( '<!--WC_START-->' ) >= 0 )
				response = response.split( '<!--WC_START-->' )[1]; // Strip off before after WC_START

			if ( response.indexOf( '<!--WC_END-->' ) >= 0 )
				response = response.split( '<!--WC_END-->' )[0]; // Strip off anything after WC_END

			response = $.parseJSON( response );
			if( response.success ) {	
				switch( response.call ) {
					case 'display_product':
						display_product( response );
						break;
					case 'refresh_display':
						refresh_display( response );
						break;		
					case 'clear_cart':
						reset_order_detail( response );
						break;
					case 'edit_line_item':
						get_line_item( response );
						break	
					case 'update_item_display':
						update_item_display( response );	
						break;	
					case 'process_payment':
						process_payment( response );
						break;
					case 'instore_redirect':
						window.location = response.url;
						break;
					case 'show_dialog':
						show_dialog( response );
						break;
				} 
			} else if( response.result == 'success' ) {
				payment_response( response );
			} else if( response.success == false ) {
				if( response.call == 'login_failed' ) {
					login_handler( response.status_message );	
				} else {
					error_handler( response );	
				}
			}
		});
	}
	
	get_product_details =function( $element ) {
		data = { 
			action: 'ins_ajax_get_product',
			call: 'display_product',
			product_id: $element.attr('id'),
		}
			
		ajax_request( data );		
	}
	
	display_product = function( response ) {
		
		if( response.html ) {
			if( ! $('.product_detail').length ) {
				$(response.html).appendTo('.product_details');
				$('.order_item_button').click(function(e) {
					var $this = $(this);
					add_item( $this );
				});
			}  
				
			for( var value in response ) {
				$('.' + value).text(response[value]); 	
			}
			
			if( response.availability === 'Out of stock' ) {		
				$('#' + product_id).addClass( 'out-of-stock' );
				$('.order_item_button').attr('disabled','disabled');
			}
					
			$('.product_quantity').val(1);
			$('.order_item_button').attr( 'id', response.product_id );
			$('.instruction').fadeOut( function(e) {
				$('.product_detail').fadeIn();
			});
		}
	}
	
	add_item = function( $element ) {
		data = {
			action: 'ins_ajax_add_order_item',
			product_id: $element.attr('id'),
			quantity: $('.product_quantity').val()
		}
		
		ajax_request( data );	
	}
	
	refresh_display = function( response ) {
		$('.order_items_table').html(response.html);
		
		if( response.item_count > 0 ) {
			if( ! $('.payment_button').hasClass( 'active' ) ) {
				$('.payment_button').toggleClass( 'active' );
			}
		} else {
			$('.payment_button').toggleClass( 'active' );
		}
		
		$.each( response, function( key, value ) {
			if( $('.' + key ).length > 0 ) {
				$('.' + key ).html(value);
			}
				
		});
		
		if( response.products ) {
			$.each( response.products, function( product_key, product ) {
				$.each( product, function( key, value ) {
					$('.' + product_key ).find('.' + key).html( value );
					if( key == 'stock' ) {
						var stock = parseInt( value );
						$('.' + product_key ).toggleClass( function() {
							if( stock > 0 ) {
								return 'in-stock';	
							} else {
								return 'not-in-stock';
							}
						});
					}
				});
			});
		}
		
		if( response.ins_disc_total ) {
			$('.ins_disc_label').html( '<a id="get_disc" href="#">DISCOUNT</a>' );	
		} else {
			$('.ins_disc_label').html( 'DISCOUNT' );
		}
		
		$( '.selectable' ).bind( 'click', function(e) {
			$(this).addClass('selected');
			
			var data = {
				action:	 	'ins_ajax_get_order_item',
				cart_id: 	$(this).attr('id'),
				product_id: $(this).find('.ins_product_id').text(),
			}
			
			ajax_request( data );
		});
				
		status_display( response );

	}
	
	clear_cart = function() {
		items = {};
		data = {
			action: 'ins_ajax_clear_cart',
		}
		
		$('.selectable').each(function() {
			var product_id = $(this).find('.ins_product_id').text();
			var quantity = $(this).find('.ins_product_quantity').text
			
			items[product_id] = {
				quantity: quantity,
			}
		});
		
		data['items'] = items;
		
		ajax_request(data);
	}
	
	reset_order_detail = function( response ) {
		$('.order_items_table').empty();
		$('.total_display').html( '$0.00');
		
		status_display( response );
	}
	
	status_display = function( response ) {
		$('.product_details').children().last().fadeOut(function(e) {
			$(this).remove();
			$('.instruction').html( response.status_message ).fadeIn().delay(3000).fadeOut(function(e) {
				$(this).html('Click a product button above to display product details here. Double-click product button to add product to order.').fadeIn();
			});
		});
	}
 	
	update_item_display = function( response ) {
		var operator
			
		if( response.coupon ) {
			if( response.action == "edit" ) {
				operator = '#';
			} else {
				$('#options').hide();
				$('#coupon_details').show();
				operator = '.';
			}
			
			$.each( response.coupon, function(key, value) {
				console.log( 'coupon_' + key + ' = ' + value );
				if( $.type(value) === 'array' && value.length > 0 ) {
					$.each(value, function( k, v ) {
						$(operator + 'coupon' + '_' + key + ' option[value="' + v +'"]').attr('selected','selected').trigger('chosen:updated');
					})
				} else if( $.type(value) === 'array' && value.length == 0 ) {
					$(operator + 'coupon' + '_' + key + ' option:selected').removeAttr('selected').trigger('chosen:updated');
				} else if( value == 'yes' ) {
					$(operator + 'coupon' + '_' + key).attr('checked', 'checked');
					$(operator + 'coupon' + '_' + key).html(value);
				} else {
					$(operator + 'coupon' + '_' + key).val(value);
					$(operator + 'coupon' + '_' + key + ' option[value="' + value +'"]').attr('selected','selected').trigger('chosen:updated');
					$(operator + 'coupon' + '_' + key).html(value === '' ? 'n/a' : value);
				}
			});
		
			$('.coupon_description').html( response.description);
				
		}
		$('.total_discount').html(response.discount);
	}
	
	show_dialog = function( response ) {
		response.options['close'] = function( ev, ui) { 
				$(this).remove();
		}		
		
		$(response.html).appendTo('.site').addClass('dialog');
		
		if( response.url ) {
			$('.dialog').find('form').append('<input class="redirect_url" type="hidden" value="' + response.url +'" />');
		}
		
		if( response.dialog_method ) {
			switch( response.dialog_method ) {
				case 'get_line_item':
					get_line_item(response);
					break;
			}
		}
	
		$('.instore_dialog_btn').button();
		$('.dialog').dialog(response.options);
	}
	
	get_line_item = function(response) {
			
		//load item data
		$.each( response.item, function( key, value ) {
			$('.line_item_' + key).html(value);
			
			if( key == 'quantity' ) 	
				$('.line_item_quantity').val( value );	
		});
		
		$('.edit_cart_item').attr('id', response.item.cart_id );
	}
	
	close_dialog = function() {
		$('.dialog').remove();
	}
	
	process_payment = function(response) {		
		$(response.html).appendTo('.product_details');
		
		$('.balance_due').append( response.amount_due );
		$('#bal_remaining').html( response.amount_due );
			
		if( response.fields ) {
			$('.payment_fields').append(response.fields);
		}
		
		$('#expmonth').css('width','80px').trigger('chosen:updated');
		
		$('.woocommerce-select').addClass('chosen').chosen();
		
		$('.pay_action').button();
		
		$('.pay_action').click(function(e) {
			var data = {
				action: 'ins_ajax_add_payment',
				payment_amount: $('#payment_amount').val(),
				gateway: response.gateway.id,
				_wpnonce: 'woocommerce-pay'
			}
			
			ajax_request( data );
		});
		
		$('.instruction').fadeOut(function() {
			$('.product_details').children().last().fadeIn();
		});
	}
	
	payment_response = function( response ) {
		$('.instore_product_btn').toggleClass('btn-disabled').off();
		
		//check for complete payment
		if( response.payment_complete ) {
			var options = {
				modal: 	  		true,
				autoOpen: 		true,
				width:	  		1000,
				dialogClass:	'no-title',
			}
			
			response['options'] = options;
			response['html'] = '<div id="change_due"><p>Change Due</p><p>' + response.balance_formatted + '</p><li id="confirm">Close</li></div>';
			
			status_display( 'Payment processed! Order Complete.' );
			
			//initialize and display dialog 	
			show_dialog( response );
	
			//Display change amount if due
			if( parseFloat( response.balance ) > 0 ) { 
				//if no change due display and hide 
				$('.dialog').delay(3000).dialog('close').remove();
			}
		} else {
			$('.balance_remaining, #bal_remaining').html( response.balance );
			$('.tendered_amount, #bal_paid' ).html( response.payment_tendered );
			$('#payment_amount').val('');
		}
	}
	
	exists = function( element ) {
		if( $(element).length > 0 ) {
			return true;
		}
		return false	
	}
	
	login_handler = function( message ) {
		$('#instore_login > .ins_error').html( message );	
	}
	
	error_handler = function( response ) {
		var text = $('.instruction').text();

		if( $('.product_details').children().size() > 1 ) {
			$('.product_details').children().last().fadeOut( function(e) {
				$('.instruction').text(response.status_message).fadeIn().delay(3000).fadeOut(function(e) {
					$('.product_details').children().last().fadeIn();
				});
			});
		} else {
			$('.instruction').fadeOut(function() {
				$(this).text(response.status_message).fadeIn().delay(3000).fadeOut(function(e) {
					$(this).text(text).fadeIn();
				});
			});
		}
		
		if( response.coupon_error ) {
			$('.coupon_error').text(response.coupon_error);	
		}		
	}
})(jQuery);