<?php

session_start();
unset($_SESSION['uid']);
unset($_SESSION['uname']);

if(isset($_SERVER['HTTPS']))
	header('Location: https://vlv-ilmenau.de');
else
	header('Location: http://vlv-ilmenau.de');