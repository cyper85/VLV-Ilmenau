<?php

/**
 * DB-Klasse
 * @author Andreas Neumann
 * @name DB-Klasse
 */


class db {
	private $db = false;

	function __construct($db_host, $db_user, $db_pass, $db_db) {
		$this->db = mysql_connect($db_host, $db_user, $db_pass);
		mysql_select_db($db_db,$this->db);
		#mysql_set_charset('utf-8',$this->db);
		mysql_query('SET CHARACTER SET utf-8',$this->db);
	}
	
	function __destruct() {
		if(is_resource($this->db))
			mysql_close($this->db);
	}
	
	public function query($query,$debug=0) {
		$return = array();
		$result = mysql_query($query,$this->db);
		if($debug)
			print $query;
		if(preg_match('/^\s*SELECT/msi',$query)) {
			if(mysql_num_rows($result)>0) {
				while($row=mysql_fetch_assoc($result))
					$return[]=$row;
				return $return;
			}
			else
				return array();
		}
		else {
			return mysql_affected_rows($this->db);			
		}
	}
	
	public function esc($string) {
		return mysql_real_escape_string($string,$this->db);
	}
	
	public function ping() {
		print "ping: ".mysql_ping($this->db)."\n";
		return mysql_ping($this->db);
	}
	
	public function insert($table,$include_array,$debug=0) {
		$keys = array();
		$values = array();
		foreach($include_array as $k => $v) {
			$keys[]='`'.$k.'`';
			$values[]="'".preg_replace('/javascript/msi','java<!---->script',mysql_real_escape_string($v,$this->db))."'";
		}
		$query="INSERT INTO `".$table."` (".implode(",",$keys).") VALUES (".implode(",",$values).")";
		if($debug)
			print $query."\n";
		$result=mysql_query($query,$this->db);
		if(mysql_affected_rows($this->db)==1) {
			return true;
		}
		elseif(mysql_affected_rows($this->db)==0) {
			$this->error=true;
			$this->errorstr="No entry affected by $table-INSERT";
			$this->is_error();
			return false;
		}
		else {
			$this->error=true;
			$this->errorstr="Unkown error by $table-INSERT";
			$this->is_error();
			return false;
		}
		
	}
	
	public function create_tmp_table($name, $from, $debug=0) {
		$this->query("CREATE TEMPORARY TABLE `$name` LIKE `$from`",$debug);
	}
	
	public function copy_table($name, $from, $debug=0) {
		$this->query("TRUNCATE `$name`",$debug);
		$this->query("INSERT INTO `$name` SELECT * FROM `$from`",$debug);
	}
	
	public function last_id() {
		return mysql_insert_id($this->db);
	}
	
	public function update($table,$update_array,$where_array,$debug=0) {
		$where = array();
		foreach($where_array as $k => $v) {
			$where[]="`".$k."` = '".mysql_real_escape_string($v,$this->db)."'";
		}
		$update = array();
		foreach($update_array as $k => $v) {
			$update[]= "`".$k."` = '".preg_replace('/javascript/msi','java<!---->script',mysql_real_escape_string($v,$this->db)."'");
		}
		$query="UPDATE LOW_PRIORITY `".$table."` SET ".implode(",",$update)." WHERE ".implode(" AND ",$where);
		$result=mysql_query($query,$this->db);
		if(mysql_affected_rows($this->db)==1) {
			return true;
		}
		elseif(mysql_affected_rows($this->db)==0) {
			$this->error=true;
			$this->errorstr="No entry affected by $table-UPDATE";
			$this->is_error();
			return false;
		}
		else {
			$this->error=true;
			$this->errorstr="Unkown error by $table-UPDATE";
			$this->is_error();
			return false;
		}
		
	}
	
	public function delete($table,$delete_array,$debug=0) {
		$where = array();
		foreach($delete_array as $k => $v) {
			$where[]="`".$k."` = '".mysql_real_escape_string($v,$this->db)."'";
		}
		$query="DELETE FROM `".$table."` WHERE ".implode(" AND ",$where);
		$result=mysql_query($query,$this->db);
		if(mysql_affected_rows($this->db)==1) {
			return true;
		}
		elseif(mysql_affected_rows($this->db)==0) {
			$this->error=true;
			$this->errorstr="No entry affected by $table-DELETE";
			$this->is_error();
			return false;
		}
		else {
			$this->error=true;
			$this->errorstr="Unkown error by $table-DELETE";
			$this->is_error();
			return false;
		}
		
	}
	
	private function is_error() {
		//erstmal nichts
	}
	
	public function query2array($query,$debug = false) {
		$return = array();
		$result = mysql_query($query,$this->db);
		if($debug)
			print $query;
		if(preg_match('/^\s*SELECT/msi',$query)) {
			if(mysql_num_rows($result)>0) {
				while($row=mysql_fetch_assoc($result))
					if(count($row)==1)
						foreach($row as $k=>$v)
							$return[]="'".$v."'";
				return implode(', ',$return);
			}
			else
				return "'999999999999999999999999999999999999999999999999999999999999999999999'";
		}
		else {
			return mysql_affected_rows($this->db);
		}
	}
}
?>
