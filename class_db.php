<?php

/*
******************************************************************
* Package: Core PDO Class
* Author: Atakan KOC / @atakank77 <atakank77@gmail.com>
* Web: http://www.pcgunluk.com
* Licence: The MIT License (MIT)
******************************************************************/

class core_pdo {
	public $db = null;
	private $config = [];
	
	public function __construct(array $config)
	{
		$this->config = array(
			'driver'	=> $config['driver'],
			'host' 		=> (($config['host']) ? $config['host'] : 'localhost'),
			'charset' 	=> (($config['charset']) ? $config['charset'] : 'utf8'),
			'collation' => (($config['collation']) ? $config['collation'] : 'utf8_general_ci'),
			'port'		=> (($config['port']) ? $config['port'] : '3306'),
			'dbname' 	=> $config['dbname'],
			'user' 		=> $config['dbuser'],
			'password' 	=> $config['dbpass'],
			'unix' 		=> $config['unix_socket'],
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
		elseif ($config['driver'] == 'sqlite')
		{
			$dsn = 'sqlite:' . $this->config['dbname'];
		}
		
		$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$this->config['charset']. ' COLLATE ' .$this->config['collation']);
		
		$this->db = new PDO($dsn, $this->config['user'], $this->config['password'], $options);
	}
}
?>
