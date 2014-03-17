<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>unframed</title>
<style type="text/css">

input[type=text], textarea {width: 100%}

</style>
</head>
<body>
<header>
	<h1>unframed</h1>
	<form id="unframed_user">
		<select id="unframed_user_names"></select>
		<button id="unframed_user_login" type="button">Login</button>
		<button id="unframed_user_logout" type="button" style="display: none;" disabled >Logout</button>
	</form>
</header>
<section>
	<form id="unframed_user_authorize" style="display: none;">
		<h2>Authorize</h2>
		<input id="unframed_user_name" type="text" placeholder="user name" />
		<input id="unframed_user_authorization" type="text" placeholder="New authorization" />
		<table>
			<thead>
				<tr>
					<td><input type="checkbox"/></td>
					<td>Name</td>
					<td>Session</td>
					<td>Timestamp</td>
					<td>User Agent</td>
				<tr>
			</thead>
			<tbody id="unframed_user_registered">
				<tr>
					<td><input type="checkbox"/></td>
					<td>unframed</td>
					<td>...</td>
					<td>...</td>
					<td>...</td>
				<tr>
			</tbody>
		</table>
		<button type="button">Authorize</button>
	</form>
</section>
<script type='text/javascript' src='jsbn.min.js'></script>
<script type='text/javascript' src='unframed.js'></script>
<script type='text/javascript' src='qsa.js'></script>
<script type='text/javascript'>

var app = unframed('unframed_user');

<?php 
require 'unframed/Unframed.php';
session_start();
$challenge = $_SESSION['unframed_user_challenge'];
if (isset($challenge)) {
	echo("app.Challenge = ".json_encode($challenge).";");
}
?>

function pass () {}

function unframed_user_verify (name, challenge) {
	var install = app.loadJson('Users/'+name),
		key = jsbn.rsa.setPublic(install.PublicKey);
	app.xhrPostJson('/unframed_user_verify.php', {
		"Installation": install.Installation,
		"Signature": key.encrypt(challenge),
		"Logout": app.Logout
	});
}

function unframed_user_login (evt) {
	this.disabled = true;
	app.Logout = false;
	if (app.Challenge === undefined) {
		app.xhrGetJson('/unframed_user_challenge.php');
	} else {
		unframed_user_verify(app.UserName, app.Challenge);
	}
}
function unframed_user_logout (evt) {
	this.disabled = true;
	app.Logout = true;
	unframed_user_verify(app.UserName, app.Challenge);
}

app.link('DOM Ready', function() {
	var sel = document.getElementById('unframed_user_names'),
		usernames = app.loadJson('usersnames');
	sel.innerHTML = usernames.map(function(name){
		return '<option value="'+name+'">'+name+'</option>';
	}).join('');
	app.UserName = sel.options[sel.selectedIndex].value;
	qsa('#unframed_user_names').on('change', function (){
		app.UserName = this.options[this.selectedIndex].value;
	}).enable();
	qsa('#unframed_user_login')
		.on('click', unframed_user_login)
		.enable()
		.elements[0].textContent = (app.Challenge === undefined ? 'Login': 'Verify');
	qsa('#unframed_user_logout')
		.on('click', unframed_user_logout);
	qsa.focus('unframed_user_login');
});

app.link('200 GET /unframed_user_challenge.php', function (response) {
	unframed_user_verify(app.UserName, response['Challenge']);
});
app.link('200 POST /unframed_user_verify.php', function (response) {
	if (app.Logout) {
		app.Challenge = undefined;
		app.emit('Logged Out');
	} else {
		app.Challenge = response['Challenge'];
		app.emit('Logged In');
	}
});
app.link('Logged In', function () {
	qsa('#unframed_user_names').disable();
	qsa('#unframed_user_login').enable()
		.elements[0].textContent = "Verify";
	qsa('#unframed_user_logout').enable().show();
	qsa('#unframed_user_authorize').show();
	qsa.focus('unframed_user_logout');
});
app.link('Logged Out', function () {
	qsa('#unframed_user_logout').hide();
	qsa('#unframed_user_authorize').hide();
	qsa('#unframed_user_names').enable();
	qsa('#unframed_user_login').enable()
		.elements[0].textContent = "Login";
	qsa.focus('unframed_user_login');
});
app.link('DOM Unload', pass);

</script> 
</body>
</html>