<?php if( ! defined( 'ABSPATH' ) ) exit;

global $current_user, $woocommerce;

?>
<div id="manager_override">
	<form class="instore_login" action="#" method="post">
<?php if( ! $pin = get_user_meta( $current_user->ID, 'instore_pin', true ) && current_user_can( 'manage_instore' ) ) { ?>
		<p>No manager pin created for this user. Enter wordpress password to continue.</p>
        <p><label for="password_check">Login Pin: </label><input type="password" id="password_check" /></p>
<?php } else { ?>
		<p>Enter Instore Pin for Manager Override</p>
        <p><label for="instore_login_pin">Login Pin: </label><input type="password" id="instore_login_pin" /></p>
<?php }?> 
		<p>
           <li class="instore_login_btn login_btn">Login</li>
           <li class="instore_exit_btn login_btn">Exit</li>
        <p>
    </form>
</div>