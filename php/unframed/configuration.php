<?php

/**
 * Let the script run through after its input is closed.
 *
 * This ensure that network timeouts don't prevent longer running 
 * processes like static resources generation or interrupt
 * database transactions without a rollback.
 */
ignore_user_abort(TRUE);

// shims go here, you can add yours ...

require_once 'unframed/php54.php';

/**
 * Unframed's application name
 *
 * @return 'unframed'
 */
function unframed_app_name() {
	return 'unframed';
}

/**
 * Prefix a relative path with the application path ('../').
 *
 * @param string $filename to prefix
 *
 * @return string 
 */
function unframed_app_path($filename) {
	return '../'.$filename;
}

/**
 * Prefix a relative path with the application's web resources path ('../www/').
 *
 * @param string $filename to prefix
 *
 * @return string 
 */
function unframed_www_path($filename) {
	return '../www/'.$filename;
}

/**
 * Prefix a relative path with the application's SQLite databases path ('../sql/').
 *
 * @param string $filename to prefix
 *
 * @return string 
 */
function unframed_sql_path($filename) {
	return '../sql/'.$filename;
}

/**
 * Prefix a relative path and return an absolute path, by default use the
 * system's temporary directory (on Debian that would be '/tmp/').
 *
 * @param string $filename to prefix
 *
 * @return string 
 */
function unframed_tmp_path($filename) {
	return sys_get_temp_dir().'/'.$filename;
}

function unframed_debug($message, $value) {
    return error_log($message.' - '.var_export($value, true));
}

?>