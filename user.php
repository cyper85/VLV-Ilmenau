<?php

require_once('config.php');

class user {
	private $data = array();
	private $fields = array();
	private $db;
	public $login_error = "";
	private $error = false;
	
	function __construct($db) {
		$this->db = $db;
		if(isset($_SESSION['uid'])) {
			$result = $this->db->query("SELECT * FROM user WHERE uid='".$_SESSION['uid']."'");
			if(count($result)==1)
				$this->data = $result[0];
			else
				unset($_SESSION['uid']);
		}
		
		global $userfields;
		$this->fields = $userfields;
	}
	
	private function rand_char() { 
		$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUFWXYZ?&%$§/()[]{}\+*-_';
		$n = rand(0,(strlen($chars)-1)); 
		return $chars{$n};
	}

	private function rand_string($length=20) {
		$str = ''; 
		for($n=0;$n<$length;$n++) { 
			$str .= $this->rand_char(); 
		} 
		return $str; 
	}
	
	private function set_uname($error = '', $hidden = false) {
		$string ="";
		if(isset($_POST['uname']))
			$uname = $_POST['uname'];
		elseif(isset($this->data['uname']))
			$uname = $this->data['uname'];
		else
			$uname = "";
		if($hidden)
			return "<tr>\n\t<td>Nutzername:</td>\n\t<td><input type='hidden' value='".$uname."' name='uname' />".$uname."</td>\n</tr>\n";
		else
			return "<tr>\n\t<td><label for='uname'>Nutzername:</label></td>\n\t<td><input type='text' maxlength='10' maxsize='10' size='10' value='".$uname."' name='uname' id='uname' /><span class='error'>".$error."</span></td>\n</tr>\n";
	}
	
	private function check_uname() {
		if(isset($_POST['uname']))
			if((strlen($_POST['uname'])>4)&&(strlen($_POST['uname'])<11)) {
				if(count($this->db->query("SELECT * FROM user WHERE `uname`='".$this->db->esc($_POST['uname'])."'"))==0)
					if(preg_match('/^[a-zA-Z0-9]+$/',$_POST['uname']))
						return array('error'=>false,'data'=>$this->set_uname('',true),'db_insert' => array('uname'=>$_POST['uname']));
					else
						return array('error'=>true,'data'=>$this->set_uname('Nutzername darf nur aus Buchstaben (ohne Umlaute) und Ziffen bestehen'));
				else
					return array('error'=>true,'data'=>$this->set_uname('Nutzername existiert bereits!'));
			}
			else
				return array('error'=>true,'data'=>$this->set_uname('Nutzername muss zw. 5 und 10 Zeichen haben'));
		else
			return array('error'=>true,'data'=>$this->set_uname('Nutzername nicht gesetzt'));
	}
	
	private function set_pass($error = '', $hidden = false) {
		$string ="";
		if($hidden)
			return "<tr>\n\t<td>Passwort:</td>\n\t<td>***</td>\n</tr>\n";
		else
			return "<tr>\n\t<td><label for='pass'>Passwort:</label></td>\n\t<td><input type='password' maxlength='16' maxsize='16' size='16' name='pass' id='pass' /><span class='error'>".$error."</span></td>\n</tr>\n<tr>\n\t<td><label for='pass_w'>Passwort wiederholen:</label></td>\n\t<td><input type='password' maxlength='16' maxsize='16' size='16' name='pass_w' id='pass_w' /><span class='error'>".$error."</span></td>\n</tr>\n";
	}
	
	private function check_pass() {
		if((isset($_POST['pass']))&&(isset($_POST['pass_w'])))
			if($_POST['pass'] == $_POST['pass_w'])
				if((strlen($_POST['pass'])>4)&&(strlen($_POST['pass'])<16))
					if((preg_match('/[a-z]+/',$_POST['pass']))&&(preg_match('/[A-Z]+/',$_POST['pass']))&&(preg_match('/[0-9]+/',$_POST['pass']))) {
						$_SESSION['new_password'] = $_POST['pass'];
						return array('error'=>false,'data'=>$this->set_pass('',true),'db_insert' => array('new_password'=>$_POST['pass']));
					}
					else
						return array('error'=>true,'data'=>$this->set_pass('Nutzername muss aus Ziffern, Klein- und Großbuchstaben bestehen'));
				else
					return array('error'=>true,'data'=>$this->set_pass('Passwort muss zw. 5 und 15 Zeichen haben'));
			else
				return array('error'=>true,'data'=>$this->set_pass('Passwörter stimmen nicht überein'));
		elseif(isset($_SESSION['new_password']))
			return array('error'=>false,'data'=>$this->set_pass('',true),'db_insert' => array('new_password'=>$_SESSION['new_password']));
		else
			return array('error'=>true,'data'=>$this->set_pass('Passwort nicht gesetzt'));
	}
	
