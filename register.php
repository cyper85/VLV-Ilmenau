<?php

require_once("config.php");
$main->register();
$main->getHeader();
?>

<form method='post' action='/register.php'>
	<div class='inputContainer'>
<?php if ($main->getRegisterErrorUser()) { ?>
<div class='error'><?php echo htmlentities($main->getRegisterErrorUser());?></div>
<?php } ?>
	<label for='vlvUser'>Nutzername</label>
	<input type='text' name='user' pattern='[a-zA-Z0-9-_]{4,50}' title='Erlaubte Zeichen: a-z, A-Z, 0-9; min. 4 und max. 50 Zeichen' id='vlvUser' tabindex='1' autofocus required />
	</div>
<?php if ($main->getRegisterErrorEmail()) { ?>
<div class='error'><?php echo htmlentities($main->getRegisterErrorEmail());?></div>
<?php } ?>
	<div class='inputContainer'>
	<label for='vlvEmail'>Uni-Mailadresse</label>
	<input type='text' pattern="[a-z0-9._%+-]+@([a-z0-9]+\.)?tu-ilmenau.de" title='@tu-ilmenau.de' name='email' id='vlvEmail' tabindex='2' required />
	</div>
<?php if ($main->getRegisterErrorPasswort()) { ?>
<div class='error'><?php echo htmlentities($main->getRegisterErrorPasswort());?></div>
<?php } ?>
	<div class='inputContainer'>
	<label for='vlvPassword'>Passwort</label>
	<input type='password' pattern=".{6,32}" title='a-z,A-Z,0-9 Pflicht, Sonderzeichen erlaubt, min. 6 und max. 32 Zeichen' name='password' tabindex='3' id='vlvPassword' required />
	</div>
	<div class='inputContainer'>
	<label for='vlvPassword2'>Passwort wiederholen</label>
	<input type='password' pattern=".{6,32}" title='a-z,A-Z,0-9 Pflicht, Sonderzeichen erlaubt, min. 6 und max. 32 Zeichen' name='password_verify' tabindex='4' id='vlvPassword2' required />
	</div>
	<div class='inputContainer'>
	<input type='submit' value='registrieren' tabindex='5' />
	</div>
</form>


<script>
	
	$(function() {
		
	});
</script>
<?php 
$main->getFooter();
?>