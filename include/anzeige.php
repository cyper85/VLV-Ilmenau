<?php

/**
 * Anzeige-Klasse
 * @author Andreas
 * @name Anzeige-Klasse
 */

class anzeige {
	private $body = "";
	private $title = "";
	private $align = "left";
	private $nav = "";	
	private $nav_array = array( "Login" => array(
						'Welcome' => 
							array(
								'site' => 'index.php',
								'info' => 'Wilkommensseite '
							),
						'Kalender' =>
							array(
								'site' => 'index.php?action=cal',
								'info' => 'Kalender verwalten'
							),
						'Eintr&auml;ge' =>
							array(
								'site' => 'index.php?action=entry',
								'info' => 'Kalendereintr&auml;ge bearbeiten'
							),
						'Inhalte' =>
							array(
								'site' => 'index.php?action=content',
								'info' => 'Inhalte verwalten'
							),
						'Sonstige Daten' =>
							array(
								'site' => 'index.php?action=data',
								'info' => 'Impressum und Userdaten verwalten'
							),
						'Logout' =>
							array(
								'site' => 'index.php?logout=true',
								'info' => 'Logout'
							)
						)
						);
	
	protected $db;
	
	function __construct() {
		session_start();
	
		if($_REQUEST['logout']=="true") {
			session_unset();
		}
		$this->db = new db();
	}
	
	protected function setBody($element) {
		$this->body.=$element->content()."\n";
	}
	
	protected function setTitle($element) {
		$this->title=$element;
	}
	
	protected function setNav($nav, $seite) {
		$local = $this->nav_array[$nav];
		$l_array = array();
		foreach ($local as $key => $value) {
			if($key == $seite) {
				$l_array[] = "<b>".$key."</b>";
			}
			else {
				$l_array[] = "<a href='".$value['site']."' title='".$value['info']."'>".$key."</a>";
			}
		}
		$this->nav = implode("&nbsp;&nbsp;-&nbsp;&nbsp;",$l_array);
	}
	
	protected function setAlign($element) {
		$this->align=$element;
	}
	
