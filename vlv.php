<?php
/*
 * Auslesen des Vorlesungsverzeichnisses
*/
set_time_limit(300);
date_default_timezone_set('Europe/Berlin');
$ids[] = array('id' => 330, 'test' => strtotime('01.10.2015'), 'year' => 2015 ,'ss' => false);
$ids[] = array('id' => 6, 'test' => strtotime('01.04.2016'), 'year' => 2016 ,'ss' => true);

$test = strtotime('01.10.2011');
$christmasdec = '20141221';
$christmasjan = '20150105';
$global_year = 2015;
$global_ss = false;

require_once('db.php');

class VLV_Entry {
	private $db;
	private $vlv_id;
	
	function __construct($vlv_id,$title,$author,$db,$url,$descr = "") {
		$this->db = $db;
		$array['vlv_id'] = $vlv_id;
		$array['title'] = $title;
		$array['description'] = $descr;
		$array['author'] = $author;
		$array['url'] = $url;
		$this->db->insert('vlv_zusammenfassung_tmp',$array);
		$this->vlv_id = $vlv_id;
	}
	
	public function more_entries($array) {
                $match = $match1 = $match2 = [];
		if(preg_match('/^\s*(.*?)\s*[:]*\s*$/msi',$array['type'],$match)) {
			$insert['type'] = $match[1];
			$insert['location'] = $array['location'];
			$insert['vlv_id'] = $this->vlv_id;
			$insert['rules'] = $array['period'];
			$insert['time_period'] = $array['time_period'];
			$insert['weekday'] = $array['wday'];
			list($lastChangeTag, $lastChangeMonat,$lastChangeJahr) = explode('.',$array['last_change']);
			$lastChangeJahr += 2000;
			$insert['last_change'] = $lastChangeJahr."-".$lastChangeMonat."-".$lastChangeTag;
			$this->db->insert('vlv_entry_tmp',$insert);
			$id = $this->db->last_id();
			
			// Studis eintragen
			$stud_array = explode(', ',$array['group']);
			foreach($stud_array as $s) {
				if(preg_match('/^(.*)\s+(\d+)\.FS\s*(.*)$/m',$s,$match2)) {
					$insert_stud['id'] = $id;
					$insert_stud['studiengang'] = $match2[1];
					$insert_stud['seminargruppe'] = $match2[3];
					$insert_stud['semester'] = $match2[2];
					$this->db->insert('vlv_entry2stud_tmp',$insert_stud);
				}
			}
			
			//Termine hinzufügen
			$dates = array();
			if(strtotime($array['period']))
				$dates[] = strtotime($array['period']);
			elseif (preg_match('/[U]\s*\((.*)\)/ms',$array['period'],$match1))
				$dates = $this->make_dates($match1[1],-2,$array['wday']);
			elseif (preg_match('/[G]\s*\((.*)\)/ms',$array['period'],$match1))
				$dates = $this->make_dates($match1[1],2,$array['wday']);
			else
				$dates = $this->make_dates($array['period'],1,$array['wday']);
			$times = explode('-',str_replace('.',':',$array['time_period']));
			if(count($times)==2) {
				foreach($dates as $d) {
					$insert_date['id'] = $id;
					$insert_date['from'] = date('c',strtotime(date('d.m.Y',$d)." ".$times[0]));
					$insert_date['to'] = date('c',strtotime(date('d.m.Y',$d)." ".$times[1]));
					global $test;
					global $christmasdec;
					global $christmasjan;
					if((strtotime($insert_date['from'])<$test)||(strtotime($insert_date['to'])<$test)||((date('Ymd',$d)>$christmasdec)&&(date('Ymd',$d)<$christmasjan)))
						continue;
					#print_r($array);
					$this->db->insert('vlv_entry2date_tmp',$insert_date);
					#exit;
				}
			}
		}
	}
	
	private function getDateWeek($year, $week, $weekdayIndex = null)
	{
		try {
			$stamp  = mktime(12, 0, 0, 1, 4, $year); // DIN 1355-1 / ISO 8601, january 4th is 1st week
			$adjust = ($week - 1); // if you want week = 1, your week offset relative to $stamp is 0
			if ($adjust != 0) {
				// adjust week
				$adjust = $adjust > 0 ? "+$adjust" : $adjust;
				$stamp  = strtotime("$adjust week", $stamp);
			}
			if ($weekdayIndex !== null) {
				// adjust day
				$adjust = $weekdayIndex - date('N', $stamp);
				if ($adjust != 0) {
					$adjust = $adjust > 0 ? "+$adjust" : $adjust;
					$stamp  = strtotime("$adjust day", $stamp);
				}
			}
			return $stamp;
		} catch (Exception $e) {
			throw new Exception("could not determine appropriate date [year=$year, week=$week]", 0, $e);
		}
	}