	private function set_email($error = '', $hidden = false) {
		$string ="";
		if(isset($_POST['email']))
			$email = $_POST['email'];
		elseif(isset($this->data['email']))
			$email = $this->data['email'];
		else
			$email = "";
		if($hidden)
			return "<tr>\n\t<td>Email:</td>\n\t<td><input type='hidden' value='".$email."' name='email' />".$email."</td>\n</tr>\n";
		else
			return "<tr>\n\t<td><label for='email'>Email:</label></td>\n\t<td><input type='text' maxlength='255' maxsize='255' size='30' value='".$email."' name='email' id='email' /><span class='error'>".$error."</span></td>\n</tr>\n";
	}
	
	private function check_email() {
		if(isset($_SESSION['uid']))
			$uid=$_SESSION['uid'];
		else
			$uid=0;
		if(isset($_POST['email']))
			if($this->checkmail($_POST['email'])) {
				preg_match('/^(.+)@/msi',$_POST['email'],$match);
				if(count($this->db->query("SELECT * FROM user WHERE `email` LIKE '".$this->db->esc($match[1])."@%tu-ilmenau.de' AND `block` = 1 AND`uid` <> '".$uid."'"))==0) {
					if(count($this->db->query("SELECT * FROM user WHERE `email`='".$this->db->esc($_POST['email'])."' AND `uid` <> '".$uid."'"))==0)
						return array('error'=>false,'data'=>$this->set_email('',true),'db_insert' => array('email'=>$_POST['email']));
					else
						return array('error'=>true,'data'=>$this->set_email('Emailadresse existiert bereits!'));
				}
				else
					return array('error'=>true,'data'=>$this->set_email('Emailadresse gehört zu einem geblockten Account!'));
			}
			else
				return array('error'=>true,'data'=>$this->set_email('Emailadresse ist nicht korrekt oder nicht von der TU Ilmenau.'));
		else
			return array('error'=>true,'data'=>$this->set_email('Emailadresse nicht gesetzt'));
	}
	
	private function set_jabber($error = '', $hidden = false) {
		$string ="";
		if(isset($_POST['jabber']))
			$jabber = $_POST['jabber'];
		elseif(isset($this->data['jabber']))
			$jabber = $this->data['jabber'];
		else
			$jabber = "";
		if($hidden)
			return "<tr>\n\t<td>Jabber:</td>\n\t<td><input type='hidden' value='".$jabber."' name='jabber' />".$jabber."</td>\n</tr>\n";
		else
			return "<tr>\n\t<td><label for='jabber'>Jabber:</label></td>\n\t<td><input type='text' maxlength='255' maxsize='255' size='30' value='".$jabber."' name='jabber' id='jabber' /><span class='error'>".$error."</span></td>\n</tr>\n";
	}
	
	private function check_jabber() {
		if(isset($_SESSION['uid']))
			$uid=$_SESSION['uid'];
		else
			$uid=0;
		if((isset($_POST['jabber']))&&($_POST['jabber']!=""))
			if($this->checkjid($_POST['jabber'])) {
				if(count($this->db->query("SELECT * FROM user WHERE `jabber`='".$this->db->esc($_POST['jabber'])."' AND `uid` <> '".$uid."'"))==0)
					return array('error'=>false,'data'=>$this->set_jabber('',true),'db_insert' => array('jabber'=>$_POST['jabber']));
				else
					return array('error'=>true,'data'=>$this->set_jabber('JID existiert bereits!'));
			}
			else
				return array('error'=>true,'data'=>$this->set_jabber('JID scheint nicht korrekt zu sein.'));
		else
			return array('error'=>false,'data'=>$this->set_jabber('',true),'db_insert' => array('jabber'=>''));
	}
	
	public function get_iCal() {
		#print_r($this->data);
		if ($this->data['iCal'])
			print "<li><a href='ical.php?u=".$this->data['iCal']."&k=".$this->data['iCal_string']."'>iCal</a></li>";
		return true;
	}
	
	private function set_iCal($error = '', $hidden = false) {
		$string ="";
		if(isset($_POST['iCal']))
			$iCal = $_POST['iCal'];
		elseif(isset($this->data['iCal']))
			$iCal = $this->data['iCal'];
		else
			$iCal = 0;
		if($hidden) {
			$echo = "<tr>\n\t<td>iCal erlauben:</td>\n\t<td><input type='hidden' value='".$iCal."' name='iCal' />";
			if($iCal)
				$echo .= "yes";
			else
				$echo .= "no";
			$echo .= "</td>\n</tr>\n";
			if((isset($_POST['iCal_string_renew']))AND($_POST['iCal_string_renew']))
				$echo .= "<tr>\n\t<td>iCal-Key neu setzen:</td>\n\t<td><input type='hidden' name='iCal_string_renew' value='1' checked=checked> yes</td>\n</tr>\n";
			return $echo;
		}
		else {
			$echo = "<tr>\n\t<td><label for='iCal'>iCal erlauben:</label></td>\n\t<td>";
			if($iCal)
				$echo .= "yes <input type='radio' name='iCal' value='1' checked=checked><input type='radio', name='iCal' value='0'> no";
			else
				$echo .= "yes <input type='radio' name='iCal' value='1'><input type='radio', name='iCal' value='0' checked=checked> no";
			$echo .= "<span class='error'>".$error."</span> </td>\n</tr>\n";
			$echo .= "<tr>\n\t<td><label for='iCal_string_renew'>iCal-Key neu setzen:</label></td>\n\t<td>";
			if((isset($_POST['iCal_string_renew']))AND($_POST['iCal_string_renew']))
				$echo .= "<input type='checkbox' name='iCal_string_renew' value='1' checked=checked>";
			else
				$echo .= "<input type='checkbox' name='iCal_string_renew' value='1'>";
			$echo .= "</td>\n</tr>\n";
			return $echo;
		}
	}
	
