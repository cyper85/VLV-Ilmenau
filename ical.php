<?php
	
require_once('config.php');

class event {
    private $txt = array();
    private $unescape = array();
    public function __construct($start,$end,$id) {
        $this->txt['DTSTART;TZID=Europe/Berlin'] = date('Ymd\THis',$start);
        $this->txt['DTEND;TZID=Europe/Berlin'] = date('Ymd\THis',$end);
        $this->txt['UID'] = $this->txt['DTSTART;TZID=Europe/Berlin']."@".$id;
        $this->txt['CLASS'] = "PUBLIC";
        $this->txt['TRANSP'] = "OPAQUE";
        $this->txt['X-MICROSOFT-CDO-BUSYSTATUS'] = "BUSY";
        $this->txt['X-MICROSOFT-MSNCALENDAR-BUSYSTATUS'] = "BUSY";
    }

    public function setLastModified($l) {
        $this->txt['LAST-MODIFIED'] = date('Ymd\THis\Z',strtotime($l));
    }

    public function setLocation($l) {
        global $db;
        $command = $db->query("SELECT `url` FROM `location` WHERE `location` = '".$db->real_escape_string($l)."'");
        if ($command->num_rows == 1) {
            $row = $command->fetch_assoc();
            $this->txt['LOCATION;ALTREP="' . $row['url'] . '"'] = $l;
        } else {
            $this->txt['LOCATION'] = $l;
        }
    }

    public function setTransparent() {
        $this->txt['TRANSP'] = "TRANSPARENT";
    }

    public function setUrl($l) {
        $this->txt['URL;VALUE=URI'] = $l;
    }

    public function setRRule($l) {
        $this->unescape['RRULE'] = $l;
        $this->unescape['X-MICROSOFT-RRULE'] = $l;
    }

    public function setExdate($l) {
        if(count($l)>0) {
            $this->unescape['EXDATE;VALUE=DATE-TIME;TZID=Europe/Berlin'] = $l;
            $this->unescape['X-MICROSOFT-EXDATE;VALUE=DATE-TIME;TZID=Europe/Berlin'] = $l;
        }
    }

    public function setSummary($s) {
        $this->txt['SUMMARY'] = $s;
    }

    public function setDescription($l) {
        $this->txt['DESCRIPTION'] = $l;
    }

    public function setHTMLDescription($l) {
        #$this->txt['X-ALT-DESC;FMTTYPE=text/html'] = "".$l;
    }

    private $lang = "de_DE";
    public function setLang($l) {
        if ($l == "en") {
            $this->lang = "en_US";
        } elseif ($l == "de") {
            $this->lang = "de_DE";
        }
    }

    public function setCategory($c) {
        $this->txt['CATEGORIES'][] = $c;
    }

    public function getEvent() {
        $return = "";
        foreach($this->txt as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $subV) {
                    $return .= mb_convert_encoding(chunk_split(mb_convert_encoding($k . ":" . str_replace(",", "\,", str_replace(";", "\;", preg_replace("/[\t]/", "\\t", preg_replace("/[\n\r]+/", "\\r\\n", str_replace("\\", "\\\\", $subV))))), "ISO-8859-15", "UTF-8"), 60, "\r\n ") . "\r\n", "UTF-8", "ISO-8859-15");
                }
            } else {
                $return .= mb_convert_encoding(chunk_split(mb_convert_encoding($k . ":" . str_replace(",", "\,", str_replace(";", "\;", preg_replace("/[\t]/", "\\t", preg_replace("/[\n\r]+/", "\\r\\n", str_replace("\\", "\\\\", $v))))), "ISO-8859-15", "UTF-8"), 60, "\r\n ") . "\r\n", "UTF-8", "ISO-8859-15");
            }
        }
        foreach($this->unescape as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $subV) {
                    $return .= mb_convert_encoding(chunk_split(mb_convert_encoding($k . ":" . $subV, "ISO-8859-15", "UTF-8"), 60, "\r\n ") . "\r\n", "UTF-8", "ISO-8859-15");
                }
            } else {
                $return .= mb_convert_encoding(chunk_split(mb_convert_encoding($k . ":" . $v, "ISO-8859-15", "UTF-8"), 60, "\r\n ") . "\r\n", "UTF-8", "ISO-8859-15");
            }
        }
        return	"BEGIN:VEVENT\r\n".
                $return.
                "END:VEVENT\r\n";
    }
}

class ical {
	
    private $events = array();
    private $data = "";
    private $name = "";

    public function __construct($name="") {
        $this->setName($name);
    }

    public function setName($name) {
        $this->name = $name;
        $this->name = preg_replace("/[ .,?\/|]/","-",$this->name);
        $this->name = preg_replace("/[-]{2,}/","-",$this->name);
    }

