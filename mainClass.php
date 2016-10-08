<?php

class mainClass {
    private $db;
    public function __construct() {
        global $db;
        $this->db = &$db;
    }

    private $title = "";
    public function setTitle($t) {
        $this->title = htmlentities(" / ".$t);
    }

    public function getHeader() {
        $url = urldecode(str_replace("?_escaped_fragment_=","#!",filter_input(INPUT_SERVER,'REQUEST_URI')));
    ?><!DOCTYPE html>
<html lang='de'>
    <head>
            <meta charset="UTF-8" />

            <title>Mein Vorlesungsverzeichnis Ilmenau<?=$this->title;?></title>
            <meta name="description" content="Personalisiertes Vorlesungsverzeichnis der Technischen Universität Ilmenau mit iCal-Export" />
            <meta name="author" content="Andreas Neumann" />
            <meta name="creator" content="Andreas Neumann" />
            <meta name="keywords" content="Vorlesungsverzeichnis VLV TU Technische Universität Technical University Ilmenau iCal ics iCalendar Export" />

            <!-- Webmaster Tools -->
            <meta name="google-site-verification" content="AajqEQuXL6oyLpV4NwqC5aB_-Wh02hreEt2b-mTr6B0" />
            <meta name="msvalidate.01" content="5DA47D975F5D2CAD264BA3503895CA2B" />
            <meta name='yandex-verification' content='6bd195e5752544ec' />
<?php
            if (
                (\filter_input(INPUT_SERVER, 'SCRIPT_NAME') === "/settings.php") OR ( \filter_input(INPUT_SERVER, 'SCRIPT_NAME') === "/login.php") OR ( \filter_input(INPUT_SERVER, 'SCRIPT_NAME') === "/register.php") OR ( \filter_input(INPUT_SERVER, 'SCRIPT_NAME') === "/literatur.php")
        ) {
            print "\t\t<meta name='robots' content='noindex,follow'>\n";
        }
        ?>
            <!-- Icons -->
            <!--[if IE]><link rel="shortcut icon" href="img/favicon.ico" /><![endif]-->
            <link rel="icon" href="img/logo96.png" />
            <link rel="apple-touch-icon-precomposed" href="img/logo152.png" />
            <meta name="msapplication-config" content="browserconfig.xml" />

            <!-- Application -->
            <meta name="application-name" content="Mein Vorlesungsverzeichnis Ilmenau" />

            <!-- Social Media -->
            <link rel="canonical" href="http://vlv-ilmenau.de<?=$url?>" />
            <meta name="og:type" content="website" />
            <meta property="og:locale" content="de_DE" />
            <meta property="og:site_name" content="Mein Vorlesungsverzeichnis Ilmenau" />
            <meta property="og:title" content="<?=preg_replace('/^\s*[\/]\s*/','',$this->title);?>" />
            <meta property="og:url" content="http://vlv-ilmenau.de<?=$url?>" />
            <meta property="og:image" content="http://vlv-ilmenau.de/img/logo270.png" />
            <meta property="og:description" content="Personalisiertes Vorlesungsverzeichnis der Technischen Universität Ilmenau mit iCal-Export" />

            <meta name="twitter:card" content="summary" />
            <meta name="twitter:creator" content="@nunAmen" />
            <meta name="twitter:title" content="Mein Vorlesungsverzeichnis Ilmenau<?=$this->title;?>" />
            <meta name="twitter:description" content="Personalisiertes Vorlesungsverzeichnis der Technischen Universität Ilmenau mit iCal-Export." />
            <meta name="twitter:image" content="http://vlv-ilmenau.de/img/logo270.png" />
            <meta name="twitter:url" content="http://vlv-ilmenau.de<?=$url?>" />
            <meta property="og:locale"	content="de_DE" />
            <meta property="fb:admins"	content="andreas.neumann.731" />
            <link rel="author" 		href="https://plus.google.com/+AndreasNeumannIlmenau"/>

            <script src='js/jquery-1.11.1.min.js' ></script>
            <script src='js/editable.js' ></script>
            <script src='js/cookies.js' ></script>
            <link href="css/screen.css" type="text/css" rel="stylesheet" />
            <link href="css/style.css" type="text/css" rel="stylesheet" />
            <link href="css/font-awesome/css/font-awesome.css" type="text/css" rel="stylesheet" />
            <script>
<?php foreach($this->js as $js) echo $js."\n"; ?>
            </script>
    </head>
    <body>
            <div class="container" id="page">
                    <div id="header">
                            <div id="logo">Mein Vorlesungsverzeichnis Ilmenau</div>
                    </div><!-- header -->


                            <div id="mainmenu">
                                    <ul id="yw0">
                                            <li<?php if(\filter_input(INPUT_SERVER,'SCRIPT_NAME') === "/index.php") { echo " class='active'"; } ?>><a href="/">Vorlesungsverzeichnis</a></li>
<?php if($this->getUser()) { ?>
                                            <li<?php if(\filter_input(INPUT_SERVER,'SCRIPT_NAME') === "/settings.php") { echo " class='active'"; } ?>><a href="settings.php">Einstellungen</a></li>
<?php if($this->isSu()): ?>			<li<?php if(\filter_input(INPUT_SERVER,'SCRIPT_NAME') === "/literatur.php") { echo " class='active'"; } ?>><a href="literatur.php">Literatur</a></li> <?php endif; ?>						
                                            <li><a href="logout.php">Logout (<?=$this->getUser()?>)</a></li>
<?php } else { ?>						
                                            <li<?php if(\filter_input(INPUT_SERVER,'SCRIPT_NAME') === "/login.php") { echo " class='active'"; } ?>><a href="login.php">Login</a></li>
                                            <li<?php if((\filter_input(INPUT_SERVER,'SCRIPT_NAME') === "/register.php") OR (\filter_input(INPUT_SERVER,'SCRIPT_NAME') === "/register_done.php")) { echo " class='active'"; } ?>><a href="register.php">Registrierung</a></li>
<?php } ?>					
                                            <li<?php if(\filter_input(INPUT_SERVER,'SCRIPT_NAME') === "/impressum.php") { echo " class='active'"; } ?>><a href="impressum.php">Impressum</a></li>
                                    </ul>
                            </div><!-- mainmenu -->
                    <div id="content"><?php
    }
    public function getFooter() {
    ?></div>
            </div>
            <!-- Piwik -->
            <script type="text/javascript">
                    var _paq = _paq || [];
                    _paq.push(["setCookieDomain", "*.vlv-ilmenau.de"]);
                    _paq.push(["setDomains", ["*.vlv-ilmenau.de"]]);
                    _paq.push(['trackPageView']);
                    _paq.push(['enableLinkTracking']);
                    (function() {
                            var u="//piwik.stadtplan-ilmenau.de/";
                            _paq.push(['setTrackerUrl', u+'piwik.php']);
                            _paq.push(['setSiteId', 11]);
                            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                            g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
                    })();
            </script>
            <noscript><p><img src="//piwik.stadtplan-ilmenau.de/piwik.php?idsite=11" style="border:0;" alt="" /></p></noscript>
            <!-- End Piwik Code -->
            <a href="https://plus.google.com/+AndreasNeumannIlmenau" rel="publisher" class='hidden'></a>

    </body>
</html><?php
    }

