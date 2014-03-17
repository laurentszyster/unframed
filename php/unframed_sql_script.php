<?php 

/*

	POST { 
		"Database": {"dsn": "sqlite:/var/tmp/unframed.db", "username": null, "password": null }, 
		"Statements": [["SELECT ...", [...]]]
		 } -> [[...]]

*/

require 'unframed/post_json.php';
require 'unframed/sql_script.php';

unframed_post_json(function($request) {
	session_start();
	$username = $_SESSION['unframed_user_name'];
	if (!($username=='unframed')) {
		throw new Unframed('Forbidden', 403);
	}
	$database = unframed_request_array($request, 'Database', array(
		'dsn' => 'sqlite:'.unframed_sql_path('unframed.db'), 
		'username' => NULL, 
		'password' => NULL
		));
	}
	$statements = unframed_request_array($request, 'Statements', array());
	return unframed_sql_script($database, $statements);
}, 16384, 5);

?>