    public function createNewVLVPeriodEvent($id) {
        global $db;
        global $main;

        $startyear = date('Y');
        if (date('n') < 4) {
            $startyear--;
        }

        $result = $db->query("SELECT `weekday`,`rules`,`time_period` FROM `vlv_entry` WHERE `id` = {$id}");
        $row = $result->fetch_assoc();
        $period = 1;
        $weekrule = $row['rules'];
        $match=[];
        if(\preg_match("/\s*[UG]\s*\((.+)\)\s*/i",$row['rules'],$match)) {
            $weekrule = $match[1];
            $period = 2;
        }

        $firstEventResult = $db->query("SELECT MIN(`from`) AS `from`, MIN(`to`) AS `to`,MAX(`to`) AS `maxto` FROM `vlv_entry2date` WHERE `id` = {$id}");
        $firstEventRow = $firstEventResult->fetch_assoc();

        $event = new event(strtotime($firstEventRow['from']),strtotime($firstEventRow['to']),$id);
        $this->eventData($event,$id);

        $allEventDates = $db->query("SELECT `from` FROM `vlv_entry2date` WHERE `id` = {$id}");
        $allDates = array();
        while($allEventDatesRow = $allEventDates->fetch_assoc() ) {
                $allDates[] = date('Y-m-d',strtotime($allEventDatesRow['from']));
        }

        $exDateGen = strtotime($firstEventRow['from'])+(60*60*24*7*$period);
        $exDates = array();

        do {
            if(!in_array(date('Y-m-d', $exDateGen), $allDates)) {
                $exDates[] = date('Ymd', $exDateGen) . "T" . date('His', strtotime($firstEventRow['from']));
            }
            $exDateGen += 60*60*24*7*$period;
        } while($exDateGen<strtotime($firstEventRow['maxto']));

        $event->setRrule("FREQ=WEEKLY;INTERVAL={$period};UNTIL=".date('Ymd\THis\Z',(strtotime($firstEventRow['maxto'])-date('Z',strtotime($firstEventRow['maxto']))))."");
        if (count($exDates) > 0) {
            $event->setExdate($exDates);
        }

        $this->events[] = $event->getEvent();
    }

    private function eventData(&$event,$id) {
        global $db;
        global $main;
        $data = $main->getVLVdata($id);

        $event->setLocation(html_entity_decode($data['location']));
        $prefix = "";
        switch($data['type']) {
            case "Vorlesungen (fakultativ)":
                $event->setCategory("fakultativ");
            case "Vorlesungen":
            case "Vorlesungen (Teleteaching)":
                $prefix = "V: ";
                $event->setCategory("Vorlesung");
                break;
            case "Übungen (fakultativ)":
                $event->setCategory("fakultativ");
            case "Übungen":
            case "Praktische Übungen":
                $prefix = "Ü: ";
                $event->setCategory("Übung");
                break;
            case "Seminare (Fakultativ)":
            case "Seminare (fakultativ)":
                $event->setCategory("fakultativ");
            case "Seminare":
                $prefix = "V: ";
                $event->setCategory("Seminar");
                break;
            case "Klausur":
                $prefix = "Klausur: ";
                break;
            case "Tutorium":
                $prefix = "Tutorium: ";
                break;
            case "Konsultation":
                $prefix = "Konsultation: ";
                break;
            case "Termine":
                $prefix = "Termin: ";
                $event->setCategory("Termin");
                break;
            case "Kolloquium":
                $prefix = "Kolloquium: ";
                break;
            case "Praktika":
                $prefix = "Praktikum: ";
                $event->setCategory("Praktikum");
                break;
        }
        $event->setSummary(html_entity_decode($prefix.$data['title']));
        $event->setCategory(html_entity_decode($data['type']));
        $event->setUrl(html_entity_decode($data['url']));
        $event->setLastModified($data['last_change']);

        $description = "Leiter:\t".html_entity_decode($data['author']);
        $HTMLdescription = "<dt>Leiter</dt><dd>".$data['author']."</dd>";

        if(strlen($data['description'])>0) {
            $command = $db->query("SELECT `lang`,`LP`,`exam`,`Vorkenntnisse`,`Lernergebnisse`,`Inhalt` ".
            "FROM `vlv2_object` WHERE `id` = '".((int) $data['description'])."'");
            $vlvEntry = $command->fetch_assoc();

            if(strlen($vlvEntry['Vorkenntnisse'])>0) {
                $description .= "\r\n\r\nVorkenntnisse:\t".trim(html_entity_decode(strip_tags($vlvEntry['Vorkenntnisse'])));
                $HTMLdescription .= "<dt>Vorkenntnisse</dt><dd>".$vlvEntry['Vorkenntnisse']."</dd>";
            }
            if(strlen($vlvEntry['Lernergebnisse'])>0) {
                $description .= "\r\n\r\nLernergebnisse:\t".trim(html_entity_decode(strip_tags($vlvEntry['Lernergebnisse'])));
                $HTMLdescription .= "<dt>Lernergebnisse</dt><dd>".$vlvEntry['Lernergebnisse']."</dd>";
            }
            if(strlen($vlvEntry['Inhalt'])>0) {
                $description .= "\r\n\r\nInhalt:\t".trim(html_entity_decode(strip_tags($vlvEntry['Inhalt'])));
                $HTMLdescription .= "<dt>Inhalt</dt><dd>".$vlvEntry['Inhalt']."</dd>";
            }
            if(strlen($vlvEntry['exam'])>0) {
                $description .= "\r\n\r\nAbschluss:\t".trim(html_entity_decode(strip_tags($vlvEntry['exam'])));
                $HTMLdescription .= "<dt>Abschluss</dt><dd>".$vlvEntry['exam']."</dd>";
            }
            if(strlen($vlvEntry['LP'])>0) {
                $description .= "\r\n\r\nLeistungspunkte:\t".trim(html_entity_decode(strip_tags($vlvEntry['LP'])));
                $HTMLdescription .= "<dt>Leistungspunkte</dt><dd>".$vlvEntry['LP']."</dd>";
            }
            if(strlen($vlvEntry['lang'])>0) {
                $event->setLang(strtolower($vlvEntry['lang']));
            }
        }
        $event->setDescription($description);
        $event->setHTMLDescription("<!DOCTYPE html><html><head></head><body><dl>".$HTMLdescription."</dl></body></html>");
    }