    private $js=array();
    public function getDateFkt() {
            $this->js['datefkt'] = "var f0 = function(timeString) {
                    if(timeString > 9) {
                            return timeString;
                    }
                    else {
                            return '0'+timeString;
                    }
            }
            var makeTime = function(dateString) {
                    var newDate = new Date(dateString*1000);
                    //console.log(newDate.toString());
                    return f0(newDate.getHours())+':'+f0(newDate.getMinutes());
            }";
    }
    public function getMonat() {
            $this->js['monat'] = 'var monat = ["Januar","Februar","M&auml;rz","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember"];';
    }
    public function getWochentag() {
            $this->js['wochentag'] = 'var wochentag = ["Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag"];';
    }
    public function getVLVArray() {
            $request = array();
            $result = $this->db->query("SELECT `studiengang`, `semester`, `seminargruppe` FROM `vlv_entry2stud` GROUP BY `studiengang`, `semester`, `seminargruppe` ORDER BY `studiengang`, `semester`, `seminargruppe`;");

            while ($row = $result->fetch_assoc()) {
                $request[] = $row;
            }

            $vlv_groups = array();

            foreach($request as $dub) {
                if(!isset($vlv_groups[$dub['studiengang']])) {
                    $vlv_groups[$dub['studiengang']] = array();
                }
                if (!isset($vlv_groups[$dub['studiengang']][$dub['semester']])) {
                    $vlv_groups[$dub['studiengang']][$dub['semester']] = array();
                }
                if (strlen($dub['seminargruppe']) > 0) {
                    $vlv_groups[$dub['studiengang']][$dub['semester']][] = $dub['seminargruppe'];
                }
            }
            $this->js['vlv_group'] = "var vlv_groups = ".json_encode($vlv_groups).";";
            return $vlv_groups;
    }

    public function getVLVdata($id) {
        if(is_array($id)) {
            $array = array();
            foreach($id as $i) {
                $array[$i] = $this->getVLVdata($i);
            }
            return $array;
        }
        $result = $this->db->query("SELECT `vlv_id`,`type`,`location`,`last_change` FROM `vlv_entry` WHERE `id` = ".( (int) $id).";");
        $data = $result->fetch_assoc();
        $command = $this->db->query("SELECT `title`,`author`,`description`,`url` FROM `vlv_zusammenfassung` WHERE `vlv_id` = '".$this->db->real_escape_string($data['vlv_id'])."';");
        $zdata = $command->fetch_assoc();
        return array_merge($data,$zdata);
    }

    private $loginError = false;
    public function getLoginError() {
        return $this->loginError;
    }

    public function login() {
        if (isset($_SESSION['uid'])) {
            $this->goHeader();
        } elseif (\filter_has_var(INPUT_POST,'user') AND \filter_has_var(INPUT_POST,'password')) {
            $result = $this->db->query("SELECT `uid`,`password` FROM `user` WHERE `uname`='" . $this->db->real_escape_string(filter_input(INPUT_POST,'user')) . "' AND `block`=0");
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                if (password_verify(filter_input(INPUT_POST,'password'), $row['password'])) {
                    $_SESSION['uname'] = filter_input(INPUT_POST,'user');
                    $_SESSION['uid'] = $row['uid'];

                    $this->db->real_query("UPDATE `user` SET `last_login` = CURRENT_TIMESTAMP WHERE `uid` = '" . $row['uid'] . "'");

                    $this->goHeader();
                }
            }
            $this->loginError = true;
        }
    }

    private $registerErrorUser = false;
    public function getRegisterErrorUser() {
        return $this->registerErrorUser;
    }

    private $registerErrorPassword = false;
    public function getRegisterErrorPasswort() {
        return $this->registerErrorPassword;
    }

    private $registerErrorEmail = false;
    public function getRegisterErrorEmail() {
        return $this->registerErrorEmail;
    }

    public function register() {
        if (isset($_SESSION['uid'])) {
            $this->goHeader();
        } elseif (\filter_has_var(INPUT_POST,'user') AND \filter_has_var(INPUT_POST,'password') AND \filter_has_var(INPUT_POST,'password_verify') AND \filter_has_var(INPUT_POST,'email')) {
            $result = $this->db->query("SELECT * FROM `user` WHERE `uname`='" . $this->db->real_escape_string(\filter_input(INPUT_POST,'user')) . "'");
            if ($result->num_rows === 1) {
                $this->registerErrorUser = "Nutzername ist bereits vorhanden";
            }

            if (!preg_match('/^[a-zA-Z0-9-_]{4,50}$/', \filter_input(INPUT_POST, 'user'))) {
                $this->registerErrorUser = "Nutzername muss mindestens 4, maximal 50 Zeichen lang sein und darf aus folgenden Zeichen bestehen: a-z, A-Z (ohne Umlaute), 0-9 und den Sonderzeichen Binde- oder Unterstrich";
            }

            $result = $this->db->query("SELECT * FROM `user` WHERE `email`='" . $this->db->real_escape_string(strtolower(trim(\filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL)))) . "'");
            if ($result->num_rows === 1) {
                $this->registerErrorEmail = "Emailadresse ist bereits vorhanden";
            }

            if (!preg_match('/^[a-z0-9._%+-]+@([a-z0-9]+\.)?tu-ilmenau.de$/', strtolower(\filter_input(INPUT_POST, 'email')))) {
                $this->registerErrorEmail = "Es muss eine gültige Emailadresse der TU-Ilmenau angegeben werden";
            }

            if (\filter_input(INPUT_POST, 'password') !== \filter_input(INPUT_POST, 'password_verify')) {
                $this->registerErrorPassword = "Beide Passwörter stimmen nicht überein";
            }

            if (!preg_match('/[a-z]+/', \filter_input(INPUT_POST, 'password')) OR ! preg_match('/[A-Z]+/', \filter_input(INPUT_POST, 'password')) OR ! preg_match('/[0-9]+/', \filter_input(INPUT_POST, 'password'))) {
                $this->registerErrorPassword = "Das Passwort muss aus Groß- und Kleinbuchstaben (a-z und A-Z) und aus Ziffern bestehen. Es kann Sonderzeichen (inkl. Umlaute enthalten)";
            }
            if ((strlen(\filter_input(INPUT_POST, 'password')) < 6) OR ( strlen(\filter_input(INPUT_POST, 'password')) > 32)) {
                $this->registerErrorPassword = "Das Passwort muss 6 oder mehr Zeichen haben. Maximal 32 Zeichen sind erlaubt.";
            }


            if (!$this->registerErrorUser AND ! $this->registerErrorEmail AND ! $this->registerErrorPassword) {
                // Registration kann beginnen
                // Verify-Code erzeugen
                $i = 0;
                do {
                    $verify = md5($i . time() . \filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL));
                    $command = $this->db->query("SELECT `uid` FROM `user` WHERE `verify` = '" . $this->db->real_escape_string($verify) . "'");
                    $i++;
                } while ($command->num_rows > 0);
                // iCal-Code erzeugen
                do {
                    $ical = md5($i . time() . \filter_input(INPUT_POST,'user'));
                    $command = $this->db->query("SELECT `uid` FROM `user` WHERE `iCal_string` = '" . $this->db->real_escape_string($ical) . "'");
                    $i++;
                } while ($command->num_rows > 0);

                $message = "Hallo,\n\nDies ist eine Verifikationsmail von vlv-ilmenau.de. Besuche einfach folgende Seite um deinen Account zu verifizieren:\n\n\t https://vlv-ilmenau.de/verify.php?c=" . urlencode($verify);
                $this->sendMail(\filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL), 'Verifizieren Sie Ihren Account auf vlv-ilmenau.de', $message);
                $this->db->real_query("INSERT INTO `user` (`uname`, `password`, `email`, `verify`, `iCal_string`, `block`) VALUES ('" . $this->db->real_escape_string(trim(\filter_input(INPUT_POST,'user'))) . "', '" . $this->db->real_escape_string(password_hash(\filter_input(INPUT_POST,'password'), PASSWORD_DEFAULT)) . "','" . $this->db->real_escape_string(strtolower(trim(\filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL)))) . "','" . $this->db->real_escape_string($verify) . "','" . $this->db->real_escape_string($ical) . "',1)");

                $this->goHeader("register_done.php");
            }
        }
    }

    public function intern() {
        if(!$this->getUid())
            $this->goHeader();
    }

    public function su() {
        if (!$this->getUid()) {
            $this->goHeader();
        }
        $command = $this->db->query("SELECT `uid` FROM `user` WHERE `uid` = '".$this->db->real_escape_string($this->getUid())."' AND `su` = 1");
        if (!($command->num_rows == 1)) {
            $this->goHeader();
        }
    }

    public function isSu() {
        if($this->getUid()) {
            $command = $this->db->query("SELECT `uid` FROM `user` WHERE `uid` = '".$this->db->real_escape_string($this->getUid())."' AND `su` = 1");
            if ($command->num_rows == 1) {
                return true;
            }
        }
        return false;
    }

    public function settings() {
    }

    public function verify() {
        if (isset($_SESSION['uid'])) {
            $this->goHeader();
        }
        if(\filter_has_var(INPUT_GET,'c')) {
            $result = $this->db->query("SELECT `uid` FROM `user` WHERE `verify` = '".$this->db->real_escape_string(\filter_input(INPUT_GET,'c'))."' AND `block` = 1");
            if($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $this->db->real_query("UPDATE `user` SET `block`= 0, `verify` = NULL WHERE `uid` = '".$row['uid']."'");
                $this->goHeader('/login.php');
            }
        }
    }

    public function getUser() {
        if (isset($_SESSION['uid'])) {
            return $_SESSION['uname'];
        } else {
            return false;
        }
    }

    public function getUid() {
        if (isset($_SESSION['uid'])) {
            return $_SESSION['uid'];
        } else {
            return false;
        }
    }

    public function getEmail() {
        if(isset($_SESSION['uid'])) {
            $result = $this->db->query("SELECT `email` FROM `user` WHERE `uid`= '".$this->getUid()."'");
            if ($row = $result->fetch_assoc()) {
                return $row['email'];
            }
        }
        return false;
    }

    private function goHeader($destination = "") {
        if (\filter_has_var(INPUT_SERVER,'HTTPS')) {
            header('Location: https://vlv-ilmenau.de/' . $destination);
        } else {
            header('Location: http://vlv-ilmenau.de/' . $destination);
        }
        die();
    }

    public function sendMail ($to, $subject, $message) {
        require_once 'Mail.php';
        $header["Precedence"] = "bulk";
        $header["From"] = "Mein Vorlesungsverzeichnis Ilmenau <webmaster@vlv-ilmenau.de>";
        $header["Reply-To"] = "webmaster@vlv-ilmenau.de";
        $header["X-Mailer"] = "PHP/" . phpversion();
        $header["Content-Type"] = "text/html; charset=UTF-8";
        $header["From"] = "webmaster@vlv-ilmenau.de";
        $header["To"] = $to;
        $header["Subject"] = $subject;

        $message .= "\n\n-- \nhttp://vlv-ilmenau.de\n";
        
        $smtp = Mail::factory('smtp',
            array ( 'host' => SMTP_HOST,
                    'auth' => SMTP_AUTH,
                    'username' => SMTP_USER,
                    'password' => SMTP_PASS));
        $mail = $smtp->send($to, $header, $message);
    }

    public function vlvSite($json) {
        $monat = array("","Januar","Februar","M&auml;rz","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember");
        $wochentag = array("Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag");
        $heute = \date("Ymd");
        $last = 0;
        $output = "";
        foreach($json['dates'] as $data) {
            $terminDatum = \date('Ymd',$data['from']);
            if($terminDatum > $last) {
                if($terminDatum === $heute) {
                    $output .= "\t<div class='vlvDate'>Heute</div>\n";
                } elseif ($terminDatum === ($heute + 1)) {
                    $output .= "\t<div class='vlvDate'>Morgen</div>\n";
                } elseif ($terminDatum === ($heute + 2)) {
                    $output .= "\t<div class='vlvDate'>&Uuml;bermorgen</div>\n";
                } else {
                    $output .= "\t<div class='vlvDate'>" . $wochentag[\date('w', $data['from'])] . ", " . \date('d', $data['from']) . ". " . $monat[\date('n', $data['from'])] . "</div>\n";
                }
                $last = $terminDatum;
            }
            $type = "";
            if (preg_match('/vorlesung/i', $json['content'][$data['id']]['type'])) {
                $type = " vlvVorlesung";
            } elseif (preg_match('/bung/i', $json['content'][$data['id']]['type'])) {
                $type = " vlvUebung";
            } elseif (preg_match('/klausur/i', $json['content'][$data['id']]['type'])) {
                $type = " vlvKlausur";
            } elseif (preg_match('/seminar/i', $json['content'][$data['id']]['type'])) {
                $type = " vlvSeminar";
            } elseif (preg_match('/praktikum/i', $json['content'][$data['id']]['type'])) {
                $type = " vlvSeminar";
            } elseif (preg_match('/praktika/i', $json['content'][$data['id']]['type'])) {
                $type = " vlvSeminar";
            }
            $output .= "\t<div class='vlvTermin{$type}' itemscope itemtype='http://schema.org/EducationEvent'>\n";
            $output .= "\t\t<span class='vlvTimerange'>\n";
            $output .= "\t\t\t<span class='vlvFrom' itemprop='startDate' content='".date('c',$data['from'])."'>".date('H:i',$data['from'])."</span>&nbsp;&ndash;&nbsp;\n";
            $output .= "\t\t\t<span class='vlvTo' itemprop='endDate' content='".date('c',$data['to'])."'>".date('H:i',$data['to'])."</span>\n";
            $output .= "\t\t</span>\n";
            $output .= "\t\t<span itemprop='performer' itemscope itemtype='http://schema.org/Person'><meta itemprop='name' content='".preg_replace('/(\,.*)$/','',$json['content'][$data['id']]['author'])."' /></span>\n";
            $output .= "\t\t<span class='vlvPrefix'>".$json['content'][$data['id']]['type']."</span>&nbsp;\n";
            $output .= "\t\t<span class='vlvTitle' itemprop='name'>".$json['content'][$data['id']]['title']."</span>\n";
            $output .= "\t\t<span class='vlvLocation' itemprop='location' itemscope itemtype='http://schema.org/Place'> (<span itemprop='name'>".$json['content'][$data['id']]['location']."</span>)</span>\n";
            if(isset($json['content'][$data['id']]['description']) AND (strlen($json['content'][$data['id']]['description'])>0)) {
                $output .= "\t\t<a itemprop='url' class='vlvDesc fa fa-info-circle' href='vlvData.php?id=".$json['content'][$data['id']]['description']."'></a>\n";
            } else {
                $output .= "\t\t<a itemprop='url' href='vlvData.php?vid=".htmlentities(urlencode($json['content'][$data['id']]['vlv_id']))."'></a>\n";
            }
            $output .= "\t</div>\n";
        }
        return $output;
    }

    public function privateVlv ($uid) {
        $idArray = array();
        $vlvIdTypeArray = array();
        $command = $this->db->query("SELECT `vlv_studiengang`, `vlv_semester`, `vlv_seminargruppe`, `vlv_id`, `type` FROM `user_rules` WHERE `type` IS NOT NULL AND `type` != '' AND `uid` = ".$uid);

        if($command->num_rows > 0) {
            while ($ruleRow = $command->fetch_assoc()) {
                if (isset($vlvIdTypeArray[$ruleRow['vlv_id']])) {
                    $vlvIdTypeArray[$ruleRow['vlv_id']][] = $ruleRow['vlv_id']['type'];
                    $vlvIdTypeArray[$ruleRow['vlv_id']] = array_unique($vlvIdTypeArray[$ruleRow['vlv_id']]);
                } else {
                    $vlvIdTypeArray[$ruleRow['vlv_id']] = array($ruleRow['vlv_id']['type']);
                }
                $vlvCommand = $this->db->query("SELECT `id` FROM `vlv_entry` WHERE `vlv_id` = '".$this->db->real_escape_string($ruleRow['vlv_id'])."' AND `type` = '".$this->db->real_escape_string($ruleRow['type'])."'");
                if($vlvCommand->num_rows > 0) {
                    $tempIdArray = array();
                    while($vlvRow = $vlvCommand->fetch_assoc()) {
                        $tempIdArray[] = $vlvRow['id'];
                    }
                    $entryCommand = $this->db->query("SELECT `id` FROM `vlv_entry2stud` WHERE `id` IN (".implode(',',$tempIdArray).") AND `studiengang` = '".$this->db->real_escape_string($ruleRow['vlv_studiengang'])."' AND `semester`= '".$this->db->real_escape_string($ruleRow['vlv_semester'])."' AND `seminargruppe` = '".$this->db->real_escape_string($ruleRow['vlv_seminargruppe'])."'");
                    if($entryCommand->num_rows > 0) {
                        while($entryRow = $entryCommand->fetch_assoc()) {
                            $idArray[] = $entryRow['id'];
                        }
                    }
                }
            }
            $idArray = array_unique($idArray);
        }

        $command = $this->db->query("SELECT `vlv_studiengang`, `vlv_semester`, `vlv_seminargruppe`, `vlv_id` FROM `user_rules` WHERE (`type` IS NULL OR `type` = '') AND `uid` = ".$uid);
        if($command->num_rows > 0) {
            while ($ruleRow = $command->fetch_assoc()) {
                $vlvCommand = $this->db->query("SELECT `id`, `type` FROM `vlv_entry` WHERE `vlv_id` = '".$this->db->real_escape_string($ruleRow['vlv_id'])."'");
                if($vlvCommand->num_rows > 0) {
                    $tempIdArray = array();
                    while($vlvRow = $vlvCommand->fetch_assoc()) {
                        if (isset($vlvIdTypeArray[$ruleRow['vlv_id']]) AND in_array($vlvRow['type'], $vlvIdTypeArray[$ruleRow['vlv_id']])) {
                            continue;
                        }
                        $tempIdArray[] = $vlvRow['id'];
                    }
                    if (count($tempIdArray) == 0) {
                        continue;
                    }
                    $entryCommand = $this->db->query("SELECT `id` FROM `vlv_entry2stud` WHERE `id` IN (".implode(',',$tempIdArray).") AND `studiengang` = '".$this->db->real_escape_string($ruleRow['vlv_studiengang'])."' AND `semester`= '".$this->db->real_escape_string($ruleRow['vlv_semester'])."' AND `seminargruppe` = '".$this->db->real_escape_string($ruleRow['vlv_seminargruppe'])."'");
                    if($entryCommand->num_rows > 0) {
                        while($entryRow = $entryCommand->fetch_assoc()) {
                            $idArray[] = $entryRow['id'];
                        }
                    }
                }
            }
            $idArray = array_unique($idArray);
        }
        return $idArray;
    }
}