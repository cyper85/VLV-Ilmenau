# VLV-Ilmenau
## Alternative Ansicht des Vorlesungsverzeichnisses der TU Ilmenau

Quellcode für https://vlv-ilmenau.de ohne die config.php

### config.php

Um den Quellcode lauffähig zu machen, muss noch eine config.php im Root-Verzeichnis angelegt werden mit folgendem Inhalt:

``` php
<?php
define('AMAZON_ID','ABC');
define('SECRET_KEY','DEF');

$db = new mysqli('host','user','password','database');

session_start();

require_once("mainClass.php");
$main = new mainClass();

function singular($text) {
        $text = str_replace("Vorlesungen","Vorlesung",$text);
        $text = str_replace("Seminare","Seminar",$text);
        $text = str_replace("Hauptseminare","Hauptseminar",$text);
        $text = str_replace("Übungen","Übung",$text);
        $text = str_replace("Praktika","Praktikum",$text);
        return $text;
}
?>
```