	private function zufallsstring($laenge=16) {
		//Zeichen, die im Zufallsstring vorkommen sollen
		$zeichen = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$zufalls_string = '';
		$anzahl_zeichen = strlen($zeichen);
		for($i=0;$i<$laenge;$i++)
		{
			$zufalls_string .= $zeichen[mt_rand(0, $anzahl_zeichen - 1)];
		}
		return $zufalls_string;
	}
	
	private function check_iCal() {
		$_POST['iCal'] = (boolean) $_POST['iCal'];
		if(isset($_POST['iCal_string_renew'])) {
			$_POST['iCal_string_renew'] = (boolean) $_POST['iCal_string_renew'];
			if($_POST['iCal_string_renew'])
				return array('error'=>false,'data'=>$this->set_iCal('',true),'db_insert' => array('iCal'=>$_POST['iCal'],'iCal_string'=>$this->zufallsstring()));
		}
		return array('error'=>false,'data'=>$this->set_iCal('',true),'db_insert' => array('iCal'=>$_POST['iCal']));
	}
	
	private function set_name($error = '', $hidden = false) {
		$string ="";
		if(isset($_POST['name']))
			$name = $_POST['name'];
		elseif(isset($this->data['name']))
			$name = $this->data['name'];
		else
			$name = "";
		if($hidden)
			return "<tr>\n\t<td>Name:</td>\n\t<td><input type='hidden' value='".$name."' name='name' />".$name."</td>\n</tr>\n";
		else
			return "<tr>\n\t<td><label for='name'>Name:</label></td>\n\t<td><input type='text' maxlength='255' maxsize='255' size='30' value='".$name."' name='name' id='name' /><span class='error'>".$error."</span></td>\n</tr>\n";
	}
	
	private function check_name() {
		if(isset($_POST['name']))
			if($_POST['name']!="")
				return array('error'=>false,'data'=>$this->set_name('',true),'db_insert' => array('name'=>$_POST['name']));
			else
				return array('error'=>true,'data'=>$this->set_name('Name nicht gesetzt.'));
		else
			return array('error'=>true,'data'=>$this->set_name('Name nicht gesetzt.'));
	}
	
	private function set_givenname($error = '', $hidden = false) {
		$string ="";
		if(isset($_POST['givenname']))
			$givenname = $_POST['givenname'];
		elseif(isset($this->data['givenname']))
			$givenname = $this->data['givenname'];
		else
			$givenname = "";
		if($hidden)
			return "<tr>\n\t<td>Vorname:</td>\n\t<td><input type='hidden' value='".$givenname."' name='givenname' />".$givenname."</td>\n</tr>\n";
		else
			return "<tr>\n\t<td><label for='givenname'>Vorname:</label></td>\n\t<td><input type='text' maxlength='255' maxsize='255' size='30' value='".$givenname."' name='givenname' id='givenname' /><span class='error'>".$error."</span></td>\n</tr>\n";
	}
	
	private function check_givenname() {
		if(isset($_POST['givenname']))
			if($_POST['givenname']!="")
				return array('error'=>false,'data'=>$this->set_givenname('',true),'db_insert' => array('givenname'=>$_POST['givenname']));
			else
				return array('error'=>true,'data'=>$this->set_givenname('Vorname nicht gesetzt.'));
		else
			return array('error'=>true,'data'=>$this->set_givenname('Vorname nicht gesetzt.'));
	}
	
	private function set_adress($error = '', $hidden = false) {
		$string ="";
		if(isset($_POST['adress']))
			$adress = $_POST['adress'];
		elseif(isset($this->data['adress']))
			$adress = $this->data['adress'];
		else
			$adress = "";
		if($hidden)
			return "<tr>\n\t<td>Adresse:</td>\n\t<td><input type='hidden' value='".$adress."' name='adress' />".str_replace("\n","<br/>\n",$adress)."</td>\n</tr>\n";
		else
			return "<tr>\n\t<td><label for='adress'>Adresse:</label></td>\n\t<td><textarea size='30' name='adress' id='adress'>".$adress."</textarea><span class='error'>".$error."</span></td>\n</tr>\n";
	}
	
