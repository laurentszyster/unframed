<?php 

/*

Verify a user's signature, returns a new challenge on succes or an error on failure.

    POST { "Name": "Bob", "Signature": "..." } 
        -> { "Challenge": "..." } | { "Error": "..." }

*/

require 'unframed/post_json.php';
require 'unframed/sql_select.php';
require 'unframed/jsbn.php';

unframed_post_json(function($request) {
    $installation = unframed_request_string($request, 'Installation'); 
    $signature = unframed_request_string($request, 'Signature');
    $logout = unframed_request_bool($request, 'Logout', FALSE);
    session_start();
    $challenge = $_SESSION['unframed_user_challenge'];
    if (!isset($challenge)) {
        throw new Unframed('PHP session not challenged yet');
    }
    try {
        $pdo = unframed_sql_open('sqlite:../sql/unframed_user_verify.db');
        $user = unframed_sql_select_object(
        	$pdo, 'unframed_user_verify', 'user_installation', $installation
        	);
    } catch (PDOException $e) {
        throw new Unframed($e->getMessage());
    }
    $privateKey = openssl_pkey_get_private($user['user_private']);
    /* 
    
    $publicKey = openssl_pkey_get_public($user['user_public']);
    if ($publicKey !== FALSE) {
        openssl_public_encrypt($challenge, $crypted, $publicKey);
        unframed_debug('crypted', bin2hex($crypted));
        if (openssl_private_decrypt($crypted, $cleartext, $privateKey)) {
            unframed_debug('cleartext', $cleartext);
        }
    }

    */
    if ($privateKey != FALSE) {
        if (openssl_private_decrypt(hex2bin($signature), $cleartext, $privateKey)) {
            if ($cleartext == $challenge) {
                $response = array();
                if ($logout) {
                    session_destroy();
                    $response['Logout'] = TRUE;
                } else {
                    $challenge = unframed_jsbn_random(20);
                    $_SESSION['unframed_user_name'] = $user['user_name'];
                    $_SESSION['unframed_user_authorization'] = $user['user_authorization'];
                    $_SESSION['unframed_user_challenge'] = $challenge;
                    $response["Challenge"] = $challenge;
                }
                session_write_close();
                return $response;
            } else {
                throw new Unframed('Forbidden - invalid Signature "'.$signature.'"', 403);
            }
        } else {
            throw new Unframed('OpenSSL error - could not decrypt "'.$signature.'"', 403);
        }
    } else {
        throw new Unframed('OpenSSL error - invalid private key "'.$user['user_private'].'"', 403);
    }
}, 1024, 2);

?>