<?

class Database {
	
	function connect($fatal = true) {
		global $CONF;
		
		$this->db = mysql_connect($CONF['db_host'], $CONF['db_user'], $CONF['db_passwd']);
		
		if (!$this->db && $fatal) {
			header("HTTP/1.1 503 Service Unavailable");
			die("Website offline - please try later\n");
		}
		
		if (!mysql_select_db($CONF['db_name'], $this->db) && $fatal) {
			header("HTTP/1.1 503 Service Unavailable");
			die("Website offline - please try later.\n");
		}
		
		$this->prefix = $CONF['db_prefix'];
	}
	
	//special function to enable using "SELECT * FROM {$db->table_configuation}" to get the proper table name!
	function __get($name) {
		if (preg_match('/table_(\w+)/',$name,$m)) {
			return $this->{$name} = $this->prefix.$m[1];
		}
	}
	
	function quote($in) {
		return "'".mysql_real_escape_string($in)."'";
	}
	
	function insert_id($in) {
			return mysql_insert_id();
	}
	
	#############
	
	function sqlMakeCountQuery(&$sql) {
		if (isset($sql['group'])) {
			if (isset($sql['having'])) {
				$sql['count_query'] = "SELECT COUNT(DISTINCT IF({$sql['having']},{$sql['group']},NULL))";
			} else {
				$sql['count_query'] = "SELECT COUNT(DISTINCT {$sql['group']})";
			}
			$sql['count_query'] = preg_replace('/\b(ASC|DESC)\b/i','',$sql['count_query']);
		} else {
			$sql['count_query'] = "SELECT count(*)";
		}
		if (isset($sql['tables']) && count($sql['tables'])) {
			$sql['count_query'] .= " FROM ".join(' ',$sql['tables']);
		}
		if (isset($sql['wheres']) && count($sql['wheres'])) {
			$sql['count_query'] .= " WHERE ".join(' AND ',$sql['wheres']);
		}
		return $sql['count_query'];
	}
	
	function sqlMakeQuery(&$sql) {
		if (is_array($sql['columns'])) {
			$sql['columns'] = join(',',$sql['columns']);
		} 
		$sql['sql_query'] = "SELECT {$sql['columns']}";
		
		if (isset($sql['tables']) && count($sql['tables'])) {
			$sql['sql_query'] .= " FROM ".join(' ',$sql['tables']);
		}
		if (isset($sql['wheres']) && count($sql['wheres'])) {
			$sql['sql_query'] .= " WHERE ".join(' AND ',$sql['wheres']);
		}
		if (isset($sql['group'])) {
			$sql['sql_query'] .= " GROUP BY {$sql['group']}";
		}
		if (isset($sql['having'])) {
			$sql['sql_query'] .= " HAVING {$sql['having']}";
		}
		if (isset($sql['order'])) {
			$sql['sql_query'] .= " ORDER BY {$sql['order']}";
		}
		if (isset($sql['limit'])) {
			$sql['sql_query'] .= " LIMIT {$sql['limit']}";
		}
		return $sql['sql_query'];
	}
	
	#############
	
	function query($query) {
		$result = mysql_query($query, $this->db) or print('<br>Error query: '.mysql_error());
		return $result;
	}
	
	function getOne($query) {
		$result = mysql_query($query, $this->db) or print("<br>Error getOne [[ $query ]] : ".mysql_error());
		if (mysql_num_rows($result)) {
			return mysql_result($result,0,0);
		} else {
			return FALSE;
		}
	}
	
	function getRow($query) {
		$result = mysql_query($query, $this->db) or print('<br>Error getRow: '.mysql_error());
		if (mysql_num_rows($result)) {
			return mysql_fetch_assoc($result);
		} else {
			return FALSE;
		}
	}
	
	function getCol($query) {
		$result = mysql_query($query, $this->db) or print('<br>Error getCol: '.mysql_error());
		if (!mysql_num_rows($result)) {
			return FALSE;
		}
		$a = array();
		while($row = mysql_fetch_row($result)) {
			$a[] = $row[0];
		}
		return $a;
	}
		
	function getAll($query) {
		$result = mysql_query($query, $this->db) or print('<br>Error getAll: '.mysql_error($this->db));
		if (!mysql_num_rows($result)) {
			return array();
		}
		$a = array();
		while($row = mysql_fetch_assoc($result)) {
			$a[] = $row;
		}
		return $a;
	}
	
	function getAssoc($query) {
		$result = mysql_query($query, $this->db) or print('<br>Error getAssoc: '.mysql_error());
		if (!mysql_num_rows($result)) {
			return array();
		}
		$a = array();
		$row = mysql_fetch_assoc($result);
		
		if (count($row) > 2) {
			do {
				$i = array_shift($row);
				$a[$i] = $row;
			} while($row = mysql_fetch_assoc($result));
		} else {
			$row = array_values($row);
			do {
				$a[$row[0]] = $row[1];
			} while($row = mysql_fetch_row($result));
		}
		return $a;
	}
		
	####################
	
	function updates_to_a(&$updates) {
		$a = array();
		foreach ($updates as $key => $value) {
			$key = str_replace('`','',$key); //ugly sql-injection protection!
			//NULL
			if (is_null($value)) {
				$a[] = "`$key`=NULL";
			} else {
				//converts uk dates to mysql format (mostly) - better than strtotime as it might not deal with uk dates
				if (preg_match('/^(\d{2})[ \/\.-]{1}(\d{2})[ \/\.-]{1}(\d{4})$/',$value,$m)) {
					$value = "{$m[3]}-{$m[2]}-{$m[1]}";
				}
				//numbers and functions, eg NOW()
				if (preg_match('/^(-?\d+|\w+\(\d*\))$/',$value)) {
					$a[] = "`$key`=$value";
				} else {
					$a[] = "`$key`='".mysql_real_escape_string($value)."'";
				}
			}
		}
		return $a;
	}
	
	function updates_to_insert($table,$updates) {
		$a = $this->updates_to_a($updates);
		$table = str_replace('`','',$table); //ugly sql-injection protection!
		return "INSERT INTO `$table` SET ".join(',',$a).",created=NOW()";
	}
	function updates_to_insertupdate($table,$updates) {
		$a = $this->updates_to_a($updates);
		$table = str_replace('`','',$table); //ugly sql-injection protection!
		return "INSERT INTO `$table` SET ".join(',',$a).",created=NOW() ON DUPLICATE KEY UPDATE ".join(',',$a);
	}
	
	function updates_to_update($table,$updates,$primarykey,$primaryvalue) {
		$a = $this->updates_to_a($updates);
		$table = str_replace('`','',$table); //ugly sql-injection protection!
		$primarykey = str_replace('`','',$primarykey); //ugly sql-injection protection!
		if (!is_numeric($primaryvalue)) {
			$primaryvalue = "'".mysql_real_escape_string($primaryvalue)."'";
		}
		return "UPDATE `$table` SET ".join(',',$a)." WHERE `$primarykey` = $primaryvalue";
	}
	
}
