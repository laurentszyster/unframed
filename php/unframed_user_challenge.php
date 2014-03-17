<?php 

// GET -> { "Challenge": "..." }

require 'unframed/get_json.php';

unframed_get_json(function($query) {
	$response = array();
	session_start();
	if (!isset($_SESSION['unframed_user_challenge'])) {
		$challenge = strtoupper(bin2hex(openssl_random_pseudo_bytes(20)));
	    $_SESSION['unframed_user_challenge'] = $challenge;
	    $response['Challenge'] = $challenge;
		session_write_close();
	} else {
	    $response['Challenge'] = $_SESSION['unframed_user_challenge'];
	}
	return $response;
});

?>