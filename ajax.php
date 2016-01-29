<?php

require_once("config.php");

header('Content-type: application/json');
if(!isset($_POST['type'])) {
	header("HTTP/1.0 400 Bad Request");
	exit;
}

function vlvSort($a,$b) {
	if ($a['title'] == $b['title']) {
		return 0;
	}
	return ($a['title'] < $b['title']) ? -1 : 1;
}

switch($_POST['type']) {
	case 'vlv':
		if(!isset($_POST['Studiengang']) OR !isset($_POST['Semester'])) {
			header("HTTP/1.0 400 Bad Request");
			exit;
		}
		$_SESSION['studiengang'] = $_POST['Studiengang'];
		$_SESSION['semester'] = $_POST['Semester'];
		$command = "SELECT `id` FROM `vlv_entry2stud` WHERE `studiengang` = '".$db->real_escape_string($_POST['Studiengang']).
			"' AND `semester` = '".$db->real_escape_string($_POST['Semester'])."'";
		if(isset($_POST['Gruppe'])) {
			$command .= " AND `seminargruppe` = '".$db->real_escape_string($_POST['Gruppe'])."'";
			$_SESSION['gruppe'] = $_POST['Gruppe'];
		}
		else
			unset($_SESSION['gruppe']);
		#print $command;
		
		$result = $db->query($command);
		$TerminIDs = array();
		while($row = $result->fetch_assoc())
			$TerminIDs[] = $row;
		$TerminIDsArray = array();
		foreach($TerminIDs as $TerminID)
			$TerminIDsArray[] = $TerminID['id'];
		$result = $db->query("SELECT `id`, UNIX_TIMESTAMP(`from`) as `from`, UNIX_TIMESTAMP(`to`) as `to` FROM `vlv_entry2date` ".
				"WHERE `id` IN (".implode(', ',$TerminIDsArray).") AND `to` > NOW() ORDER BY `from` LIMIT 100");
		$Termine = array();
		while($row = $result->fetch_assoc())
			$Termine[] = $row;
		$return = array();
		$return['dates'] = $Termine;
		$TerminIDsArray = array();
		foreach($Termine as $Termin)
			$TerminIDsArray[] = $Termin['id'];
		
		$return['content'] = $main->getVLVdata($TerminIDsArray);
		
		echo json_encode($return);
		break;
	case 'auswahl':
		if(!isset($_POST['Studiengang']) OR !isset($_POST['Semester'])) {
			header("HTTP/1.0 400 Bad Request");
			exit;
		}
		$return = array();
		$_SESSION['studiengang'] = $_POST['Studiengang'];
		$_SESSION['semester'] = $_POST['Semester'];
		$command = "SELECT `id` FROM `vlv_entry2stud` WHERE `studiengang` = '".$db->real_escape_string($_POST['Studiengang']).
			"' AND `semester` = '".$db->real_escape_string($_POST['Semester'])."'";
		if(isset($_POST['Gruppe'])) {
			$command .= " AND `seminargruppe` = '".$db->real_escape_string($_POST['Gruppe'])."'";
			$_SESSION['gruppe'] = $_POST['Gruppe'];
		}
		else
			unset($_SESSION['gruppe']);
		
		$result = $db->query($command);
		$TerminIDs = array();
		while($row = $result->fetch_assoc())
			$TerminIDs[] = $row['id'];
		$result = $db->query("SELECT `vlv_id` FROM `vlv_entry` WHERE `id` IN (".implode(', ',$TerminIDs).") GROUP BY `vlv_id`");
		while($row = $result->fetch_assoc()) {
			$command = $db->query("SELECT `title`,`author` FROM `vlv_zusammenfassung` WHERE `vlv_id` = '".$db->real_escape_string($row['vlv_id'])."';");
			$return[$row['vlv_id']] = $command->fetch_assoc();
			$command = $db->query("SELECT `id`,`location`,`type`,`rules`,`time_period`, `weekday` FROM `vlv_entry` WHERE `vlv_id` = '".$db->real_escape_string($row['vlv_id'])."';");
			$return[$row['vlv_id']]['date'] = array();
			while($entry = $command->fetch_assoc()) {
				if (!isset($return[$row['vlv_id']]['date'][$entry['type']]))
					$return[$row['vlv_id']]['date'][$entry['type']] = array();
				$studcommand = $db->query("SELECT `studiengang`, `semester`, `seminargruppe` FROM `vlv_entry2stud` WHERE `id` = ".$entry['id']);
				while($studRow = $studcommand->fetch_assoc()) {
					if (!isset($return[$row['vlv_id']]['date'][$entry['type']][$studRow['studiengang']."|".$studRow['semester']."|".$studRow['seminargruppe']]))
						$return[$row['vlv_id']]['date'][$entry['type']][$studRow['studiengang']."|".$studRow['semester']."|".$studRow['seminargruppe']] = array();
						$return[$row['vlv_id']]['date'][$entry['type']][$studRow['studiengang']."|".$studRow['semester']."|".$studRow['seminargruppe']][] = $entry['location']." - ".$entry['weekday'].", ".$entry['time_period']." (".$entry["rules"].")";
				}
			}
		}
		uasort($return,"vlvSort");
		echo json_encode($return);
		break;
	case 'chooseEvent':
		if(!$main->getUid()) die();
		$return = array();
		if(isset($_POST['vlvId'])) {
			if(isset($_POST['vlvType']) AND isset($_POST['vlvStudi'])) {
				list($sgang,$semester,$gruppe) = explode('|',$_POST['vlvStudi']);
				$db->real_query("INSERT INTO `user_rules` (`uid`, `vlv_id`,`vlv_studiengang`,`vlv_semester`,`vlv_seminargruppe`,`type`) ".
					"VALUES ('".$db->real_escape_string($_SESSION['uid'])."','".$db->real_escape_string($_POST['vlvId'])."','".$db->real_escape_string($sgang)."','".$db->real_escape_string($semester)."','".$db->real_escape_string($gruppe)."','".$db->real_escape_string($_POST['vlvType'])."')".
					"ON DUPLICATE KEY UPDATE `vlv_studiengang` = '".$db->real_escape_string($sgang)."',`vlv_semester`='".$db->real_escape_string($semester)."',`vlv_seminargruppe` = '".$db->real_escape_string($gruppe)."'");
			}
			elseif(isset($_POST['sgang']) AND isset($_POST['semester']) AND isset($_POST['group'])) {
				// Prüfe, ob bereits ein Eintrag besteht:
				$db->real_query("DELETE FROM `user_rules` WHERE `uid` = '".$db->real_escape_string($_SESSION['uid'])."' AND `vlv_id` = '".$db->real_escape_string($_POST['vlvId'])."' AND (".
					"(`tpye` = NULL OR (`vlv_studiengang` = '".$db->real_escape_string($_POST['sgang'])."' AND `vlv_semester` = '".$db->real_escape_string($_POST['semester'])."' AND `vlv_seminargruppe` = '".$db->real_escape_string($_POST['group'])."'))");
				// Füge neue Werte ein
				$db->real_query("INSERT INTO `user_rules` (`uid`, `vlv_id`,`vlv_studiengang`,`vlv_semester`,`vlv_seminargruppe`,`type`) ".
					"VALUES ('".$db->real_escape_string($_SESSION['uid'])."','".$db->real_escape_string($_POST['vlvId'])."','".$db->real_escape_string($_POST['sgang'])."','".$db->real_escape_string($_POST['semester'])."','".$db->real_escape_string($_POST['group'])."','')");
				$return['sql'] = "INSERT INTO `user_rules` (`uid`, `vlv_id`,`vlv_studiengang`,`vlv_semester`,`vlv_seminargruppe`,`type`) ".
					"VALUES ('".$db->real_escape_string($_SESSION['uid'])."','".$db->real_escape_string($_POST['vlvId'])."','".$db->real_escape_string($_POST['sgang'])."','".$db->real_escape_string($_POST['semester'])."','".$db->real_escape_string($_POST['group'])."','')";
			}
			$db->query("UPDATE `user` SET `lastupdate` = CURRENT_TIMESTAMP() WHERE `uid` = ".$main->getUid());
		}
		echo json_encode($return);
		break;
	case 'unChooseEvent':
		if(!$main->getUid()) die();
		$return = array();
		if(isset($_POST['vlvId'])) {
			if(isset($_POST['vlvType']) AND isset($_POST['vlvStudi'])) {
				list($sgang,$semester,$gruppe) = explode('|',$_POST['vlvStudi']);
				$db->real_query("DELETE FROM `user_rules` WHERE `uid` = '".$db->real_escape_string($_SESSION['uid'])."' AND `vlv_id` = '".$db->real_escape_string($_POST['vlvId'])."' AND `type` = '".$db->real_escape_string($_POST['vlvType'])."' AND".
					"`vlv_studiengang` = '".$db->real_escape_string($sgang)."' AND `vlv_semester` = '".$db->real_escape_string($semester)."' AND `vlv_seminargruppe` = '".$db->real_escape_string($gruppe)."'");
			}
			elseif(isset($_POST['sgang']) AND isset($_POST['semester']) AND isset($_POST['group'])) {
				$db->real_query("DELETE FROM `user_rules` WHERE `uid` = '".$db->real_escape_string($_SESSION['uid'])."' AND `vlv_id` = '".$db->real_escape_string($_POST['vlvId'])."' AND ".
					"`vlv_studiengang` = '".$db->real_escape_string($_POST['sgang'])."' AND `vlv_semester` = '".$db->real_escape_string($_POST['semester'])."' AND `vlv_seminargruppe` = '".$db->real_escape_string($_POST['group'])."'");
			}
			$db->query("UPDATE `user` SET `lastupdate` = CURRENT_TIMESTAMP() WHERE `uid` = ".$main->getUid());
		}
		echo json_encode($return);
		break;
	case 'vlvOwns':
		if(!$main->getUid()) die();
		$return['own'] = array();
		$command = $db->query("SELECT `vlv_id`,`vlv_studiengang`,`vlv_semester`,`vlv_seminargruppe`,`type` FROM `user_rules` WHERE `uid` = '".$db->real_escape_string($_SESSION['uid'])."'");
		while($row = $command->fetch_assoc()) {
			if(!isset($return['own'][$row['vlv_id']]))
				$return['own'][$row['vlv_id']] = array();
			if(strlen($row['type'])==0)
				$return['own'][$row['vlv_id']]['root'] = $row['vlv_studiengang']."|".$row['vlv_semester']."|".$row['vlv_seminargruppe'];
			else
				$return['own'][$row['vlv_id']][$row['type']] = $row['vlv_studiengang']."|".$row['vlv_semester']."|".$row['vlv_seminargruppe'];
		}
		echo json_encode($return);
		break;
	case 'changeICal':
		if(!$main->getUid()) die();
		$i = 0;
		do {
			$ical = md5($i.time().$main->getUser());
			$command = $db->query("SELECT `uid` FROM `user` WHERE `iCal_string` = '".$db->real_escape_string($ical)."'");
			$i++;
		} while($command->num_rows > 0);
		$db->real_query("UPDATE `user` SET `iCal_string` = '".$db->real_escape_string($ical)."' WHERE `uid` = '".$db->real_escape_string($main->getUid())."'");
		echo json_encode(array());
		break;
	case 'litFind':
		$return = array();
		$return['objects'] = array();
		if(isset($_POST['query'])) {
			require_once("amazon.php");
			if(preg_match("/^\s*ISBN\s*(.+)\s*$/i",$_POST['query'],$match)) {
				$return['test'] = true;
				$return['amazon'] = amazon_isbn_search(preg_replace("/\D/","",$match[1]));
			}
			else {
				$return['amazon'] = amazon_search($_POST['query']);
			}
			$find = $return['amazon'];
			if(isset($find->Items)) {
				if(isset($find->Items->TotalResults)) {
					for($i = 0; $i < $find->Items->TotalResults; $i++) {
						if(strlen($find->Items->Item[$i]->ItemAttributes->ISBN."")>0)
							$return['objects'][] = makeIsbnReturn($find->Items->Item[$i]);
						
					}
				}
				elseif(isset($find->Items->Item)) {
					if(strlen($find->Items->Item->ItemAttributes->ISBN."")>0)
						$return['objects'][] = makeIsbnReturn($find->Items->Item);
				}
			}
			if(preg_match("/^\s*ISBN\s*(.+)\s*$/i",$_POST['query'],$match)) {
				$return['test'] = true;
				$return['amazonCom'] = amazon_isbn_search(preg_replace("/\D/","",$match[1]),"com");
			}
			else {
				$return['amazonCom'] = amazon_search($_POST['query'],"com");
			}
			$find = $return['amazonCom'];
			if(isset($find->Items)) {
				if(isset($find->Items->TotalResults)) {
					for($i = 0; $i < $find->Items->TotalResults; $i++) {
						if(strlen($find->Items->Item[$i]->ItemAttributes->ISBN."")>0)
							$return['objects'][] = makeIsbnReturn($find->Items->Item[$i]);
						
					}
				}
				elseif(isset($find->Items->Item)) {
					if(strlen($find->Items->Item->ItemAttributes->ISBN."")>0)
						$return['objects'][] = makeIsbnReturn($find->Items->Item);
				}
			}
		}
		echo json_encode($return);
		break;
	case 'changePassword':
		$return = array();
		if($_POST['vlvPassword-password'] != $_POST['vlvPassword-password-verify'])
			$return["error"] = "Beide Passwörter stimmen nicht überein";
			
		elseif(
			!preg_match('/[a-z]+/',$_POST['vlvPassword-password']) OR
			!preg_match('/[A-Z]+/',$_POST['vlvPassword-password']) OR
			!preg_match('/[0-9]+/',$_POST['vlvPassword-password']))
			$return["error"] = "Das Passwort muss aus Groß- und Kleinbuchstaben (a-z und A-Z) und aus Ziffern bestehen. Es kann Sonderzeichen (inkl. Umlaute enthalten)";
		elseif((strlen($_POST['vlvPassword-password'])<6) OR (strlen($_POST['vlvPassword-password'])>32))
			$return["error"] = "Das Passwort muss 6 oder mehr Zeichen haben. Maximal 32 Zeichen sind erlaubt.";
		else {
			$db->real_query("UPDATE `user` SET `password` = '".$db->real_escape_string(password_hash($_POST['vlvPassword-password'],PASSWORD_DEFAULT))."' WHERE `uid` = '".$db->real_escape_string($main->getUid())."'");
			$return["content"] = "<i>changed</i>";
		}
		echo json_encode($return);
		break;
	default:
		echo json_encode(array());
		break;
}

function makeIsbnReturn($amazon) {
	$author = array();
	foreach($amazon->ItemAttributes->Author as $a)
		$author[] = $a;
	$string = "<img width='".$amazon->MediumImage->Width."' src='".$amazon->MediumImage->URL."' /> ".implode(', ',$author).": ".$amazon->ItemAttributes->Title.". ".$amazon->ItemAttributes->Publisher." (ISBN: ".$amazon->ItemAttributes->ISBN.", ".$amazon->ItemAttributes->PublicationDate.")";
	return array(
			"ISBN"=>$amazon->ItemAttributes->ISBN."",
			"string"=>$string
		);
}