	private function check_adress() {
		if((isset($_POST['adress']))&&($_POST['adress']!=""))
			if(strlen($_POST['adress'])<1001)
				return array('error'=>false,'data'=>$this->set_adress('',true),'db_insert' => array('adress'=>$_POST['adress']));
			else
				return array('error'=>true,'data'=>$this->set_adress('Es dürfen maximal 1000 Zeichen in der Adresse sein.'),'db_insert' => array('adress'=>$_POST['adress']));
		else
			return array('error'=>false,'data'=>$this->set_adress('',true),'db_insert' => array('adress'=>''));
	}
	
	private function set_su($error = '', $hidden = false) {
		if((isset($_SESSION['su']))&&($_SESSION['su'])) {
			$string ="";
			if(isset($_POST['su']))
				$su = $_POST['su'];
			elseif(isset($this->data['su']))
				$su = $this->data['su'];
			else
				$su = 0;
			if($hidden)
				if($su == 1)
					return "<tr>\n\t<td>SuperUser:</td>\n\t<td><input type='hidden' value='1' name='su' />yes</td>\n</tr>\n";
				else
					return "<tr>\n\t<td>SuperUser:</td>\n\t<td><input type='hidden' value='0' name='su' />yes</td>\n</tr>\n";
			else {
				$return = "<tr>\n\t<td><label for='su'>SuperUser:</label></td>\n\t<td><input type='checkbox' value='1' name='su' id='su' ";
				if($su == 1)
					$return .= "checked='checked' ";
				$return .= "/><span class='error'>".$error."</span></td>\n</tr>\n";
			}
		}
	}
	
	private function check_su() {
		if((isset($_SESSION['su']))&&($_SESSION['su'])) {
			if((isset($_POST['su']))&&($_POST['su']))
				return array('error'=>false,'data'=>$this->set_su('',true),'db_insert' => array('su'=>'1'));
			else
				return array('error'=>false,'data'=>$this->set_su('',true),'db_insert' => array('su'=>'0'));
		}
	}
	
	private function set_Matrikel($error = '', $hidden = false) {
		$string ="";
		global $maxMatrikel;
		if(isset($_POST['Matrikel']))
			$matrikel = $_POST['Matrikel'];
		elseif(isset($this->data['Matrikel']))
			$matrikel = $this->data['Matrikel'];
		else
			$matrikel = $maxMatrikel;
		if($hidden)
			return "<tr>\n\t<td>Matrikel:</td>\n\t<td><input type='hidden' value='".$matrikel."' name='Matrikel' />".$matrikel."</td>\n</tr>\n";
		else {
			$return = "<tr>\n\t<td><label for='Matrikel'>Matrikel:</label></td>\n\t<td><select size='1' name='Matrikel' id='Matrikel'>\n";
			for($i=$maxMatrikel;$i>1952;$i--) {
				$return .= "<option value='$i'";
				if($i == $matrikel)
					$return .= " selected='selected'";
				$return .= ">$i</option>\n";
			}
			$return .= "</select><span class='error'>".$error."</span></td>\n</tr>\n";
			return $return;
		}
	}
	
	private function check_Matrikel() {
		if((isset($_POST['Matrikel']))&&($_POST['Matrikel'])) {
			global $maxMatrikel;
			$Matrikel = (int) $_POST['Matrikel'];
			if(($Matrikel>1952)&&($Matrikel<=$maxMatrikel))
				return array('error'=>false,'data'=>$this->set_Matrikel('',true),'db_insert' => array('Matrikel'=>$Matrikel));
			else
				return array('error'=>true,'data'=>$this->set_Matrikel('Matrikel muss zwischen 1953 und '.$maxMatrikel.' liegen.'));
		}
		else
			return array('error'=>true,'data'=>$this->set_Matrikel('Matrikel nicht angegeben.'));
	}
	
	private function set_Studiengang($error = '', $hidden = false) {
		$string ="";
		if(isset($_POST['Studiengang']))
			$sg = $_POST['Studiengang'];
		elseif(isset($this->data['Studiengang']))
			$sg = $this->data['Studiengang'];
		else
			$sg = 'XXXXXXXXXXXXXXXXXXXXXXXXX';
		if($hidden)
			return "<tr>\n\t<td>Studiengang:</td>\n\t<td><input type='hidden' value='".$sg."' name='Studiengang' />".$sg."</td>\n</tr>\n";
		else {
			$return = "<tr>\n\t<td><label for='Studiengang'>Studiengang:</label></td>\n\t<td><select size='1' name='Studiengang' id='Studiengang'>\n";
			$result = $this->db->query("SELECT DISTINCT `studiengang` FROM `vlv_entry2stud` ORDER BY `studiengang`");
			foreach($result as $r) {
				$return .= "<option value='".$r['studiengang']."'";
				if($r['studiengang'] == $sg)
					$return .= " selected='selected'";
				$return .= ">".$r['studiengang']."</option>\n";
			}
			$return .= "</select><span class='error'>".$error."</span></td>\n</tr>\n";
			return $return;
		}
	}
	
