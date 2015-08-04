<div id="mgr_override">
<form id="override" method="post">
  <span class="ins_error"></span>
  <p>Enter instore login pin to proceed</p>
  <br>
  <p>
    <label for="login_pin">Login Pin: </label>
    <input type="password" id="login_pin" />
  </p>
  <br>
  <p>
    <?php wp_nonce_field( 'instore_login_action', 'instore_login_nonce' ); ?>
    <li id="instore_login_btn" class="instore_dialog_btn" type="button" onClick="process_dialog();">Continue</li>
    <li id="cancel" class="instore_dialog_btn" type="button" onClick="close_dialog();">Cancel</li>
  </p>
</form>
<script type="text/javascript">
	(function($) {
		$(document).ready(function(e) {
            
        });
		
		process_dialog = function() {
			var data = {
				action: 'ins_ajax_request_override',
				pin: $('#login_pin').val()
			}
			
			ajax_request( data );
		}
	})(jQuery);
</script>
<style type="text/css">
	
</style>
</div>

