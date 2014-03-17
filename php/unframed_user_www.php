<?php 

/*

The entry point of unframed's application.

```
POST { 
	"Table": "Table", 
	"Primary": "Column", 
	"Values": { "Column": "Value" },
	"Resources": { "Resource": "Template" } 
	} -> { "rowCount": 1, "errors": { "Resource": "" } }

Replace values in a table, eventually create that table if it does not
exists in the user's database, apply one or more templates to replace
resources in the user's path.

Note that scalar values will be serialized as JSON for the database.

Synopsis
---

Login as user 'blog'.

Then do:

```
POST /unframed_user_replace.php { 
	"Table": "author",
	"Values": {
		"author": "Laurent Szyster",
		},
	"Resources": {
		"author/Laurent Szyster.json": "json"
		}
	} -> 1

A new resource is now available : 

```
GET /blog/author/Laurent%20Szyster.json -> {
	"author": "Laurent Szyster"
}

Rince and repeat.

```
POST /unframed_user_replace.php { 
	"Table": "article",
	"Values": {
		"article": 0,
		"author": "Laurent Szyster",
		"title": "Hello ...",
		"body": "... World!",
		"date": "today",
		"tags": ["about"]
		},
	"Resources": {
		"article/0.json": "json",
		"article/0.html": "blog/article",
		"index.html": "blog/index"
		}
	} -> 1

```
GET /blog/article/0.json -> {
	"article": 0,
	"author": "Laurent Szyster",
	"title": "Hello ...",
	"body": "... World!",
	"date": "today",
	"tags": ["about"]
	}

Note that the user control which resources is invalidated with what
template. Because templates have side-effects and they are sorted by
resource path before beeing applied.

*/

require 'unframed/post_json.php';
require 'unframed/sql_post.php';
require 'unframed/www_invalidate.php';

unframed_post_json(function($request) {
	session_start();
	if (!isset($_SESSION['unframed_user_name'])) {
		throw new Unframed('Forbidden', 403);
	}
	$username = $_SESSION['unframed_user_name'];
	$database = $username.'.db';
	$table = unframed_request_string($request, 'Table');
	$primary = unframed_request_string($request, 'Primary', $table);
	$values = unframed_request_array($request, 'Values');
	$key = $values[$primary];
	if (!isset($key)) {
		throw new Unframed('missing '.$primary.' value, unable to replace in '.$table);
	}
	$resources = unframed_request_array($request, 'Resources', array());
	if (count($resources) > 0) {
		$resourcePath = unframed_www_path($username);
		if (!is_dir($resourcePath)) {
			if (!mkdir($resourcePath, 0777)) {
				throw new Unframed('Unable to create the user resource path '.$resourcePath);
			};
		}
		$routes = array();
		foreach ($resources as $resource => $template) {
			$templateFilename = 'templates/'.$template.'.php';
			if (is_file($templatePath)) {
				$routes[$resourcePath.'/'.$resource] = $templateFilename;
			}
		}
		$fun = function($pdo) use ($table, $primary, $values, $routes) {
			if (unframed_sql_post($pdo, $table, $primary, $values) == 1) {
				return array(
					"Replace" => TRUE,
					"Invalidate" => unframed_www_invalidate($values, $routes, $pdo)
				);
			} else {
				return array("Replace" => FALSE);
			}
		}
	} else {
		$fun = function($pdo) use ($table, $primary, $values) {
			return array(
				"Replace" => (unframed_sql_post($pdo, $table, $primary, $values) == 1)
				);
		}
	}
	$pdo = unframed_sqlite_open($database);
	return unframed_sql_transaction($pdo, $fun);
});

?>