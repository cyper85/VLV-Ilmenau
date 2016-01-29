<?php

/**
 * @author Andreas Neumann
 */

class lang {
	private $lang = 'default';
	private $lang_array = array();
	
	function __construct() {
		//Spracheinstellung übernehmen
		if ((isset($_REQUEST['lang']))&&(strlen($_REQUEST['lang'])))
			$this->lang = $_REQUEST['lang'];
		elseif ((isset($_SESSION['lang']))&&(strlen($_SESSION['lang'])))
			$this->lang = $_SESSION['lang'];
		require_once('lang/main.php');
		$this->lang_array = array_merge($this->lang_array,$lang);
	}
	
	public function get($string) {
		if(isset($this->lang_array[$string][$this->lang]))
			return $this->lang_array[$string][$this->lang];
		if(isset($this->lang_array[$string]['default']))
			return $this->lang_array[$string]['default'];
		else {
			trigger_error('Schluessel '.$string.' in Languagepack nicht vorhanden!',E_USER_NOTICE);
			return "";
		}
	}
	
	public function get_nav($string) {
		if(isset($this->lang_array['nav'][$string][$this->lang]))
			return $this->lang_array['nav'][$string][$this->lang];
		if(isset($this->lang_array['nav'][$string]['default']))
			return $this->lang_array['nav'][$string]['default'];
		else {
			trigger_error('Schluessel '.$string.' in Languagepack für Nav nicht vorhanden!',E_NOTICE);
			return array();
		}
	}
	
	public function get_link($string) {
		if(isset($this->lang_array['link'][$string][$this->lang]))
			return $this->lang_array['link'][$string][$this->lang];
		if(isset($this->lang_array['link'][$string]['default']))
			return $this->lang_array['link'][$string]['default'];
		else {
			trigger_error('Schluessel '.$string.' in Languagepack für Link nicht vorhanden!',E_NOTICE);
			return array();
		}
	}
	
	
	public function load_lang($data = "") {
		if(strlen($data)>0) {
			require_once('lang/'.$data.'.php');
			$this->lang_array = array_merge($this->lang_array,$lang);
			return true;
		}
		else
			return false;
	}
}

abstract class sc {
	protected $db;
	protected $anzeige;
	protected $title = "Deine Studienkalender in Ilmenau";
	protected $meta_desc = "Terminübersicht für Studenten der TU Ilmenau";
	protected $meta_keys = array();
	protected $lang;
	protected $nav_main = array( 'home','impressum');
	protected $nav_anonym = array( 'login','registrieren');
	protected $nav_login = array('mydata','myfriends','logout');
	protected $nav_groupadmin = array('gruppe');
	protected $nav_admin = array();
	protected $check_user_array = array();
	
	abstract function set_main();
	
	public function __construct($db) {
		//Datenbankverbindung
		$this->db = $db;
		//Sprachanbindung
		$this->lang = new lang();
		$this->title = $this->lang->get('hp_title_ender'); 
	}
	
	public function get_out() {
		$this->html_header();
		$this->header();
		print "<div id='main'>\n".$this->anzeige."\n</div>\n";
		$this->nav();
		$this->footer();
	}
	
	public function load_lang($string) {
		$this->lang->load_lang($string);
		$this->title = $this->lang->get('title') ." - ".$this->lang->get('hp_title_ender');
	}
	
	public function set_header($array = array()) {
		if(!is_array($array))
			return false;
		elseif(isset($array['title'])) {
			$this->title = $array['title'] ." - ".$this->lang->get('hp_title_ender'); 
		}
		elseif(isset($array['meta_keys'])) {
			
		}
		elseif(isset($array['meta_keys'])) {
			
		}
	}
	
	protected function html_header() {
		print "<html>\n\t<head>\n";
		if(strlen($this->title)>0)
			print "\t\t\t<title>".$this->title."</Hometitle>\n";
		print "\t\t</head>\n\t<body>\n";
	}
	
	protected function header() {
		print "\t\t\t<div id='header'>".$this->nav_oben()."</div>\n";
	}
	
	protected function footer() {
		print "\t\t<div id='footer'>".$this->nav_unten()."</div>\n\t</body>\n</html>";
	}
	
	protected function nav() {
		print "\t\t<div id='nav'>\n";
		print "\t\t</div>\n";
		
	}
	
	protected function check_user() {
		$array['login'] = false;
		$array['groupadmin'] = false;
		$array['admin'] = false;
		
		if(isset($_SESSION['uid'])) {
			$array['login'] = true;
			if(isset($this->check_user_array['groupadmin']))
				$array['groupadmin'] = $this->check_user_array['groupadmin'];
			else {
				$result = $this->db->query("SELECT COUNT(*) as count FROM groupadmins WHERE uid = '".$_SESSION['uid']."'");
				if($result[0]['count'] > 0)
					$array['groupadmin'] = true;
				else
					$array['groupadmin'] = false;
			}
			if(isset($this->check_user_array['admin']))
				$array['admin'] = $this->check_user_array['admin'];
			else {
				$result = $this->db->query("SELECT su FROM user WHERE user = '".$_SESSION['uid']."'");
				if($result[0]['su'])
					$array['admin'] = true;
				else
					$array['admin'] = false;
			}
		}
		$this->check_user_array = $array;
		return $array;
	}
	
	protected function make_link($link, $speak, $new = false, $attr=array()) {
		$string = "<a href='".$link."'";
		if($new)
			$string .= " target='_blank'";
		if(isset($speak['long']))
			$string .= " title='".$speak['long']."'";
		foreach($attr as $k => $v)
			$string .= " ".$k."='".$v."'";
		$string .= ">".$speak['short']."</a>";
		return $string;
	}
	
	protected function nav_oben() {
		$array = $this->nav_main;
		$user = $this->check_user();
		if($user['login']) {
			array_merge($array,$this->nav_login);
			if($user['groupadmin'])
				array_merge($array,$this->nav_groupadmin);
			if($user['admin'])
				array_merge($array,$this->nav_admin);
		}
		else
			$array = array_merge($array,$this->nav_anonym);
		$local_array = array();
		foreach($array as $a) {
			$local_array[] = $this->make_link($a.".php",$this->lang->get_nav[$a]);
		}
		return implode(' | ',$local_array);
	}
	
	protected function nav_unten() {
		$string = $this->nav_oben();
		if(strlen($string)>0)
			return $string." | &copy; Andreas Neumann";
		else
			return "&copy; Andreas Neumann";
	}
	
	public function maindiv($insert) {
		#if(is_object($insert))
					
	}
}
?>