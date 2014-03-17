<?php

/*

	POST { "Installation": "...", "Authorization": "..." } -> {}

*/

require 'unframed/post_json.php';
require 'unframed/sql_select.php';
require 'unframed/sql_insert.php';

unframed_post_json(function($request){
	session_start();
	if (!($_SESSION['unframed_user_name']=='unframed')) {
		throw new Unframed('Forbidden', 403);
	}
	//
	$installation = unframed_request_string($request, 'Installation');
	$authorization = unframed_request_string($request, 'Authorization', '');
	//
    $pdo_register = unframed_sqlite_open('unframed_user_register.db');
    $user = unframed_sql_select_object(
    	$pdo_register, 'unframed_user_register', 'user_installation', $installation
    	);
    //
    $user['user_authorization'] = $authorization;
    $pdo_verify = unframed_sqlite_open('unframed_user_verify.db');
    unframed_sql_transaction($pdo_verify, function($pdo) use ($user) {
    	unframed_sql_replace_values($pdo, 'unframed_user_verify', $user)
    });
	//
	return array("Authorized"=>TRUE);
});

?>