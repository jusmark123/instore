(function($) {
	$(document).ready(function(e) {
       	var window_height = $(window).height(); 
	   	$html = $('.order_detail').parent().html();
	  
	 	if( $('#wpadminbar').length > 0 ) {
		  window_height -= $('#wpadminbar').height();
		}
	   
	   $('body').css('height', window_height );
	   $('.order_detail').parents().css( 'height', window_height );
	   $('header').remove(); 
	   $('footer').remove();
	   
	   $('.chosen_select').chosen({width:'200px'}); 
	});
})(jQuery);