    public function createNewVLVEvent($start,$end,$id) {
        $event = new event($start,$end,$id);
        $this->eventData($event,$id);
        $this->events[] = $event->getEvent();
    }

    private function createData() {
        $this->data = "BEGIN:VCALENDAR\r\n".
            "VERSION:2.0\r\n".
            "PRODID:-//vlv-ilmenau.de//NONSGML Events//\r\n".
            "METHOD:PUBLISH\r\n".
            "X-WR-TIMEZONE:Europe/Berlin\r\n".
            "X-WR-CALNAME:Mein Vorlesungsverzeichnis\r\n".
            "X-WR-CALDESC:Persöhnliches Vorlesungsverzeichnis basierend a\r\n".
            " uf den offiziellen Daten der Technischen Universität Ilmenau\r\n".
            "X-MICROSOFT-CALSCALE:0x0001\r\n".
            "X-MS-WKHRDAYS:MO,TU,WE,TH,FR\r\n".
            "X-PRIMARY-CALENDAR:FALSE\r\n".
            "X-PUBLISHED-TTL:+P1D\r\n".
            "CALSCALE:GREGORIAN\r\n".
            "BEGIN:VTIMEZONE\r\n".
            "TZID:Europe/Berlin\r\n".
            "BEGIN:DAYLIGHT\r\n".
            "TZOFFSETFROM:+0100\r\n".
            "TZOFFSETTO:+0200\r\n".
            "DTSTART:19810329T020000\r\n".
            "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU\r\n".
            "TZNAME:CEST\r\n".
            "END:DAYLIGHT\r\n".
            "BEGIN:STANDARD\r\n".
            "TZOFFSETFROM:+0200\r\n".
            "TZOFFSETTO:+0100\r\n".
            "DTSTART:19961027T030000\r\n".
            "RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU\r\n".
            "TZNAME:CET\r\n".
            "END:STANDARD\r\n".
            "END:VTIMEZONE\r\n";

        foreach ($this->events as $e) {
            $this->data .= $e;
        }
        $this->data .= "END:VCALENDAR";
        $this->data = preg_replace("/\r\n[\s\t]*\r\n/ms","\r\n",$this->data);
        #$this->data = str_replace("\r\n\r\n","\r\n",$this->data);
    }

    private $lastmod = 0;
    public function setLastMod($time) {
        $this->lastmod = (int) $time;
    }

    public function __destruct() {
        $this->createData();
        header('Content-Description: File Transfer');
        header("Content-type:text/calendar; charset=UTF-8");
        header('Content-Disposition: attachment; filename="vlv-'.$this->name.'.ics"');
        header('Content-Length: '.strlen($this->data));
        header('Content-Language: de, en');
        header('Cache-Control: public');

        header("Last-Modified: ".gmdate("D, d M Y H:i:s", $this->lastmod)." GMT");
        header("Expires: ".gmdate("D, d M Y H:i:s", $this->lastmod + (60*60*24))." GMT");
        header("Etag: ".md5(gmdate("D, d M Y H:i:s", $this->lastmod)." GMT"));

#		Header('Connection: close');
        echo $this->data;
    }
}

