<?php 

require_once 'unframed/get_json.php';

/**
 * Returns the JSON body of $maxLength bytes from a POST request, decoded as an array of $maxDepth
 * or throw an exception.
 *
 * @param int $maxLength the maximum length of the request JSON body
 * @param int $maxDepth the maximum depth of the request JSON object
 * @param int $options passed to json_decode
 *
 * Note that the JSON_BIGINT_AS_STRING option is allways set for json_decode.
 *
 * @return array
 */
function unframed_post_json_body($maxLength=16384, $maxDepth=512, $options=0) {
	if ($_SERVER['REQUEST_METHOD']!=='POST') {
		throw new Unframed('Method Not Allowed', 405);
	}
	$body = file_get_contents('php://input', NULL, NULL, NULL, $maxLength);
	$json = json_decode($body, true, $maxDepth, $options|JSON_BIGINT_AS_STRING);
	if ($json === NULL) {
		throw new Unframed(json_last_error_msg(), 400);
	} else {
		return $json;
	}
}

/**
 * Apply a $fun that handles the parsed JSON body of a POST request and returns
 * an array that will be sent as a JSON body in the HTTP response.
 *
 * @param function $fun the function to apply
 * @param int $maxLength the maximum length of the request JSON body
 * @param int $maxDepth the maximum depth of the request JSON object
 *
 * @return void or an exception
 */
function unframed_post_json($fun, $maxLength=16384, $maxDepth=512) {
	try {
		unframed_ok_json($fun(unframed_post_json_body($maxLength, $maxDepth)));
	} catch (Unframed $e) {
		unframed_error_json($e);
	}
}

function unframed_request_property($request, $key, $default=NULL) {
	$value = $request[$key];
	if (isset($value)) {
		return $value;
	}
	if ($default===NULL) {
		throw new Unframed('Name Error - $key missing');
	}
	return $default;
}

function unframed_request_string($request, $key, $default=NULL) {
	$value = unframed_request_property($request, $key, $default);
	if (!is_string($value)) {
		throw new Unframed('Type Error - $key must be a String');
	}
	return $value;
}

function unframed_request_int($request, $key, $default=NULL) {
	$value = unframed_request_property($request, $key, $default);
	if (!is_int($value)) {
		throw new Unframed('Type Error - $key must be an Integer');
	}
	return $value;
}

function unframed_request_float($request, $key, $default=NULL) {
	$value = unframed_request_property($request, $key, $default);
	if (!is_float($value)) {
		throw new Unframed('Type Error - $key must be an Float');
	}
	return $value;
}

function unframed_request_bool($request, $key, $default=NULL) {
	$value = unframed_request_property($request, $key, $default);
	if (!is_bool($value)) {
		throw new Unframed('Type Error - $key must be an Boolean');
	}
	return $value;
}

function unframed_request_array($request, $key, $default=NULL) {
	$value = unframed_request_property($request, $key, $default);
	if (!is_array($value)) {
		throw new Unframed('Type Error - $key must be an Array');
	}
	return $value;
}

?>