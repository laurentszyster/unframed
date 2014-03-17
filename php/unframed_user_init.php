<?php

require 'unframed/jsbn.php';
require 'unframed/get_json.php';
require 'unframed/sql_select.php';
require 'unframed/sql_insert.php';
require 'unframed/www_invalidate.php';

/**
 * The DDL statements for the 'unframed_user_register' database 
 *
 * @return array of SQL statements
 */
function unframed_user_register_ddl() {
    $CREATE_UNFRAMED_USER_REGISTER = <<<EOD
CREATE TABLE IF NOT EXISTS 'unframed_user_register' (
        'user_installation' TEXT NOT NULL,
        'user_name' TEXT NOT NULL,
        'user_private' TEXT NOT NULL,
        'user_public' TEXT NOT NULL,
        'user_timestamp' INTEGER NOT NULL,
        PRIMARY KEY ('user_installation')
        )
EOD;
	return array($CREATE_UNFRAMED_USER_REGISTER);
}

/**
 * The DDL statements for the `unframed_user_verify` database 
 *
 * @return array of SQL statements
 */
function unframed_user_verify_ddl() {
    $CREATE_UNFRAMED_USER_VERIFY = <<<EOD
CREATE TABLE IF NOT EXISTS 'unframed_user_verify' (
    'user_installation' TEXT NOT NULL,
    'user_name' TEXT NOT NULL,
    'user_private' TEXT NOT NULL,
    'user_public' TEXT NOT NULL,
    'user_timestamp' INTEGER NOT NULL,
    'user_authorization' TEXT,
    PRIMARY KEY ('user_installation')
    )
EOD;
	return array(
		$CREATE_UNFRAMED_USER_VERIFY //, $INDEX_UNFRAMED_USER_VERIFY
		);
}

/**
 * Initialize the `unframed_user_register` and `unframed_user_verify` databases,
 * grant the `unframed` authorization to a new installation named `unframed`,
 * return a JSON message with this installation's challenge and RSA public key.
 *
 * @return {"Installation": "...", "PublicKey": ...}
 */
function unframed_user_init() {
    // create the database if they do not exist
    $pdo_register = unframed_sqlite_open('unframed_user_register.db');
    unframed_sql_declare($pdo_register, unframed_user_register_ddl());
    $pdo_verify = unframed_sqlite_open('unframed_user_verify.db');
    unframed_sql_declare($pdo_verify, unframed_user_verify_ddl());
    // generate a new challenge, a new RSA key and export its PEM string
    $challenge = unframed_jsbn_random(20);
    $keys = unframed_jsbn_rsa_new(512);
    openssl_pkey_export($keys, $privateKey);
    $publicKey = openssl_pkey_get_details($keys)['key'];
    // insert a new 'unframed' user installation
    $user = array(
        'user_installation' => $challenge,
        'user_name' => 'unframed',
        'user_private' => $privateKey,
        'user_public' => $publicKey,
        'user_timestamp' => 0, // TODO ? change to this installation time
        'user_authorization' => 'unframed'
        ); 
    unframed_sql_transaction($pdo_verify, function ($pdo) use ($user) {
        return unframed_sql_insert_values($pdo, 'unframed_user_verify', $user);
    });
    // invalidate index.html with index.php
    unframed_www_invalidate(array(), array(
        'index.html' => 'index.php'
        ), null);
    // return the Installation key and its RSA public key.
    return array(
        "Installation" => $challenge,
        "PublicKey" => unframed_jsbn_rsa_public($keys)
        );
}

unframed_get_json(function($query){
    return unframed_user_init();
});

?>