	private function make_dates($string,$diff,$wday) {
		global $global_year;
		global $global_ss;
                $odd = -1;
                if($diff === 2)
                    $odd = 0;
                elseif($diff === -2) {
                    $odd = 1;
                    $diff = 2;
                }
		if(preg_match('/Montag/i',$wday))
			$wday = 1;
		elseif(preg_match('/Dienstag/i',$wday))
			$wday = 2;
		elseif(preg_match('/Mittwoch/i',$wday))
			$wday = 3;
		elseif(preg_match('/Donnerstag/i',$wday))
			$wday = 4;
		elseif(preg_match('/Freitag/i',$wday))
			$wday = 5;
		elseif(preg_match('/Samstag/i',$wday))
			$wday = 6;
		elseif(preg_match('/Sonntag/i',$wday))
			$wday = 7;
		else
			$wday = 0;
		$return_array = array();
		$string_array1 = explode(',',$string);
		foreach($string_array1 as $s1) {
			$string_array2 = explode(';',$s1);
			foreach($string_array2 as $s2) {
				if(preg_match_all('/(\d+)\.\s*(KW(\s*\d{4})?)?\s*-\s*(\d+)\./ms',$s2,$match)) {
					for($i=0;$i<count($match[0]);$i++){
						if($global_ss)
							$year = $global_year;
						elseif($match[1][$i]<date('W',strtotime('01.10.'.$global_year)))
							$year = $global_year+1;
						else
							$year = $global_year;
						if($match[1][$i]>0)
							$start = $this->getDateWeek($year, $match[1][$i],$wday);
						else
							return;
						$j=0;
						do {
							if($j==0)
                                                            $date = $start;
							else {
                                                            $date = strtotime('+'. $j*$diff*7 .' days',$start);
                                                            if($odd !== -1) {
                                                                if(date("W",$date)%2 !== $odd)
                                                                    $date = strtotime('+'. (1+$j*$diff)*7 .' days',$start);
                                                            }
                                                        }
							
							if((($match[4][$i]>$match[1][$i])&&(date('W',$date) <= $match[4][$i]))||(($match[4][$i]<$match[1][$i])&&((date('W',$date) <= $match[4][$i])||(date('W',$date) >= $match[1][$i]))))
								$return_array[] = $date;
							else
								break 1;
							$j++;
						} while(true);
					}
				}
				elseif(preg_match_all('/(\d+)\./ms',$s2,$match)) {
					for($i=0;$i<count($match[0]);$i++){
						if($global_ss)
							$year = $global_year;
						elseif($match[1][$i]<date('W',strtotime('01.10.'.$global_year)))
							$year = $global_year+1;
						else
							$year = $global_year;
						for($i=0;$i<count($match[0]);$i++)
							$return_array[] = $this->getDateWeek($year, $match[1][$i],$wday);
					}
				}
			}
		}
		return $return_array;
	}
}

