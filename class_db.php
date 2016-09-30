<?php

/*
******************************************************************
* Package: Core PDO Class
* Author: Atakan KOC / @atakank77 <atakank77@gmail.com>
* Web: http://www.pcgunluk.com
* Licence: The MIT License (MIT)
******************************************************************/

define('DB_BOTH', 0);
define('DB_ASSOC', 1);
define('DB_NUM', 2);

class core_pdo {
	public $db = null;
	public $query_count = 0;
	public $sql = '';
	public $query_list = array();
	public $show_error = false;
	private $config = array();
	
	function __construct(array $config)
	{
		$this->config = array(
			'driver'	=> (($config['driver']) ? $config['driver'] : 'mysql'),
			'host' 		=> (($config['host']) ? $config['host'] : 'localhost'),
			'charset' 	=> (($config['charset']) ? $config['charset'] : 'utf8'),
			'collation' => (($config['collation']) ? $config['collation'] : 'utf8_general_ci'),
			'port'		=> (($config['port']) ? $config['port'] : '3306'),
			'dbname' 	=> $config['dbname'],
			'user' 		=> $config['dbuser'],
			'password' 	=> $config['dbpass'],
			'unix' 		=> $config['unix_socket'],
			'prefix'	=> $config['prefix']
		);
		
		if ($config['driver'] == 'mysql')
		{
			if ($this->config['unix'])
			{
				$dsn = 'mysql:unix_socket='.$this->config['unix'].';dbname='.$this->config['dbname'];
			}
			else
			{
				$dsn = 'mysql:host='.$this->config['host'].';port='.$this->config['port'].';dbname='.$this->config['dbname'];
			}
		}
		else
		{
			$dsn = 'sqlite:' . $this->config['dbname'];
		}
		
		$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$this->config['charset']. ' COLLATE ' .$this->config['collation']);
		try 
		{
			$this->db = new PDO($dsn, $this->config['user'], $this->config['password'], $options);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
		$this->error('Connection failed: ' . $e->getMessage());
		}
	}

	function __destruct()
	{
		$this->db = null;
	}
  
	public query_log ($sql, $qcount = true)
	{
		if ($qcount)
		{
			$this->query_count++;
		}

		$this->sql = preg_replace('#\s+#', ' ', $query);
		$this->query_list[] = $this->sql;		
	}

	public function escape($str)
	{
		return $this->db->quote(trim($str));
	}
  
	public function query ($sql)
	{
		$this->query_log($sql);
		$result = $this->db->query($sql);
		if (!$result and $this->show_error)
		{
			$this->error('Query Error :');
		}
		return $result;
	}
	
	public function query_first ($sql)
	{
		$this->query_log($sql);
		$sth = $this->db->prepare($sql);
		$sth->execute();
		$result = $sth->fetchColumn();
		return $result;
	}
	
	public function query_insert($table, $array_values = array())
	{
		$count = 0;
		foreach($array_values as $key => $value)
		{
			$count++;
			$fields .= ($count > 1 ? ',':'') . '\''.$key.'\'';
			$values .= ($count > 1 ? ',':'') . '\''.$this->escape($value).'\'';
		}
    
		$queryresult = $this->query("INSERT INTO ".$this->config['prefix']."$table ($fields) VALUES ($values)");
		return $this->db->lastInsertId();
	}
	
	public function query_update($table, $array_values = array(), $where="")
	{
		$count = 0;
		foreach($array_values as $key => $value)
		{
			$count++;
			$values .= ($count > 1 ? ',':'') . '\''.$key.'\' = ' . '\''.$this->escape($value).'\'';
		}
		$queryresult = $this->query("UPDATE ".$this->config['prefix']."$table set $values ".($where ? " WHERE $where":""));
		return $queryresult;		
	}
	
	public function query_delete($table, $where="", $limit="")
	{
		$queryresult = $this->query("DELETE FROM ".$this->config['prefix']."$table".($where ? " WHERE $where":"").($limit ? " LIMIT $limit":""));
		return $queryresult;
	}

	public function error($str)
	{
		$msg = '<h1>Database Error</h1>';
		$msg .= '<h4>Query: <em style="font-weight:normal;">"'.$this->sql.'"</em></h4>';
		$msg .= '<h4>Error: <em style="font-weight:normal;">'.$this->error.'</em></h4>';
		die($msg);

		if (!$this->show_error)
		{
			return true;
		}
		
		if ($this->sql)
		{
			$errortext = "Invalid SQL: ".$errortext."\r<br />" . chop($this->sql) . ';';
		}
		
		$error_msg = $this->db->errorInfo();
		
		$text_errno = $error_msg[0] . ' ' . $error_msg[1];
		$text_error = $error_msg[2];
		$date = date('l, F jS Y @ h:i:s A');
    
		$messagex = '<div style="width:600px;padding:10px;border:5px solid gray;">';
    
		$messagex .= str_replace(array('<', '>', '"','&lt;br /&gt;'),array('&lt;', '&gt;', '&quot;', '<br />'), $errortext)."<br /><br />";
		
		if ($text_error or $text_errno)
		{
			$messagex .= "MySQL Error : $text_error<br />";
			$messagex .= "Error Number : $text_errno<br />";
		}
		
		$messagex .= "Error Date : $date<br />";
		$messagex .= "</div>";
		
		if ($this->db)
		{
			$this->close();
		}
    
		die($messagex);
	}
  
	public function close()
	{
		$this->db = null;
	}
}
?>
