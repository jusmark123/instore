<?php if( ! defined( 'ABSPATH' ) ) exit; //exit if accessed directly 
get_header(); ?>

<div id="content">
  <div class="instore_security">
    <div class="instore_logo"> <img src="<?php echo esc_attr( Instore()->plugin_url() . '/images/instore-logo.png'); ?>" /> </div>
    <form id="instore_login">
      <?php wp_nonce_field( 'instore_login_action', 'instore_login_nonce' ); ?>
      <span class="ins_error"></span>
      <p>In-Store console locked.<br />
        Please enter In-Store login pin to unlock.</p>
      <p>
        <label for="instore_login_pin">Login Pin: </label>
        <input type="password" id="instore_login_pin" required="required"/>
      </p>
      <p>
        <input type="submit" class="instore_login_btn" id="instore_login_btn" value="Login"  />
        <input type="button" class="instore_login_btn" id="instore_exit" value="Exit" />
      </p>
    </form>
  </div>
  <script type="text/javascript">
  (function($) {
	  $(document).ready(function(e) {
  		$('.instore_login_btn').on('click', function(e) {
			e.preventDefault();
			if( $(this).attr('id') == 'instore_exit') {
				data = { action: 'ins_ajax_instore_redirect'};
				
				ajax_request( data );
				
			} else {
				if( $('#instore_login_pin').val() ) {
					var data = {
						action:   'ins_ajax_instore_security',
						pin:	  $('#instore_login_pin').val(),
						instore_login_nonce: $('#instore_login_nonce').val(),
					}
				} else {
					$('#instore_login_pin').css('border','1px solid red');
					$('.ins_error').text('Enter login pin to proceed').css('color','red');	
				}
			}	
			ajax_request( data );
		}).button();
	  });
  })(jQuery);
  </script> 
</div>
<?php get_footer();

