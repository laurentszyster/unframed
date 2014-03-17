<?php

/* 

Associate the given name with a new RSA pair of keys in the `unframed.db` SQLite database, 
returns the public key and a challenge.

	POST { "Name": "..." } -> { 
		"PublicKey": {
			"modulus": "3BE4...", 
			"exponent": "10001"
			},
		"Challenge": "..." 
		}

See also: unframed_user_authorize.php and unframed_user_verify.php

*/

require 'unframed/jsbn.php';
require 'unframed/post_json.php';
require 'unframed/sql_insert.php';

unframed_post_json(function ($request) {
	$name = unframed_request_string($request, 'Name');
	// generate a new challenge, a new RSA key and export its PEM string
	$challenge = unframed_jsbn_random(20);
    $keys = unframed_jsbn_rsa_new(512);
    openssl_pkey_export($keys, $privateKey);
    $publicKey = openssl_pkey_get_details($keys)['key'];
	// register a new user in the default unframed database (create a database 
	// if it does not exists) using the first challenge as session identifier.
	$user = array(
		'user_installation' => $challenge,
		'user_name' => $name,
        'user_private' => $privateKey,
        'user_public' => $publicKey,
		'user_timestamp' => $_SERVER['REQUEST_TIME']
		);
    $pdo = unframed_sqlite_open('unframed_user_register.db');
	$fun = function ($pdo) use ($user) {
		return unframed_sql_insert_values($pdo, 'unframed_user_register', $user);
	};
	$inserted = unframed_sql_transaction($pdo, $fun);
	// update this session's state with the new challenge
	session_start();
	$_SESSION['unframed_user_challenge'] = $challenge;
	session_write_close();
	// return the RSA public key and the challenge
	return array(
		"PublicKey" => unframed_jsbn_rsa_public($keys),
		"Challenge" => $challenge,
		"Name" => $name
		);
}, 4096, 2);

?>