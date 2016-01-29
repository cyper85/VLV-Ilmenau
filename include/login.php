<?php

require_once("anzeige.php");
class login extends anzeige {
	private $uname = "";
	private $error = false;
	
	public function content() {
		if(!isset($_SESSION['uid'])) {
			if((isset($_REQUEST['user']))&&(isset($_REQUEST['pass']))) {
				$return=$this->db->query("SELECT admin,uid,name,email,kalender,pass FROM user WHERE uname='".mysql_real_escape_string($_REQUEST['user'])."'");
				if(!(checkSaltedHash( $_REQUEST['pass'], $return[0]['pass']))) {
					$this->error=true;
					$this->uname=htmlentities($_REQUEST['user']);
					$this->set_content();
				}
				else {
					$_SESSION['uid']=$return[0]['uid'];
					$_SESSION['uname']=$_REQUEST['user'];
					$_SESSION['name']=$return[0]['name'];
					$_SESSION['email']=$return[0]['email'];
					$_SESSION['cid']=$return[0]['kalender'];
					$_SESSION['admin']= (bool) $return[0]['admin'];
				}
			}
			else				
				$this->set_content();
		}
	} 
	
	private function set_content() {
		$this->setTitle("Login");
		$table = new Table();
		//Fehler
		if($this->error) {
			$zelle = new Cell();
			$header = array(
				"colspan"=>2,
				"align"=>"center"
			);
			$zelle->setHeader($header);
			$text = new TextSpan();
			$header = array(
				"style"=>"color:#FF0000",
				"name"=>"error"
			);
			$text->setHeader($header);
			$text->setContent("Falsche Loginangaben");
			$zelle->setContent($text);
			$table->addRow(array($zelle));
		}
		
		//User-Name$zelle = new Cell();
		$zelle1 = new Cell();
		$text = new TextSpan();
		$text->setContent("User:");
		$zelle1->setContent($text);
		$zelle2 = new Cell();
		$input = new Input();
		$header = array(
			"name"=>"user",
			"value" => $this->uname,
			"type" => "text"
		);
		$input->setHeader($header);
		$zelle2->setContent($input);
		$table->addRow(array($zelle1,$zelle2));
		//Passwort
		$zelle1 = new Cell();
		$text = new TextSpan();
		$text->setContent("Passwort:");
		$zelle1->setContent($text);
		$zelle2 = new Cell();
		$input = new Input();
		$header = array(
			"name"=>"pass",
			"type" => "password"
		);
		$input->setHeader($header);
		$zelle2->setContent($input);
		$table->addRow(array($zelle1,$zelle2));
		//Senden
		$zelle = new Cell();
		$header = array(
			"rowspan"=>2,
			"align"=>"right"
		);
		$zelle->setHeader($header);
		$input = new Input();
		$header = array(
			"value"=>"Login",
			"type" => "submit"
		);
		$input->setHeader($header);
		$zelle->setContent($input);
		$table->addRow(array($zelle));
		$form = new Form();
		$form->setHeader(
		array("method"=>"post",
		"action"=>"index.php",
		"name"=>"LoginForm"));
		$form->setContent($table);
		
		$this->setBody($form);
		$this->setAlign("center");
		$this->get_content();
		exit;
	}
}

$login = new login();
$login->content();
?>