$result = $db->query("SELECT `value` FROM `sysvar` WHERE `key` = 'lastupdate'");
$row = $result->fetch_assoc();
if(\filter_has_var(INPUT_GET,'id')) {
    $command = $db->query("SELECT UNIX_TIMESTAMP(`lastupdate`) as `lastupdate` FROM `user` WHERE `iCal` = 0 AND `iCal_string` = '".$db->real_escape_string(filter_input(INPUT_GET,'id'))."'");
    if($command->num_rows === 1) {
        $userrow = $command->fetch_assoc();
        if ($row['value'] < $userrow['lastupdate']) {
            $row['value'] = $userrow['lastupdate'];
        }
    }
}

// Cachecontroll (um keinen sinnlosen Traffic zu erzeugen)
if(\filter_has_var(INPUT_SERVER,'HTTP_IF_NONE_MATCH') AND (\trim(\filter_input(INPUT_SERVER,'HTTP_IF_NONE_MATCH')) == md5(gmdate("D, d M Y H:i:s", $row['value'])." GMT"))) {
    header("HTTP/1.1 304 Not Modified");
    exit;
}
elseif(\filter_has_var(INPUT_SERVER,'HTTP_IF_MODIFIED_SINCE') AND (\trim(\filter_input(INPUT_SERVER,'HTTP_IF_MODIFIED_SINCE')) == $row['value'])) {
    header("HTTP/1.1 304 Not Modified");
    exit;
}

$ical = new ical();
$ical->setLastMod($row['value']);

if(\filter_has_var(INPUT_GET,'Studiengang') AND \filter_has_var(INPUT_GET,'Semester')) {
    $command = "SELECT `id` FROM `vlv_entry2stud` WHERE `studiengang` = '".$db->real_escape_string($_GET['Studiengang']).
        "' AND `semester` = '".$db->real_escape_string($_GET['Semester'])."'";
    if(isset($_GET['Gruppe'])) {
        $command .= " AND `seminargruppe` = '" . $db->real_escape_string($_GET['Gruppe']) . "'";
        $ical->setName($_GET['Studiengang'] . "-" . $_GET['Semester'] . "-" . $_GET['Gruppe']);
    } else {
        $ical->setName($_GET['Studiengang'] . "-" . $_GET['Semester']);
    }

    $result = $db->query($command);
    $TerminIDs = array();
    while ($row = $result->fetch_assoc()) {
        $TerminIDs[] = $row;
    }
    $TerminIDsArray = array();
    foreach ($TerminIDs as $TerminID) {
        $TerminIDsArray[] = $TerminID['id'];
    }
    $result = $db->query("SELECT `rules`,`id` FROM `vlv_entry` WHERE `id` IN (".implode(', ',$TerminIDsArray).")");
    while($row = $result->fetch_assoc()) {
        if(preg_match('/KW\s*[\)]?\s*$/', $row['rules'])) {
            $ical->createNewVLVPeriodEvent($row['id']);
        }
        else {
            $result2 = $db->query("SELECT `id`, UNIX_TIMESTAMP(`from`) as `from`, UNIX_TIMESTAMP(`to`) as `to` FROM `vlv_entry2date` ".
                "WHERE `id` = '".$row['id']."' ORDER BY `from`");
            while ($row2 = $result2->fetch_assoc()) {
                $ical->createNewVLVEvent($row2['from'], $row2['to'], $row['id']);
            }
        }
    }
}
elseif(\filter_has_var(INPUT_GET,'id')) {
    $command = $db->query("SELECT `uid`,`uname` FROM `user` WHERE `iCal` = 0 AND `iCal_string` = '".$db->real_escape_string(\filter_var(INPUT_GET,'id'))."'");
    if($command->num_rows == 1) {
        $row = $command->fetch_assoc();
        $ical->setName($row['uname']);
        $idArray = $main->privateVlv($row['uid']);

        $result = $db->query("SELECT `rules`,`id` FROM `vlv_entry` WHERE `id` IN (".implode(', ',$idArray).")");
        while($row = $result->fetch_assoc()) {
            if(preg_match('/KW\s*[\)]?\s*$/', $row['rules'])) {
                $ical->createNewVLVPeriodEvent($row['id']);
            }
            else {
                $result2 = $db->query("SELECT `id`, UNIX_TIMESTAMP(`from`) as `from`, UNIX_TIMESTAMP(`to`) as `to` FROM `vlv_entry2date` ".
                    "WHERE `id` = '".$row['id']."' ORDER BY `from`");
                while ($row2 = $result2->fetch_assoc()) {
                    $ical->createNewVLVEvent($row2['from'], $row2['to'], $row['id']);
                }
            }
        }
    }
    else {
        sleep(10);
        header("HTTP/1.0 400 Bad Request");
        exit;
    }
}
else {
    sleep(10);
    header("HTTP/1.0 400 Bad Request");
    exit;
}