<?php

function tcb_roster_public_login_local(){

	ob_start();

	echo '<div class="tcb_login_local">';

	$args = array(
		'echo'                => true,
		'redirect'            => admin_url(),
		'remember'            => true,
		'value_remember'      => true,
		'required_username'   => true,
		'required_password'   => true,
		'value_remember'      => true,
	);
	
	wp_login_form( $args );

	echo '</div>';

	return ob_get_clean();
}