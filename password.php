<?php

require_once("config.php");

if(isset($_GET['id']) OR isset($_POST['id'])) {
	$db->query("UPDATE `user` SET `new_password` = NULL WHERE UNIX_TIMESTAMP(`new_password_time`) < (UNIX_TIMESTAMP(NOW())-90000)");
	if(isset($_GET['id']))
		$password = $_GET['id'];
	elseif(isset($_POST['id']))
		$password = $_POST['id'];
	$command = $db->query("SELECT `uid` FROM `user` WHERE `new_password` IS NOT NULL AND `new_password` != '' AND `new_password` = '".$db->real_escape_string($password)."'");
	
	if($command->num_rows == 0) {
		header('HTTP/1.1 401 Unauthorized');
		die();
	}
	$error = "";
	if(isset($_POST['password']) AND isset($_POST['password_verify'])) {
		if($_POST['password'] != $_POST['password_verify'])
			$error = "Beide Passwörter stimmen nicht überein";
		elseif(
			!preg_match('/[a-z]+/',$_POST['password']) OR
			!preg_match('/[A-Z]+/',$_POST['password']) OR
			!preg_match('/[0-9]+/',$_POST['password']))
			$error = "Das Passwort muss aus Groß- und Kleinbuchstaben (a-z und A-Z) und aus Ziffern bestehen. Es kann Sonderzeichen (inkl. Umlaute enthalten)";
		elseif((strlen($_POST['password'])<6) OR (strlen($_POST['password'])>32))
			$error = "Das Passwort muss 6 oder mehr Zeichen haben. Maximal 32 Zeichen sind erlaubt.";
		else {
			$db->real_query("UPDATE `user` SET `password` = '".$db->real_escape_string(password_hash($_POST['password'],PASSWORD_DEFAULT))."' WHERE `uid` = '".$db->real_escape_string($main->getUid())."'");
			$main->getHeader();
?>
<form method='post' action='https://vlv-ilmenau.de/login.php'>
	<div class='notice'>Sie k&ouml;nnen sich nun mit dem neuen Passwort einloggen.</div>
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
			die();
		}
	}
	$main->getHeader();
?>
<form method='post' action='/password.php'>
	<input type='hidden' name='id' value='<?= $password ?>' />
<?php
	if(strlen($error)) print "<div class='error'>".htmlentities($error)."</div>";
?>
	<div class='inputContainer'>
	<label for='vlvPassword'>Neues Passwort</label>
	<input type='password' pattern=".{6,32}" title='a-z,A-Z,0-9 Pflicht, Sonderzeichen erlaubt, min. 6 und max. 32 Zeichen' name='password' tabindex='1' id='vlvPassword' required autofocus />
	</div>
	<div class='inputContainer'>
	<label for='vlvPassword2'>Passwort wiederholen</label>
	<input type='password' pattern=".{6,32}" title='a-z,A-Z,0-9 Pflicht, Sonderzeichen erlaubt, min. 6 und max. 32 Zeichen' name='password_verify' tabindex='2' id='vlvPassword2' required />
	</div>
	<div class='inputContainer'>
	<input type='submit' value='speichern' tabindex='3' />
	</div>
</form>
<?php
}
elseif(isset($_POST['email'])) {
	$verify = "";
	$i = 0;
	do {
		$verify = md5($i.time().$_POST['email']);
		$command = $db->query("SELECT `uid` FROM `user` WHERE `new_password` = '".$db->real_escape_string($verify)."'");
		$i++;
	} while($command->num_rows > 0);
	$db->query("UPDATE `user` SET `new_password` = '".$db->real_escape_string($verify)."', `new_password_time` = NOW() WHERE `email` = '".$db->real_escape_string($_POST['email'])."'");
	if($db->affected_rows == 1) {
		$main->sendMail($_POST['email'],"Neues Passwort auf vlv-ilmenau.de","Hallo,\n\nEs wurde ein neues Passwort angefordert. Um das Passwort zurücksetzen zu können, folgen Sie bitte folgenden Link:\n\n\thttps://vlv-ilmenau.de/password.php?id=".urlencode($verify)."\n\nDieser Link ist 24h gültig. Sollten Sie kein neues Passwort angefordert haben, ignorieren Sie einfach diese Mail.");
	}
	$main->getHeader();
	print "<div class='notice'>Es wurde Ihnen eine Mail gesandt. Bitte klicken Sie auf den Link in der Mail.</div>";
}
else {
	$main->getHeader();
?>

<form method='post' action='/password.php'>
	<div class='inputContainer'>
	<label for='vlvEmail'>Uni-Mailadresse</label>
	<input type='text' pattern="[a-z0-9._%+-]+@([a-z0-9]+\.)?tu-ilmenau.de" title='@tu-ilmenau.de' name='email' id='vlvEmail' tabindex='1' required autofocus />
	</div>
	<div class='inputContainer'>
	<input type='submit' value='Neues Passwort anfordern' tabindex='5' />
	</div>
</form>

<?php 
}
$main->getFooter();
?>