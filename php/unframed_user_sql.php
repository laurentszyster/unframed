<?php 

/*

POST { "Statement": "SELECT ...", "Parameters": {...}|[...] } -> {
	"fetchAll": [{...}]
	}

POST { "Statement": "UPDATE | DELETE | INSERT ...", "Parameters": {...}|[...] } -> {
	"rowCount": ...
}

POST { "Statement": "CREATE | ALTER | DROP ...", "Parameters": {...}|[...] } -> []

*/

require 'unframed/post_json.php';
require 'unframed/sql_transaction.php';

unframed_post_json(function($request) {
	session_start();
	if (!isset($_SESSION['unframed_user_name'])) {
		throw new Unframed('Forbidden', 403);
	}
	$username = $_SESSION['unframed_user_name'];
	$statement = unframed_request_string($request, 'Statement');
	$parameters = unframed_request_array($request, 'Parameters');
	if ($username == "unframed") { // enable
		$dsn = 'sqlite:../sql/'.unframed_request_string($request, 'Database', $username).'.db';
	} else {
		$dsn = 'sqlite:../sql/'.$database.'.db';
	}
	$fun = function ($pdo) use ($statement, $parameters) {
		$st =  $pdo->prepare($statement);
		if ($st->execute($parameters)) {
			if (preg_match('/^select/i', $statement)>0) {
				return array("fetchAll"=>$st->fetchAll());
			} elseif (preg_match('/^(insert|replace|update|delete)/i', $statement)>0) {
				return array("rowCount"=>$st->rowCount());
			}
			return array();
		}
		throw new Unframed($st->errorInfo()[2]);
	}
	$pdo = unframed_sql_open($dsn);
	return unframed_sql_transaction($pdo, $fun);
});

?>