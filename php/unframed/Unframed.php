<?php
/**
 * Unframed's very core: to require a minimum of configuration and fail fast to HTTP.
 *
 * @author Laurent Szyster
 */

require_once 'unframed/configuration.php';

/**
 * Unframed extends Exception
 *
 * Unframed exceptions are throwed to fail fast and send the exception code as HTTP response.
 * 
 */
class Unframed extends Exception {
    public function __construct($message, $code=500, Exception $previous=null) {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Get the version of unframed sources.
 *
 * @return string the version of unframed sources
 *
 */
function unframed_version () {
    return '0.0.1'; 
}

/**
 * Apply a $fun or send an HTTP reply using the catched Unframed exception.
 *
 * @param function $fun the function to apply
 *
 * @return the return value of the function applied or the throwed exception.
 *
 */
function unframed_apply($fun) {
	try {
		return $fun();
	} catch (Unframed $e) {
		http_response_code($e->getCode());
		echo $e->getMessage(), "\n";
	}
}

?>