<?php

require_once("config.php");
$main->verify();
$main->getHeader();
?>

<h2>Ups...</h2>
<p>Da ist grad was schief gelaufen. Hast du deinen Account eventuell bereits aktiviert? Versuche dich <a href='/login.php'>einzuloggen</a>!</p>

<?php 
$main->getFooter();
?>