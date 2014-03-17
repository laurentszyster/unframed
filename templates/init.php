<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>unframed</title>
</head>
<body>
<h1>unframed</h1>
<form>
	<button id="unframed_user_init" type="button" disabled >Initialize</button>
</form>
<script type='text/javascript'>
<?php 
function echo_file($name) {
	echo file_get_contents($name);
}
echo_file('../deps/fragment-min.js');
echo_file('../deps/unframed-min.js');
?>
</script>
<script type='text/javascript'>

var app = unframed('unframed_user'),
	button = gebi('unframed_user_init');
function pass () {}
function unframed_user_init (evt) {
	button.disabled = true;
	app.xhrGetJson('/unframed_user_init.php');
}
app.link('DOM Ready', function() {
	qsa(button).on('click', unframed_user_init).enable();
	button.focus();
});
app.link('200 GET /unframed_user_init.php', function (response) {
	app.saveJson('Users/unframed', response);
	app.saveJson('usersnames', ['unframed']);
	button.textContent = 'Initialized');
	// document.location = '/unframed_user_admin.php';
});
app.link('DOM Unload', pass);

</script> 
</body>
</html>