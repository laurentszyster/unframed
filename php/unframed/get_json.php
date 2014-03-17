<?php 

require_once 'unframed/Unframed.php';

/**
 * Returns the query parameters of a GET request as an array.
 *
 * @return array
 *
 * @throws Unframed
 */
function unframed_get_query() {
	if ($_SERVER['REQUEST_METHOD']!=='GET') {
		throw new Unframed('Method Not Allowed', 405);
	} else {
		return $_GET;
	}
}

/**
 * Set the appropriate HTTP response headers, let PHP set the HTTP response code and send a 
 * JSON response body. Note that if 'application/json' is not in the $_SERVER['HTTP_ACCEPT']
 * the JSON will be pretty printed.
 *
 * @param array $json the JSON response
 * @param int $options passed to json_encode
 *
 * @return void
 *
 * @throws Unframed
 */
function unframed_ok_json($json, $options=0) {
	$accept = $_SERVER['HTTP_ACCEPT'];
	if (preg_match('/application.json/i', $accept) < 1) {
		$options = $options | JSON_PRETTY_PRINT;
	}
	$body = json_encode($json, $options);
	if (is_string($body)) {
		header('Content-Type: application/json');
		echo $body, "\n";
	} else {
		throw new Unframed(json_last_error_msg());
	}
}

/**
 *
 */
function unframed_error_json($e) {
	http_response_code($e->getCode());
	$json = array('Error' => $e->getMessage());
	$body = json_encode($json, JSON_PRETTY_PRINT);
	header('Content-Type: application/json');
	echo $body, "\n";
}

/**
 * Apply a $fun that handles the parsed query string of a GET request and returns
 * an array that will be sent as a JSON body in the HTTP response, catch any Unframed
 * exception, reply with an error code and a JSON error message.
 */
function unframed_get_json($fun) {
	try {
		unframed_ok_json($fun(unframed_get_query()));
	} catch (Unframed $e) {
		unframed_error_json($e);
	}
}

?>