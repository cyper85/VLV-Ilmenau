<?php

require_once("config.php");
$main->login();
$main->getHeader();
?>

<form method='post' action='/login.php'>
<?php if ($main->getLoginError()) { ?>
<div class='error'>Falsche Login-Daten</div>
<?php } ?>
	<div class='inputContainer'>
	<label for='vlvUser'>Nutzername</label>
	<input type='text' name='user' id='vlvUser' tabindex='1' autofocus required />
	</div>
	<div class='inputContainer'>
	<label for='vlvPassword'>Passwort</label>
	<input type='password' name='password' tabindex='2' id='vlvPassword' required />
	</div>
	<div class='inputContainer'>
	<input type='submit' value='login' tabindex='3' /> <a href='password.php' class='button red'>Passwort vergessen</a>
	</div>
</form>
<?php 
$main->getFooter();
?>