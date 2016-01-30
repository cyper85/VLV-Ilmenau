<?php

require_once("config.php");

header('Content-type: application/json');
if(!filter_has_var(INPUT_POST,'type')) {
    header("HTTP/1.0 400 Bad Request");
    exit;
}

function vlvSort($a,$b) {
    if ($a['title'] == $b['title']) {
        return 0;
    }
    return ($a['title'] < $b['title']) ? -1 : 1;
}

switch(\filter_input(INPUT_POST,'type')) {
    case 'vlv':
        if(!\filter_has_var(INPUT_POST,'Studiengang') OR !\filter_has_var(INPUT_POST,'Semester')) {
            header("HTTP/1.0 400 Bad Request");
            exit;
        }
        $_SESSION['studiengang'] = \filter_input(INPUT_POST,'Studiengang');
        $_SESSION['semester'] = \filter_input(INPUT_POST,'Semester');
        $command = "SELECT `id` FROM `vlv_entry2stud` WHERE `studiengang` = '".$db->real_escape_string(\filter_input(INPUT_POST,'Studiengang')).
                "' AND `semester` = '".$db->real_escape_string(\filter_input(INPUT_POST,'Semester'))."'";
        if (\filter_has_var(INPUT_POST, 'Gruppe')) {
            $command .= " AND `seminargruppe` = '" . $db->real_escape_string(\filter_input(INPUT_POST, 'Gruppe')) . "'";
            $_SESSION['gruppe'] = \filter_input(INPUT_POST, 'Gruppe');
        } else {
            unset($_SESSION['gruppe']);
        }
        #print $command;

        $result = $db->query($command);
        $TerminIDs = [];
        while ($row = $result->fetch_assoc()) {
            $TerminIDs[] = $row;
        }
        $TerminIDsArray = [];
        foreach ($TerminIDs as $TerminID) {
            $TerminIDsArray[] = $TerminID['id'];
        }
        $result = $db->query("SELECT `id`, UNIX_TIMESTAMP(`from`) as `from`, UNIX_TIMESTAMP(`to`) as `to` FROM `vlv_entry2date` ".
            "WHERE `id` IN (".implode(', ',$TerminIDsArray).") AND `to` > NOW() ORDER BY `from` LIMIT 100");
        $Termine = [];
        while ($row = $result->fetch_assoc()) {
            $Termine[] = $row;
        }
        $return = [];
        $return['dates'] = $Termine;
        $TerminIDsArray = [];
        foreach ($Termine as $Termin) {
            $TerminIDsArray[] = $Termin['id'];
        }

        $return['content'] = $main->getVLVdata($TerminIDsArray);

        echo json_encode($return);
        break;
    case 'auswahl':
        if(!\filter_has_var(INPUT_POST,'Studiengang') OR !\filter_has_var(INPUT_POST,'Semester')) {
            header("HTTP/1.0 400 Bad Request");
            exit;
        }
        $return = [];
        $_SESSION['studiengang'] = \filter_input(INPUT_POST,'Studiengang');
        $_SESSION['semester'] = \filter_input(INPUT_POST,'Semester');
        $command = "SELECT `id` FROM `vlv_entry2stud` WHERE `studiengang` = '".$db->real_escape_string(\filter_input(INPUT_POST,'Studiengang')).
            "' AND `semester` = '".$db->real_escape_string(\filter_input(INPUT_POST,'Semester'))."'";
        if (\filter_has_var(INPUT_POST, 'Gruppe')) {
            $command .= " AND `seminargruppe` = '" . $db->real_escape_string(\filter_input(INPUT_POST, 'Gruppe')) . "'";
            $_SESSION['gruppe'] = \filter_input(INPUT_POST, 'Gruppe');
        } else {
            unset($_SESSION['gruppe']);
        }

        $result = $db->query($command);
        $TerminIDs = [];
        while ($row = $result->fetch_assoc()) {
            $TerminIDs[] = $row['id'];
        }
        $result = $db->query("SELECT `vlv_id` FROM `vlv_entry` WHERE `id` IN (".implode(', ',$TerminIDs).") GROUP BY `vlv_id`");
        while($row = $result->fetch_assoc()) {
            $command = $db->query("SELECT `title`,`author` FROM `vlv_zusammenfassung` WHERE `vlv_id` = '".$db->real_escape_string($row['vlv_id'])."';");
            $return[$row['vlv_id']] = $command->fetch_assoc();
            $command = $db->query("SELECT `id`,`location`,`type`,`rules`,`time_period`, `weekday` FROM `vlv_entry` WHERE `vlv_id` = '".$db->real_escape_string($row['vlv_id'])."';");
            $return[$row['vlv_id']]['date'] = [];
            while($entry = $command->fetch_assoc()) {
                if (!isset($return[$row['vlv_id']]['date'][$entry['type']])) {
                    $return[$row['vlv_id']]['date'][$entry['type']] = [];
                }
                $studcommand = $db->query("SELECT `studiengang`, `semester`, `seminargruppe` FROM `vlv_entry2stud` WHERE `id` = ".$entry['id']);
                while($studRow = $studcommand->fetch_assoc()) {
                    if (!isset($return[$row['vlv_id']]['date'][$entry['type']][$studRow['studiengang'] . "|" . $studRow['semester'] . "|" . $studRow['seminargruppe']])) {
                        $return[$row['vlv_id']]['date'][$entry['type']][$studRow['studiengang'] . "|" . $studRow['semester'] . "|" . $studRow['seminargruppe']] = [];
                    }
                    $return[$row['vlv_id']]['date'][$entry['type']][$studRow['studiengang']."|".$studRow['semester']."|".$studRow['seminargruppe']][] = $entry['location']." - ".$entry['weekday'].", ".$entry['time_period']." (".$entry["rules"].")";
                }
            }
        }
        uasort($return,"vlvSort");
        echo json_encode($return);
        break;
    case 'chooseEvent':
        if (!$main->getUid()) {
            die();
        }
        $return = [];
        if(\filter_has_var(INPUT_POST,'vlvId')) {
            if(\filter_has_var(INPUT_POST,'vlvType') AND \filter_has_var(INPUT_POST,'vlvStudi')) {
                list($sgang,$semester,$gruppe) = explode('|',\filter_input(INPUT_POST,'vlvStudi'));
                $db->real_query("INSERT INTO `user_rules` (`uid`, `vlv_id`,`vlv_studiengang`,`vlv_semester`,`vlv_seminargruppe`,`type`) ".
                    "VALUES ('".$db->real_escape_string($_SESSION['uid'])."','".$db->real_escape_string(\filter_input(INPUT_POST,'vlvId'))."','".$db->real_escape_string($sgang)."','".$db->real_escape_string($semester)."','".$db->real_escape_string($gruppe)."','".$db->real_escape_string(\filter_input(INPUT_POST,'vlvType'))."')".
                    "ON DUPLICATE KEY UPDATE `vlv_studiengang` = '".$db->real_escape_string($sgang)."',`vlv_semester`='".$db->real_escape_string($semester)."',`vlv_seminargruppe` = '".$db->real_escape_string($gruppe)."'");
            } elseif(\filter_has_var(INPUT_POST,'sgang') AND \filter_has_var(INPUT_POST,'semester') AND \filter_has_var(INPUT_POST,'group')) {
                // Prüfe, ob bereits ein Eintrag besteht:
                $db->real_query("DELETE FROM `user_rules` WHERE `uid` = '".$db->real_escape_string($_SESSION['uid'])."' AND `vlv_id` = '".$db->real_escape_string(\filter_input(INPUT_POST,'vlvId'))."' AND (".
                    "(`tpye` = NULL OR (`vlv_studiengang` = '".$db->real_escape_string(\filter_input(INPUT_POST,'sgang'))."' AND `vlv_semester` = '".$db->real_escape_string(\filter_input(INPUT_POST,'semester'))."' AND `vlv_seminargruppe` = '".$db->real_escape_string(\filter_input(INPUT_POST,'group'))."'))");
                // Füge neue Werte ein
                $db->real_query("INSERT INTO `user_rules` (`uid`, `vlv_id`,`vlv_studiengang`,`vlv_semester`,`vlv_seminargruppe`,`type`) ".
                    "VALUES ('".$db->real_escape_string($_SESSION['uid'])."','".$db->real_escape_string(\filter_input(INPUT_POST,'vlvId'))."','".$db->real_escape_string(\filter_input(INPUT_POST,'sgang'))."','".$db->real_escape_string(\filter_input(INPUT_POST,'semester'))."','".$db->real_escape_string(\filter_input(INPUT_POST,'group'))."','')");
                $return['sql'] = "INSERT INTO `user_rules` (`uid`, `vlv_id`,`vlv_studiengang`,`vlv_semester`,`vlv_seminargruppe`,`type`) ".
                    "VALUES ('".$db->real_escape_string($_SESSION['uid'])."','".$db->real_escape_string(\filter_input(INPUT_POST,'vlvId'))."','".$db->real_escape_string(\filter_input(INPUT_POST,'sgang'))."','".$db->real_escape_string(\filter_input(INPUT_POST,'semester'))."','".$db->real_escape_string(\filter_input(INPUT_POST,'group'))."','')";
            }
            $db->query("UPDATE `user` SET `lastupdate` = CURRENT_TIMESTAMP() WHERE `uid` = ".$main->getUid());
        }
        echo json_encode($return);
        break;
    case 'unChooseEvent':
        if(!$main->getUid()) die();
        $return = [];
        if(\filter_has_var(INPUT_POST,'vlvId')) {
            if(\filter_has_var(INPUT_POST,'vlvType') AND \filter_has_var(INPUT_POST,'vlvStudi')) {
                list($sgang,$semester,$gruppe) = explode('|',\filter_input(INPUT_POST,'vlvStudi'));
                $db->real_query("DELETE FROM `user_rules` WHERE `uid` = '".$db->real_escape_string($_SESSION['uid'])."' AND `vlv_id` = '".$db->real_escape_string(\filter_input(INPUT_POST,'vlvId'))."' AND `type` = '".$db->real_escape_string(\filter_input(INPUT_POST,'vlvType'))."' AND".
                    "`vlv_studiengang` = '".$db->real_escape_string($sgang)."' AND `vlv_semester` = '".$db->real_escape_string($semester)."' AND `vlv_seminargruppe` = '".$db->real_escape_string($gruppe)."'");
            }
            elseif(\filter_has_var(INPUT_POST,'sgang') AND \filter_has_var(INPUT_POST,'semester') AND \filter_has_var(INPUT_POST,'group')) {
                $db->real_query("DELETE FROM `user_rules` WHERE `uid` = '".$db->real_escape_string($_SESSION['uid'])."' AND `vlv_id` = '".$db->real_escape_string(\filter_input(INPUT_POST,'vlvId'))."' AND ".
                    "`vlv_studiengang` = '".$db->real_escape_string(\filter_input(INPUT_POST,'sgang'))."' AND `vlv_semester` = '".$db->real_escape_string(\filter_input(INPUT_POST,'semester'))."' AND `vlv_seminargruppe` = '".$db->real_escape_string(\filter_input(INPUT_POST,'group'))."'");
            }
            $db->query("UPDATE `user` SET `lastupdate` = CURRENT_TIMESTAMP() WHERE `uid` = ".$main->getUid());
        }
        echo json_encode($return);
        break;
    case 'vlvOwns':
        if(!$main->getUid()) {
            die();
        }
        $return['own'] = [];
            $command = $db->query("SELECT `vlv_id`,`vlv_studiengang`,`vlv_semester`,`vlv_seminargruppe`,`type` FROM `user_rules` WHERE `uid` = '".$db->real_escape_string($_SESSION['uid'])."'");
            while($row = $command->fetch_assoc()) {
                if(!isset($return['own'][$row['vlv_id']])) {
                $return['own'][$row['vlv_id']] = [];
            }
            if (strlen($row['type']) === 0) {
                $return['own'][$row['vlv_id']]['root'] = $row['vlv_studiengang'] . "|" . $row['vlv_semester'] . "|" . $row['vlv_seminargruppe'];
            } else {
                $return['own'][$row['vlv_id']][$row['type']] = $row['vlv_studiengang'] . "|" . $row['vlv_semester'] . "|" . $row['vlv_seminargruppe'];
            }
        }
        echo json_encode($return);
        break;
    case 'changeICal':
        if(!$main->getUid()) {
            die();
        }
        $i = 0;
        do {
            $ical = md5($i.time().$main->getUser());
            $command = $db->query("SELECT `uid` FROM `user` WHERE `iCal_string` = '".$db->real_escape_string($ical)."'");
            $i++;
        } while($command->num_rows > 0);
        $db->real_query("UPDATE `user` SET `iCal_string` = '".$db->real_escape_string($ical)."' WHERE `uid` = '".$db->real_escape_string($main->getUid())."'");
        echo json_encode([]);
        break;
    case 'litFind':
        $return = [];
        $return['objects'] = [];
        if(\filter_has_var(INPUT_POST,'query')) {
            require_once("amazon.php");
            if(preg_match("/^\s*ISBN\s*(.+)\s*$/i",\filter_input(INPUT_POST,'query'),$match)) {
                $return['test'] = true;
                $return['amazon'] = amazon_isbn_search(preg_replace("/\D/","",$match[1]));
            }
            else {
                $return['amazon'] = amazon_search(\filter_input(INPUT_POST,'query'));
            }
            $find = $return['amazon'];
            if(isset($find->Items)) {
                if(isset($find->Items->TotalResults)) {
                    for($i = 0; $i < $find->Items->TotalResults; $i++) {
                        if(strlen($find->Items->Item[$i]->ItemAttributes->ISBN . "") > 0) {
                            $return['objects'][] = makeIsbnReturn($find->Items->Item[$i]);
                        }
                    }
                } elseif(isset($find->Items->Item)) {
                    if(strlen($find->Items->Item->ItemAttributes->ISBN . "") > 0) {
                        $return['objects'][] = makeIsbnReturn($find->Items->Item);
                    }
                }
            }
            if(preg_match("/^\s*ISBN\s*(.+)\s*$/i",\filter_input(INPUT_POST,'query'),$match)) {
                $return['test'] = true;
                $return['amazonCom'] = amazon_isbn_search(preg_replace("/\D/","",$match[1]),"com");
            }
            else {
                $return['amazonCom'] = amazon_search(\filter_input(INPUT_POST,'query'),"com");
            }
            $find = $return['amazonCom'];
            if(isset($find->Items)) {
                if(isset($find->Items->TotalResults)) {
                    for($i = 0; $i < $find->Items->TotalResults; $i++) {
                        if(strlen($find->Items->Item[$i]->ItemAttributes->ISBN . "") > 0) {
                            $return['objects'][] = makeIsbnReturn($find->Items->Item[$i]);
                        }
                    }
                } elseif(isset($find->Items->Item)) {
                    if(strlen($find->Items->Item->ItemAttributes->ISBN . "") > 0) {
                        $return['objects'][] = makeIsbnReturn($find->Items->Item);
                    }
                }
            }
        }
        echo json_encode($return);
        break;
    case 'changePassword':
        $return = [];
        if (\filter_input(INPUT_POST, 'vlvPassword-password') != \filter_input(INPUT_POST, 'vlvPassword-password-verify')) {
            $return["error"] = "Beide Passwörter stimmen nicht überein";
        } elseif (
                !preg_match('/[a-z]+/', \filter_input(INPUT_POST, 'vlvPassword-password')) OR ! preg_match('/[A-Z]+/', \filter_input(INPUT_POST, 'vlvPassword-password')) OR ! preg_match('/[0-9]+/', \filter_input(INPUT_POST, 'vlvPassword-password'))) {
            $return["error"] = "Das Passwort muss aus Groß- und Kleinbuchstaben (a-z und A-Z) und aus Ziffern bestehen. Es kann Sonderzeichen (inkl. Umlaute enthalten)";
        } elseif ((strlen(\filter_input(INPUT_POST, 'vlvPassword-password')) < 6) OR ( strlen(\filter_input(INPUT_POST, 'vlvPassword-password')) > 32)) {
            $return["error"] = "Das Passwort muss 6 oder mehr Zeichen haben. Maximal 32 Zeichen sind erlaubt.";
        } else {
            $db->real_query("UPDATE `user` SET `password` = '" . $db->real_escape_string(password_hash(\filter_input(INPUT_POST, 'vlvPassword-password'), PASSWORD_DEFAULT)) . "' WHERE `uid` = '" . $db->real_escape_string($main->getUid()) . "'");
            $return["content"] = "<i>changed</i>";
        }
        echo json_encode($return);
        break;
    default:
        echo json_encode([]);
        break;
}

function makeIsbnReturn($amazon) {
    $author = [];
    foreach ($amazon->ItemAttributes->Author as $a) {
        $author[] = $a;
    }
    $string = "<img width='".$amazon->MediumImage->Width."' src='".$amazon->MediumImage->URL."' /> ".implode(', ',$author).": ".$amazon->ItemAttributes->Title.". ".$amazon->ItemAttributes->Publisher." (ISBN: ".$amazon->ItemAttributes->ISBN.", ".$amazon->ItemAttributes->PublicationDate.")";
    return [
            "ISBN"=>$amazon->ItemAttributes->ISBN."",
            "string"=>$string
    ];
}