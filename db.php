<?php

/**
 * DB-Klasse
 * @author Andreas Neumann
 * @name DB-Klasse
 */


class db {
    private $db = false;

    function __construct($db_host, $db_user, $db_pass, $db_db) {
        $this->db = mysqli_connect($db_host, $db_user, $db_pass,$db_db);
        $this->db->set_charset('utf-8');
    }

    function __destruct() {
        if(is_resource($this->db)) {
            $this->db->close();
        }
    }

    public function query($query,$debug=0) {
        $return = array();
        $result = $this->db->query($query);
        if ($debug) {
            print $query;
        }
        if(preg_match('/^\s*SELECT/msi',$query)) {
            if ($result->num_rows > 0) {
                $return = [];
                while ($row = $result->fetch_assoc()) {
                    $return[] = $row;
                }
                return $return;
            } else {
                return array();
            }
        }
        else {
            return $this->db->affected_rows;			
        }
    }

    public function esc($string) {
        return $this->db->real_escape_string($string);
    }

    public function ping() {
        $ping = $this->db->ping();
        print "ping: ".$ping."\n";
        return $ping;
    }

    public function insert($table,$include_array,$debug=0) {
        $keys = array();
        $values = array();
        foreach($include_array as $k => $v) {
            $keys[]='`'.$k.'`';
            $values[]="'". preg_replace('/javascript/msi','java<!---->script',$this->esc($v))."'";
        }
        $query="INSERT INTO `".$table."` (".implode(",",$keys).") VALUES (".implode(",",$values).")";
        if ($debug) {
            print $query . "\n";
        }
        $this->db->query($query);
        if($this->db->affected_rows===1) {
            return true;
        }
        elseif($this->db->affected_rows===0) {
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
        return $this->db->insert_id;
    }

    public function update($table,$update_array,$where_array,$debug=0) {
        $where = array();
        foreach($where_array as $k => $v) {
            $where[]="`".$k."` = '".$this->esc($v)."'";
        }
        $update = array();
        foreach($update_array as $k => $v) {
            $update[]= "`".$k."` = '".preg_replace('/javascript/msi','java<!---->script',$this->db->esc($v)."'");
        }
        $query="UPDATE LOW_PRIORITY `".$table."` SET ".implode(",",$update)." WHERE ".implode(" AND ",$where);
        $this->db->query($query);
        if($this->db->affected_rows===1) {
            return true;
        }
        elseif($this->db->affected_rows===0) {
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
            $where[]="`".$k."` = '".$this->db->esc($v)."'";
        }
        $query="DELETE FROM `".$table."` WHERE ".implode(" AND ",$where);
        $this->db->query($query);
        if($this->db->affected_rows===1) {
            return true;
        }
        elseif($this->db->affected_rows===0) {
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
        $result = $this->db->query($query);
        if ($debug) {
            print $query;
        }
        if(preg_match('/^\s*SELECT/msi',$query)) {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    if (count($row) === 1) {
                        foreach ($row as $k => $v) {
                            $return[] = "'" . $v . "'";
                        }
                    }
                }
                return implode(', ', $return);
            }
            else {
                return "'999999999999999999999999999999999999999999999999999999999999999999999'";
            }
        }
        else {
                return $this->db->affected_rows;
        }
    }
}