	private function check_Studiengang() {
		if((isset($_POST['Studiengang']))&&($_POST['Studiengang'])) {
			$result = $this->db->query("SELECT `studiengang` FROM `vlv_entry2stud` WHERE `studiengang` = '".$this->db->esc($_POST['Studiengang'])."' ORDER BY `studiengang`");
			if(count($result)>0)
				return array('error'=>false,'data'=>$this->set_Studiengang('',true),'db_insert' => array('Studiengang'=>$_POST['Studiengang']));
			else
				return array('error'=>true,'data'=>$this->set_Studiengang('Studiengang unbekannt'));
		}
		else
			return array('error'=>true,'data'=>$this->set_Studiengang('Studiengang nicht angegeben.'));
	}
	
	private function set_Seminargruppe($error = '', $hidden = false) {
		$string ="";
		if(isset($_POST['Seminargruppe']))
			$sg = $_POST['Seminargruppe'];
		elseif(isset($this->data['Seminargruppe']))
			$sg = $this->data['Seminargruppe'];
		else
			$sg = 'XXXXXXXXXXXXXXXXXXXXXXXXX';
		if($hidden)
			return "<tr>\n\t<td>Seminargruppe:</td>\n\t<td><input type='hidden' value='".$sg."' name='Seminargruppe' />".$sg."</td>\n</tr>\n";
		else {
			$return = "<tr>\n\t<td><label for='Seminargruppe'>Seminargruppe:</label></td>\n\t<td><select size='1' name='Seminargruppe' id='Seminargruppe'>\n";
			$result=array();
			if(isset($_POST['Studiengang']))
				$result = $this->db->query("SELECT DISTINCT `seminargruppe` FROM `vlv_entry2stud` WHERE `studiengang` = '".$_POST['Studiengang']."' ORDER BY `seminargruppe`");
			if(count($result)==0)
				$result = $this->db->query("SELECT DISTINCT `seminargruppe` FROM `vlv_entry2stud` ORDER BY `seminargruppe`");
			foreach($result as $r) {
				$return .= "<option value='".$r['seminargruppe']."'";
				if($r['seminargruppe'] == $sg)
					$return .= " selected='selected'";
				$return .= ">".$r['seminargruppe']."</option>\n";
			}
			$return .= "</select><span class='error'>".$error."</span></td>\n</tr>\n";
			return $return;
		}
	}
	
	private function check_Seminargruppe() {
		if((isset($_POST['Seminargruppe']))&&($_POST['Seminargruppe'])) {
			$result = $this->db->query("SELECT `seminargruppe` FROM `vlv_entry2stud` WHERE `seminargruppe` = '".$this->db->esc($_POST['Seminargruppe'])."' ORDER BY `seminargruppe`");
			if(count($result)>0) {
				$result = $this->db->query("SELECT `seminargruppe` FROM `vlv_entry2stud` WHERE `seminargruppe` = '".$this->db->esc($_POST['Seminargruppe'])."' AND `studiengang` = '".$this->db->esc($_POST['Studiengang'])."' ORDER BY `seminargruppe`");
				if(count($result)>0)
					return array('error'=>false,'data'=>$this->set_Seminargruppe('',true),'db_insert' => array('Seminargruppe'=>$_POST['Seminargruppe']));
				else
					return array('error'=>true,'data'=>$this->set_Seminargruppe('Kombination Seminargruppe & Studiengang unbekannt'));
			}
			else
				return array('error'=>true,'data'=>$this->set_Seminargruppe('Seminargruppe unbekannt'));
		}
		else
			return array('error'=>true,'data'=>$this->set_Seminargruppe('Seminargruppe nicht angegeben.'));
	}
	
	private function checkmail($email){
		return preg_match("/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-.]*tu-ilmenau\.de$/", $email);
	}
	private function checkjid($jid){
		return preg_match("/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-.]+\.([a-zA-Z]{2,4})$/", $jid); 
	}
	
	public function create_user() {
		$print = "";
		$insert = array();
		$data_print = "";
		foreach($this->fields as $field) {
			if($field == 'pass')
				continue;
			if(method_exists($this,'check_'.$field)) {
				$return = call_user_func(array($this,'check_'.$field));
				$this->error = (($this->error)||($return['error']));
				$print .= $return['data'];
				if((!$this->error)&&(isset($return['db_insert'])))
					$insert = array_merge($insert, $return['db_insert']);
				
			}
			else {
				print "Schwerer Softwarefehler!!!\n";
				exit;
			}
		}
		if(!$this->error) {
			$insert['password'] = '0';
			$insert['new_password'] = '0';
			if($this->db->insert('user',$insert)) {
				unset($_SESSION['new_password']);
				$string = $this->rand_string(100);
				if($this->db->insert('user_new',array('uid'=>$this->db->last_id(),'string'=>$string))) {
					global $url;
					global $header;
					$text = "Hallo,\nFür dich wurde ein Account bei ".$url." erstellt. Bitte aktiviere ihn, indem du folgende Seite aufrufst: ".$url."activate.php?id=".urlencode($string)."\nDanke!\n\n-- \nStudiCal";
					if(mail($insert['email'],'Registrierungsmail',$text,$header))
						$print = "Ihnen wurde nun eine Mail geschickt. Damit können Sie Ihren Account aktivieren!";
					else
						$print = "Leider konnte keine Mail versandt werden... Bitte wenden Sie sich an den Administrator!";
				}
				else
					$print = "Fehler in der Datenbank 1";
			}
			else
				$print = "Fehler in der Datenbank 2";
		}
		else
			$print = "<form method='post'><table>".$print."<tr><td colspan='2'><input type='submit' name='submit' value='speichern' /></td></tr></table></form>";
		echo $print;
	}
	