function VLV($id) {
	global $db;
	$url = "http://www.tu-ilmenau.de/vlv/index.php?id=".$id;
	//Eintagsarray
	$entries = array();
	$file = utf8_encode(file_get_contents($url));

	#print date('c',strtotime('+41 weeks mondays',strtotime('next monday',strtotime('03.01.2011'))));

	//parsen
	if(preg_match_all('/\<a[^>]*href=\"(index\.php\?id\='.$id.'\&[amp;]*funccall\=1\&[amp;]*sgkurz=.*?&[amp;]*vers=text&[amp;]*studiengang=[^&]*?)\"\>.*?\<\/a\>/',$file,$match)) {
		//Studiengangsseiten parsen
		if((isset($match[1]))&&(count($match[1])>0)) {
			foreach($match[1] as $m) {
				$m = str_replace('&amp;','&',$m);
				$sg_url = "http://www.tu-ilmenau.de/vlv/".$m;
				$sg_file = file_get_contents($sg_url);
				if(preg_match_all('/(http\:\/\/wcms3\.rz\.tu\-ilmenau\.de\/\%7Egoettlich\/elvvi\/[a-zA-z]*\/list\/fachseite\.php\?fid\=[a-zA-Z0-9]+)/',$sg_file,$sg_match)) {
					$entries = array_merge($sg_match[1],$entries);
				}
			}
		}
	}

	#print_r($entries);
	#exit;
	//Fächer abfragen
	$entries = array_unique($entries);
	natsort($entries);

	foreach($entries as $entry) {
		#sleep(1);
		//Seite aufrufen
		$file = utf8_encode(file_get_contents($entry));
		print $entry."\n";
		//Seite parsen
		$pageMatch = array();
		//Seite parsen
		$match1 = array();
		//Allgemeine Daten finden
		if(($id==330)&&(!preg_match('/<tr[^>]*>\s*<td[^>]*>\s*<strong[^>]*>([^>]*)<\/strong>\s*<\/td>\s*<\/tr>\s*<tr[^>]*>\s*<td>\s*&nbsp;\s*<\/td>\s*<td[^>]*>([^>]*)<\/td>\s*<\/tr>/msi',$file,$match1)))
			continue;
		elseif(($id==6)&&(!preg_match('/<p[^>]*class=\"stupla_bold\"[^>]*>([^>]*)<\/p>\s*<p[^>]*>Lesende\(r\)\:\s*([^>]*)<\/p>/msi',$file,$match1))) 
			continue;

		//Inhalt
		if(preg_match('/<th>\s*Fachnummer\s*<\/th>\s*<td>(.*?)<\/td>/s',$file,$pageMatch['id'])) {
			//Titel
			preg_match('/<th>\s*Fachname\s*<\/th>\s*<td>(.*?)<\/td>/s',$file,$pageMatch['Titel']);
			//Inhalt
			preg_match('/<th>\s*Inhalt\s*<\/th>\s*<td>(.*?)<\/td>/s',$file,$pageMatch['Inhalt']);
			//Sprache
			preg_match('/<th>\s*Sprache\s*<\/th>\s*<td>(.*?)<\/td>/s',$file,$pageMatch['lang']);
			//Fachverantwortlicher
			preg_match('/<th>\s*Fachverantwortliche\(r\)\s*<\/th>\s*<td>(.*?)<\/td>/s',$file,$pageMatch['Fachverantwortlicher']);
			//Fachgebiet
			preg_match('/<th>\s*Fachgebietsnummer\s*<\/th>\s*<td>(.*?)<\/td>/s',$file,$pageMatch['Fachgebiet']);
			//LP
			preg_match('/<th>\s*Leistungspunkte\s*<\/th>\s*<td>(.*?)<\/td>/s',$file,$pageMatch['LP']);
			//Lernergebnisse
			preg_match('/<th>\s*Lernergebnisse\s*<\/th>\s*<td>(.*?)<\/td>/s',$file,$pageMatch['Lernergebnisse']);
			//Vorkenntnisse
			preg_match('/<th>\s*Vorkenntnisse\s*<\/th>\s*<td>(.*?)<\/td>/s',$file,$pageMatch['Vorkenntnisse']);
			//Abschluss
			preg_match('/<th>\s*Abschluss\s*<\/th>\s*<td>(.*?)<\/td>/s',$file,$pageMatch['exam']);
			$pageMatch2 = array();
			foreach($pageMatch as $k => $v)
				$pageMatch2[$k] = $v[1];
			$objectId = $pageMatch2['id'];
			// Erzeuge Eintrag
			$db->insert('vlv2_object_tmp',$pageMatch2);
			
			//Literatur
			preg_match('/<th>\s*Literatur\s*<\/th>\s*<td>(.*?)<\/td>/s',$file,$pageMatch['Literatur']);
			
			$liste = explode("\n",trim(html_entity_decode(strip_tags(preg_replace("/(<\s*[\/]?\s*(br|p|span|li)[^>]*>)/","\n",$pageMatch['Literatur'][1])))));
			foreach($liste as $l) {
				if(strlen(trim($l))>0) {
					if(strlen(trim($l))<255) {
						$db->insert('vlv_literatur_tmp',array('vlv_id'=>$pageMatch2['id'],"literatur_ref"=>trim($l)));
					}
					else {
						$lArray = preg_split("/(ISBN[^a-z]+)/i",trim($l),-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
						if(count($lArray)>1){
							for($i = 0; $i < count($lArray); $i = $i+2) {
								if(isset($lArray[$i+1]))
									$db->insert('vlv_literatur_tmp',array('vlv_id'=>$pageMatch2['id'],"literatur_ref"=>$lArray[$i]." ".$lArray[$i+1]));
								else
									$db->insert('vlv_literatur_tmp',array('vlv_id'=>$pageMatch2['id'],"literatur_ref"=>$lArray[$i]));
							}
						}
						else
							$db->insert('vlv_literatur_tmp',array('vlv_id'=>$pageMatch2['id'],"literatur_ref"=>trim($l)));
					}
				}
			}
		}
		else
			$objectId = "";
		
		
		
		#print "TEST1.5\n";
		if(!preg_match('/fid=([A-Za-z0-9]+)/msi',$entry,$match2))
			continue;
		
		#print "TEST2\n";
		/*if(($id==330)&&(preg_match_all('/<tr[^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>[^<]*?(<!--[^>]*-->)?[^<]*?<\/td[^>]*>\s*<\/tr[^>]*>/msi',$file,$match))) {
			//1 = Ü/V/P/K
			$last = "";
			#print_r($match);
			for($i=0;$i<count($match[0]);$i++) {
				//Wenn das Datum OK ist, den Datensatz anlegen
				if((preg_match('/\d+\.\d+\.\d+/ms',$match[3][$i]))||(preg_match('/KW/ms',$match[3][$i]))) {
					if(!isset($object)) {
						$object = new VLV_Entry($match2[1],$match1[1],$match1[2],$db,$entry,$objectId);
					}
					if(!preg_match('/^\s*&nbsp;\s*$/msi',$match[1][$i]))
						$last = $match[1][$i];
					$localarray['type'] = $last;
					$localarray['wday'] = $match[2][$i];
					$localarray['period'] = $match[3][$i];
					$localarray['time_period'] = $match[4][$i];
					$localarray['location'] = $match[5][$i];
					$localarray['group'] = $match[6][$i];
					$object->more_entries($localarray);
					unset($localarray);
				}
#				else
#					continue;
			}
		}
		elseif(($id==6)&&(preg_match_all('/<tr[^>]*>\s*<t[dh][^>]*>([^<]*?)[:]?\s*<\/t[dh][^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>[^<]*?(<!--)?\s*Ge&auml;ndert am: ([^<]*)\s*(-->)?[^<]*?<\/td[^>]*>\s*<\/tr[^>]*>/msi',$file,$match))) {
		*/
		if(preg_match_all('/<tr[^>]*>\s*<t[dh][^>]*>([^<]*?)[:]?\s*<\/t[dh][^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>([^<]*?)<\/td[^>]*>\s*<td[^>]*>[^<]*?(<!--)?\s*Ge&auml;ndert am: ([^<>]*)\s*(-->)?[^<>]*?<\/td[^>]*>\s*<\/tr[^>]*>/msi',$file,$match)) {
			//1 = Ü/V/P/K
			$last = "";
			#print_r($match);
			for($i=0;$i<count($match[0]);$i++) {
				//Wenn das Datum OK ist, den Datensatz anlegen
				#print $match[3][$i]."\n";
				if((preg_match('/\d+\.\d+\.\d+/ms',$match[3][$i]))||(preg_match('/KW/ms',$match[3][$i]))) {
					if(!isset($object))
						$object = new VLV_Entry($match2[1],$match1[1],$match1[2],$db,$entry,$objectId,$match[8][$i]);
					if(!preg_match('/^\s*&nbsp;\s*$/msi',$match[1][$i]))
						$last = $match[1][$i];
					$localarray['type'] = $last;
					$localarray['wday'] = $match[2][$i];
					$localarray['period'] = $match[3][$i];
					$localarray['time_period'] = $match[4][$i];
					$localarray['location'] = $match[5][$i];
					$localarray['group'] = $match[6][$i];
					$localarray['last_change'] = $match[8][$i];
#print_r($localarray); exit;
					#print "go\n";
					$object->more_entries($localarray);
					unset($localarray);
				}
				else
					continue;
			}
		}
		if(isset($object))
			unset($object);
		
	}
}

$db = new db('localhost','studical','studical','studical');
$db->create_tmp_table("vlv_entry_tmp","vlv_entry");
$db->create_tmp_table("vlv_entry2stud_tmp","vlv_entry2stud");
$db->create_tmp_table("vlv_entry2date_tmp","vlv_entry2date");
$db->create_tmp_table("vlv_zusammenfassung_tmp","vlv_zusammenfassung");
$db->create_tmp_table("vlv_literatur_tmp","vlv_literatur");
$db->create_tmp_table("vlv2_object_tmp","vlv2_object");

// Startseite auslesen

foreach($ids as $id) {
	print "start: ".$id['id']."\n";
	$start = time();
	$test = $id['test'];
	$global_year = $id['year'];
	$global_ss = $id['ss'];
	VLV($id['id']);
	print "end: ".$id['id']." (Dauer: ".(time() - $start)."sec)\n";
}

$db->copy_table("vlv_zusammenfassung", "vlv_zusammenfassung_tmp");
$db->copy_table("vlv_entry2date", "vlv_entry2date_tmp");
$db->copy_table("vlv_entry2stud", "vlv_entry2stud_tmp");
$db->copy_table("vlv_entry", "vlv_entry_tmp");
$db->copy_table("vlv_literatur", "vlv_literatur_tmp");
$db->copy_table("vlv2_object","vlv2_object_tmp");
$db->query("INSERT INTO `sysvar` (`key`,`value`) VALUES ('lastupdate','".time()."') ON DUPLICATE KEY UPDATE `value` = '".time()."'");
?>
