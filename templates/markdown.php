<?php

include '../deps/parsedown/Parsedown.php';

$parsedown = new Parsedown();
$source = $unframed_resource['Markdown'];
if (is_string($source)) {
	echo $parsedown->parse($source);
} else {
	throw new Unframed('Missing Markdown source for '.$unframed_path);
}

?>