	public function create_user_form() {
		$print = "";
		foreach($this->fields as $field) {
			if($field == 'pass')
				continue;
			if(method_exists($this,'set_'.$field))
				$print .= call_user_func(array($this,'set_'.$field));
			else {
				print "Schwerer Softwarefehler!!!\n";
				exit;
			}
		}
		echo "<form method='post'><table>".$print."<tr><td colspan='2'><input type='submit' name='submit' value='speichern' /></td></tr></table></form>";
	}
	
	public function change_user() {
		$print = "";
		$insert = array();
		$data_print = "";
		foreach($this->fields as $field) {
			if($field == 'uname')
				continue;
			if(method_exists($this,'check_'.$field)) {
				$return = call_user_func(array($this,'check_'.$field));
				$this->error = (($this->error)||($return['error']));
				$print .= $return['data'];
				if((!$this->error)&&(isset($return['db_insert'])))
					$insert = array_merge($insert, $return['db_insert']);
				
			}
			else {
				print "Schwerer Softwarefehler!!!\n";
				exit;
			}
		}
		if(!$this->error) {
			if($this->db->update('user',$insert,array('uid' => $_SESSION['uid']))) {
				unset($_SESSION['new_password']);
				$string = $this->rand_string(100);
				$print = "Daten erfolgreich geupdatet!";
			}
			else
				$print = "Fehler in der Datenbank 3";
		}
		else
			$print = "<form method='post'><table>".$print."<tr><td colspan='2'><input type='submit' name='submit' value='speichern' /></td></tr></table></form>";
		echo $print;
	}
	
	public function change_user_form() {
		$print = "";
		foreach($this->fields as $field) {
			if($field == 'uname')
				continue;
			if(method_exists($this,'set_'.$field))
				$print .= call_user_func(array($this,'set_'.$field));
			else {
				print "Schwerer Softwarefehler!!!\n";
				exit;
			}
		}
		echo "<form method='post'><table>".$print."<tr><td colspan='2'><input type='submit' name='submit' value='speichern' /></td></tr></table></form>";
	}
	
	private function generateSaltedHash( $data, $salt=null ) {
		if( is_null($salt) ) {
			$salt = substr(md5(uniqid(rand())), 0, 8);
		}
		return $salt.md5($salt.$data);
	}

	private function checkSaltedHash( $data, $hash ) {
		return $hash === $this->generateSaltedHash($data, substr($hash, 0, 8));
	} 
	
	public function activate() {
		if(isset($_GET['id'])) {
			$result = $this->db->query("SELECT `uid` FROM `user_new` WHERE `string`='".$this->db->esc($_GET['id'])."'");
			if(count($result)==1) {
				$this->db->delete('user_new',array('uid' => $result[0]['uid']));
				do {
					$pass = $this->rand_string();
				} while (!((preg_match('/[a-z]+/',$pass))&&(preg_match('/[A-Z]+/',$pass))&&(preg_match('/[0-9]+/',$pass))));
				$this->db->update('user',array('new_password' => $this->generateSaltedHash($pass)),array('uid' => $result[0]['uid']));
				$email = $this->db->query("SELECT `email` FROM `user` WHERE `uid`='".$result[0]['uid']."'");
				global $header;
				$text = "Hi,\nVielen Dank für Ihre Registrierung. Hier ist Ihr Passwort:\n\t".$pass."\nSie können es nach Ihrem Login ändern.\nBis Bald!\n\n-- \nStudiCal";
				mail($email[0]['email'],'Passwortmailmail',$text,$header);
				print "Ihr Account wurde aktiviert. An Ihre Emailadresse wurde das Passwort versendet.";
			}
			else
				print "Leider ist uns dieser Aktivierungskode nicht bekannt. Eventuell wurde er schon einmal benutzt!";
		}
		else 
			print "Leider ist uns dieser Aktivierungskode nicht bekannt. Eventuell wurde er schon einmal benutzt!";
	}
	
	private function set_session($array) {
		foreach($array as $k => $v)
			$_SESSION[$k] = $v;
	}
	