	protected function get_content() {
		$html = <<<EENNDDEE
<html>
	<head>
		<link type="text/css" href="css/vader/jquery-ui-1.8.2.custom.css" rel="Stylesheet">			
		<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.2.custom.min.js"></script>
		<script type="text/javascript">
			$(function () {
				$("#since").datepicker(
					{
						dateFormat: 'dd.mm.yy', // Anzeige auf Deutsch
						dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
						dayNamesMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
						dayNamesShort: ['Son', 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam'],
						monthNames: ['Januar','Februar','Marts','April','Maj','Juni','Juli','August','September','Oktober','November','December'],
						monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun','Jul','Aug','Sep','Okt','Nov','Dec'],
						showWeek: true,
						weekHeader: 'W',
						prevText: 'Fr�her',
						nextText: 'Sp�ter',
						currentText: 'Heute',
						closeText: 'Schlie�en',
						showOn: 'both'
					}
				);
				$("#till").datepicker(
					{
						dateFormat: 'dd.mm.yy', // Anzeige auf Deutsch
						dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
						dayNamesMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
						dayNamesShort: ['Son', 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam'],
						monthNames: ['Januar','Februar','Marts','April','Maj','Juni','Juli','August','September','Oktober','November','December'],
						monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun','Jul','Aug','Sep','Okt','Nov','Dec'],
						showWeek: true,
						weekHeader: 'W',
						prevText: 'Fr�her',
						nextText: 'Sp�ter',
						currentText: 'Heute',
						closeText: 'Schlie�en',
						showOn: 'both'
					}
				);
			})
		</script>
		<title>$this->title</title>
	</head>
	<body style='min-height:100%'>
		<!--Navigation oben-->
		<div style='text-align:center;height:1.2em; border-bottom:1px solid gray; border-top:1px solid gray; padding-bottom:4px; margin:0px; '>
			$this->nav
		</div>
		<div style='min-width:80%' align='$this->align'>
			<h1>$this->title</h1>
			$this->body
		</div>
		<!--Navigation unten-->
		<div style='text-align:center;height:1.2em; border-bottom:1px solid gray; border-top:1px solid gray; padding-bottom:4px; margin:0px; '>
			$this->nav
		</div>
	</body>
</html>
EENNDDEE;
			
		echo $html;
		exit;
	}
}

class HTMLItem {
	protected $header = array();
	protected $contentstr = "";
	private $name = "";
	
	public function setHeader($method) {
		$this->header=$method;
	}
	
	public function setContent($content) {
		if(strlen($this->contentstr)>0)
			$this->contentstr.="\n";
//		echo $content->content();
		if(is_object($content)){
			$this->contentstr.=$content->content();
		}
		else {
			$this->contentstr.=$content;
		}
	}
	
	public function setName($name) {
		$this->name=$name;
	}
	
	public function content() {
		$code = "<".$this->name;
		foreach($this->header as $k => $v) {
			$code .= " ".$k."='".$v."'";
		}
		if($this->contentstr=="") {
			$code .= " />";
		}
		else {
			$code .= ">";
			$code .= $this->contentstr;
			$code .= "</".$this->name.">\n";
		}
		return $code;
	}
}

class Form extends HTMLItem {
	function __construct() {
		$this->setName("form");
	}
}

class Line extends HTMLItem {
	function __construct() {
		$this->setName("hr");
	}
}

class Link extends HTMLItem {
	function __construct() {
		$this->setName("a");
	}
}

class Table extends HTMLItem {
	function __construct() {
		$this->setName("table");
	}
	
	public function addRow($Cells,$header = array()) {
		$row= new Row();
		$row->setHeader($header);
		if(is_object($Cells)) {
			$row->setContent($Cells);
		}
		else {
			foreach($Cells as $Cell)
				$row->setContent($Cell);
		}
		$this->setContent($row);
	}
}

class CellHead extends HTMLItem {
	function __construct() {
		$this->setName("th");
	}
}

class Cell extends HTMLItem {
	function __construct() {
		$this->setName("td");
	}
}

class Row extends HTMLItem {
	function __construct() {
		$this->setName("tr");
	}
}

class Input extends HTMLItem {
	function __construct() {
		$this->setName("input");
	}
}

class TextSpan extends HTMLItem {
		
	public function setContent($content) {
		$this->contentstr .= $content;
	}
	
	function __construct() {
		$this->setName("span");
	}
}

class TextPara extends HTMLItem {
		
	public function setContent($content) {
		$this->contentstr .= $content;
	}
	
	function __construct() {
		$this->setName("p");
	}
}

class SelectBox extends HTMLItem {
	private $contentarr = array();
	private $selected = "";
	protected $header = array();
	private $name = "";
	
	public function setHeader($method) {
		$this->header=$method;
	}
	
	public function setContent($content) {
		if(is_array($content)) {
			$this->contentarr = array_merge($this->contentarr,$content);
		}
	}
	
	public function setSelected($content) {
		$this->selected = $content;
	}
		
	function __construct() {
		$this->name = "select";
	}
	
	function content() {
		$code = "<".$this->name;
		foreach($this->header as $k => $v) {
			$code .= " ".$k."='".$v."'";
		}
		if(count($this->contentarr)==0) {
			$code .= " />";
		}
		else {
			$code .= ">";
			foreach($this->contentarr as $key => $value) {
				$code .= "<option value='$key'";
				if($this->selected == $key) {
					$code .= " selected='selected' ";
				}
				$code .= ">$value</option>";
			}
			$code .= "</".$this->name.">";
		}
		return $code;
	}
}

class Header extends HTMLItem {
		
	protected $tiefe = 2;
	protected $contentstr = "";
	protected $header = array();
	
	public function setHeader($method) {
		$this->header=$method;
	}	

	public function setContent($content,$tiefe=2) {
		$this->tiefe = $tiefe;
		$this->contentstr .= $content;
	}
	
	public function setDeapth ($tiefe) {
		$this->tiefe = $tiefe;
	}
	
	function content() {
		$code = "<h".$this->tiefe;
		foreach($this->header as $k => $v) {
			$code .= " ".$k."='".$v."'";
		}
		if(strlen($this->contentstr)==0) {
			$code .= " />\n";
		}
		else {
			$code .= ">";
			$code .= $this->contentstr;
			$code .= "</h".$this->tiefe.">\n";
		}
		return $code;
	}
}

class TextArea extends HTMLItem {
	
	protected $contentstr = " ";
	
	public function setContent($content) {
		if(($this->contentstr==" ")&&(strlen($content)>0)) {
			$this->contentstr = $content;
		}
		else {
			$this->contentstr .= $content;
		}
	}
	
	function __construct() {
		$this->setName("textarea");
	}
}

class Image extends HTMLItem {
	
	public function setContent($content) {}
	
	function __construct() {
		$this->setName("img");
	}
}

function cheater() {
	global $cheater;
	if($cheater!=true) {
		echo "<b>CHEATER!</b>";
		exit;
	}
}

function bildhochladen($id) {
	#$datei = $_FILES['logo']['name']; // Dies hab ich noch nicht getestet, da ich den Namen immer nach datum und user id abgespeichert hab.
	$id = (int) $id;
	if(($id == 0)||(!isset($_FILES['logo']))) {		
		return true;
	}
	$datei = $id.".jpg";
	
	#$datei = str_replace(" ", "_", "$datei");
	#$datei = htmlentities($datei); // Mit leerzeichen -> _ hab ich auch noch nicht getestet, sollte aba klappen
	$dateityp = GetImageSize($_FILES['logo']['tmp_name']);
	if(($dateityp[2] == 1)||($dateityp[2] == 2)||($dateityp[2] == 3)) {
		if($_FILES['datei']['size'] <  2048000) { 
		//max. Gr��e in bytes
      		move_uploaded_file($_FILES['logo']['tmp_name'], "upload/temp-$datei");
            $file        = "upload/temp-$datei";
            $target    = "upload/$datei";
            $max_width   = "16"; //Breite �ndern
            $max_height   = "16"; //H�he �ndern
            $quality     = "100"; //Qualit�t �ndern (max. 100)
            if($dateityp[2]==1) {
            	$src_img     = imagecreatefromgif($file);
            }
            elseif($dateityp[2]==2) {
            	$src_img     = imagecreatefromjpeg($file);
            }
            elseif($dateityp[2]==3) {
            	$src_img     = imagecreatefrompng($file);
            }
            $picsize     = getimagesize($file);
            $src_width   = $picsize[0];
            $src_height  = $picsize[1];
            
            if($src_width > $src_height) {
				if($src_width > $max_width) {
					$convert = $max_width/$src_width;
                    $dest_width = $max_width;
                    $dest_height = ceil($src_height*$convert);
				}
				else {
					$dest_width = $src_width;
					$dest_height = $src_height;
				}
			}
			else {
				if($src_height > $max_height) {
					$convert = $max_height/$src_height;
					$dest_height = $max_height;
					$dest_width = ceil($src_width*$convert);
				}
				else {
					$dest_height = $src_height;
					$dest_width = $src_width;
				}
			}
			$dst_img = imagecreatetruecolor($dest_width,$dest_height);
			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);
			imagejpeg($dst_img, "$target", $quality);
/*
			// Ab hier wird noch eine Thumbnail erstellt. 
			$file2       = "upload/$datei";
			$target2    = "upload/thumbnail-$datei";
			$max_width   = "16"; //Thumbnailbreite
			$max_height   = "16"; //Thumbnailh�he
			$quality     = "100"; //Thumbnailqualit�t
			$src_img     = imagecreatefromjpeg($file2);
			$picsize     = getimagesize($file2);
			$src_width   = $picsize[0];
			$src_height  = $picsize[1];
                  
			if($src_width > $src_height) {
				if($src_width > $max_width) {
					$convert = $max_width/$src_width;
					$dest_width = $max_width;
					$dest_height = ceil($src_height*$convert);
				}
				else {
					$dest_width = $src_width;
					$dest_height = $src_height;
				}
			}
			else {
				if($src_height > $max_height) {
					$convert = $max_height/$src_height;
					$dest_height = $max_height;
					$dest_width = ceil($src_width*$convert);
				}
				else {
					$dest_height = $src_height;
					$dest_width = $src_width;
				}
			}
			$dst_img = imagecreatetruecolor($dest_width,$dest_height);
			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);
			imagejpeg($dst_img, "$target2", $quality);
*/
			unlink($file);
			#echo "<img src=\"upload/$datum-$userid.jpg\">";
			return true;
		}
   		else {
			echo "<center><b>Das Bild darf nicht gr��er als 2MB sein</b></center>";
			return false;
		}
	}
	else {
    	echo "<center><b>Bitte nur Bilder im JPG, GIF oder PNG Format hochladen</b></center>";
		return false;
	}
}

function generateSaltedHash( $data, $salt=null )
{
    if( is_null($salt) ) {
        $salt = substr(md5(uniqid(rand())), 0, 8);
    }
    return $salt.md5($salt.$data);
}

function checkSaltedHash( $data, $hash )
{
    return $hash === generateSaltedHash($data, substr($hash, 0, 8));
}  
?>
