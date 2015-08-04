(function($){
	$(window).load(function(){
		$('.chosen-select').chosen();
		$('.instore-devices .insert').click(function() {
			var $tbody = $('.instore-devices').find('tbody');
			var index = $tbody.find('tr').size();
			var code = '<tr class="new">\
				<td class="check-column"><input type="checkbox" /></td>\
				<td><input type="text" name="device_name[' + index + ']" /></td>\
				<td><input type="text" name="device_mac_id[' + index + ']" /></td>\
				<td><input type="checkbox" name="cash_drawer[' + index + ']" /></td>\
				<td><input type="checkbox" name="receipt_printer[' + index + ']" /></td>\
				<td><input type="checkbox" name="barcode_scanner[' + index + ']" /></td>\
			</tr>';
				
			$tbody.append( code );
		
			return false;
		});
			
		$('.instore-devices .remove').click(function() {
			var $tbody = $('.instore-devices').find('tbody');
				
			$tbody.find('.check-column input:checked').each(function() {
				$(this).closest('tr').hide().find('input').val('');
			});
				
			return false;
		});
	});
})(jQuery);