	public function ical($user,$key) {
		$result1 = $this->db->query("SELECT `count` FROM `block_bad_persons` WHERE `ip` = '".$this->getRealIpAddr()."' AND `timestamp` > TIMESTAMPADD(DAY,-1,NOW())");
		if((count($result1)==0)||($result1[0]['count']<4)) {
			if(count($result1)==0)
				$count1 = 1;
			else
				$count1 = $result1[0]['count'];
			$result2 = $this->db->query("SELECT `count` FROM `block_bad_persons` WHERE `uname` = '".$this->db->esc($user)."' AND `timestamp` > TIMESTAMPADD(DAY,-1,NOW())");
			if((count($result2)==0)||($result2[0]['count']<4)) {
				if(count($result2)==0)
					$count2 = 1;
				else
					$count2 = $result2[0]['count']+1;
				$result_u = $this->db->query("SELECT `uid` FROM `user` WHERE `iCal_string` = '".$this->db->esc($key)."' AND `iCal` = 1 AND (`uname` = '".$this->db->esc($user)."' OR `uid` = '".$this->db->esc( (int) $user)."')");
				if(count($result_u)==1) {
					session_start();
					$_SESSION['uid'] = $result_u[0]['uid'];
					return true;
				}
			}
			else
				$count2 = 4;
			if($count2==1)
				$this->db->insert('block_bad_persons',array('count' => 1, 'uname' => $user));
			else
				$this->db->update('block_bad_persons',array('timestamp' => date('c'),'count'=>$count2),array('uname' => $user));
		}
		else
			$count1 = 4;
		if($count1==1)
			$this->db->insert('block_bad_persons',array('count' => 1, 'ip' => $this->getRealIpAddr()));
		else
			$this->db->update('block_bad_persons',array('timestamp' => date('c'),'count'=>$count1),array('ip' => $user));
		sleep(10);
		die ('nööp');
		return false;
	}
	
	public function login() {
		if((!isset($_SESSION['uid']))&&(isset($_POST['user']))&&(isset($_POST['pass']))) {
			$result_b1 = $this->db->query("SELECT `count` FROM `block_bad_persons` WHERE `ip` = '".$this->getRealIpAddr()."' AND `timestamp` > TIMESTAMPADD(DAY,-1,NOW())");
			if((count($result_b1)==1)&&($result_b1[0]['count']==3)) {
				$this->db->update('block_bad_persons',array('timestamp' => date('c')),array('ip' => $this->getRealIpAddr()));
				$this->login_error = "Login nicht möglich";
				return false;
			}
			$result_u = $this->db->query("SELECT * FROM `user` WHERE ( `block` IS NULL OR `block` = 0 )AND`uname`='".$this->db->esc($_POST['user'])."'");
			if(count($result_u)==1) {
				$result_b2 = $this->db->query("SELECT `count` FROM `block_bad_persons` WHERE `uname` = '".$_POST['user']."' AND `timestamp` > TIMESTAMPADD(DAY,-1,NOW())");
				if((count($result_b2)==1)&&($result_b2[0]['count']==3)) {
					$this->db->update('block_bad_persons',array('timestamp' => date('c')),array('uname' => $_POST['user']));
					$this->login_error = "Login nicht möglich";
					return false;
				}
				if($this->checkSaltedHash($_POST['pass'],$result_u[0]['password'])) {
					$this->data = $result_u[0];
					$this->set_session($result_u[0]);
					$this->db->query("DELETE FROM `block_bad_persons` WHERE `uname` = '".$result_u[0]['uname']."' OR `ip` = '".$this->getRealIpAddr()."' OR `timestamp` < TIMESTAMPADD(DAY,-1,NOW())");
					return true;
				}
				elseif($this->checkSaltedHash($_POST['pass'],$result_u[0]['new_password'])) {
					$this->db->update('user',array('password' => $result_u[0]['new_password'], 'new_password' => '0'),array('uid' => $result_u[0]['uid']));
					$this->data = $result_u[0];
					$this->set_session($result_u[0]);
					$this->db->query("DELETE FROM `block_bad_persons` WHERE `uname` = '".$result_u[0]['uname']."' OR `ip` = '".$this->getRealIpAddr()."' OR `timestamp` < TIMESTAMPADD(DAY,-1,NOW())");
					return true;
				}
				else {
					#print $this->generateSaltedHash($_POST['pass'], substr($result_u[0]['new_password'], 0, 8));
					if(count($result_b2)==0)
						$this->db->insert('block_bad_persons',array('count' => 1, 'uname' => $_POST['user']));
					else
						$this->db->update('block_bad_persons',array('timestamp' => date('c'), 'count' => $result_b2[0]['count']+1),array('uname' => $_POST['user']));
					if(count($result_b1)==0)
						$this->db->insert('block_bad_persons',array('count' => 1, 'ip' => $this->getRealIpAddr()));
					else
						$this->db->update('block_bad_persons',array('timestamp' => date('c'), 'count' => $result_b1[0]['count']+1),array('ip' => $this->getRealIpAddr()));
					$this->login_error = "Passwort falsch";
					return false;
				}
			}
			else {
				if(count($result_b1)==0)
					$this->db->insert('block_bad_persons',array('count' => 1, 'ip' => $this->getRealIpAddr()));
				else
					$this->db->update('block_bad_persons',array('timestamp' => date('c'), 'count' => $result_b1[0]['count']+1),array('ip' => $this->getRealIpAddr()));
				$this->login_error = "Nutzername nicht bekannt oder Nutzer geblockt";
				return false;
			}
		}
		elseif((!isset($_SESSION['uid']))&&(isset($_SERVER['SSL_CLIENT_S_DN_Email']))) {
			$result_u = $this->db->query("SELECT * FROM `user` WHERE ( `block` IS NULL OR `block` = 0 ) AND `email`='".$this->db->esc($_SERVER['SSL_CLIENT_S_DN_Email'])."'");
				if(count($result_u)==1) {
				$result_u[0]['ssl_client_cert'] = true;
				$this->data = $result_u[0];
				$this->set_session($result_u[0]);
				$this->db->query("DELETE FROM `block_bad_persons` WHERE `uname` = '".$result_u[0]['uname']."' OR `ip` = '".$this->getRealIpAddr()."' OR `timestamp` < TIMESTAMPADD(DAY,-1,NOW())");
				return true;
			}
			for($i==1;true;$i++) {
				if(!isset($_SERVER['SSL_CLIENT_S_DN_Email_'.$i]))
					break;
				else {
					$result_u = $this->db->query("SELECT * FROM `user` WHERE ( `block` IS NULL OR `block` = 0 ) AND `email`='".$this->db->esc($_SERVER['SSL_CLIENT_S_DN_Email_'.$i])."'");
					if(count($result_u)==1) {
						$result_u[0]['ssl_client_cert'] = true;
						$this->data = $result_u[0];
						$this->set_session($result_u[0]);
						$this->db->query("DELETE FROM `block_bad_persons` WHERE `uname` = '".$result_u[0]['uname']."' OR `ip` = '".$this->getRealIpAddr()."' OR `timestamp` < TIMESTAMPADD(DAY,-1,NOW())");
						return true;
					}
				}
			}
		}
	}
	
	private function getRealIpAddr() {
		if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
		  return $_SERVER['HTTP_CLIENT_IP'];
		elseif ((!empty($_SERVER['HTTP_X_FORWARDED_FOR']))&&($_SERVER['HTTP_X_FORWARDED_FOR']!='unknown'))   //to check ip is pass from proxy
		  return $_SERVER['HTTP_X_FORWARDED_FOR'];
		else
		  return $_SERVER['REMOTE_ADDR'];
	}
	
	public function intern() {
		if(!isset($_SESSION['uid'])) {
			print "\nError: Diese Seite darf nur angemeldet betrachtet werden!\n";
			exit;
		}
	}
	
	public function new_password() {
		if((isset($_POST['email']))&&($_POST['email'] != "")) {
			do {
				$pass = $this->rand_string();
			} while (!((preg_match('/[a-z]+/',$pass))&&(preg_match('/[A-Z]+/',$pass))&&(preg_match('/[0-9]+/',$pass))));
			if($this->db->update(array('new_password' => $pass),array('email' => $_POST['email'])))
				print "Neues Passwort wurde versandt.";
			else
				$this->new_password_form('Mailadresse nicht bekannt.');
		}
		else
			$this->new_password_form();
	}
	
	private function new_password_form($error = '') {
		if($error != '')
			print "<div class='error'>$error</div>\n";
		print "<form method='post' action='forget.php'>Emailadresse: <input type='text' name='email' size='30' /><input type='submit' value='los' /></form>";
	}
	
	public function get_header() {
	
	print <<<ENDE
	
		<script src="https://www.openstreetmap.org/openlayers/OpenStreetMap.js"></script>
		<script type="text/javascript" src="https://stadtplan-ilmenau.de/OpenLayers-2.11/OpenLayers.js"></script>
		<script>
		  function init() {
			map = new OpenLayers.Map("basicMap", { controls: [new OpenLayers.Control.Navigation()] });
			var mapnik = new OpenLayers.Layer.OSM();
			map.addLayer(mapnik);
ENDE;
		require_once('wagner-koords.php');
		print "var lonLat = new OpenLayers.LonLat(".$lon.",".$lat.") // Center of the map\n";
print <<<ENDE

			//map.setCenter(new OpenLayers.LonLat(13.41,52.52) // Center of the map
			  .transform(
				new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
				new OpenLayers.Projection("EPSG:900913") // to Spherical Mercator Projection
			  );
			var zoom=15;
	 
			var markers = new OpenLayers.Layer.Markers( "Markers" );
			map.addLayer(markers);
		 
			markers.addMarker(new OpenLayers.Marker(lonLat));
			map.setCenter (lonLat, zoom);
		  }
		</script>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/jquery-ui.min.js"></script>
		<script type="text/javascript" src="js/jquery.bpopup-0.7.0.min.js"></script>
		<SCRIPT SRC="javascript.js" TYPE="text/javascript"></SCRIPT>
		<link rel="stylesheet" type="text/css" href="css.css" media="all" />
		<link rel="stylesheet" type="text/css" href="screen.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="mobile.css" media="handheld" />
		<link rel="stylesheet" type="text/css" href="print.css" media="print" />
		<link rel="SHORTCUT ICON" href="favicon.ico" type="image/x-icon">
ENDE;
	}
}
?>
