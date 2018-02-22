<?php
include_once $GLOBALS['ROOT_PATH'] . 'inc_0700/Config.php';
/***
 * Connection class is used to retrieve a PDO database object
 * in order to communicate with the database
 * @author neoaz
 */
Class Connection{
	
	//instance variables
	private $pdo = NULL;
	private $key = '';
	private $code = '';
	
	/**
	 * constructor
	 */
	public function __construct(){
		$config = new Config($this->key);
		$pdo = new PDO($config->getHost().';'.$config->getDBName()
				,$config->getUsername()
				,$config->getPassword());
		$this->pdo = $pdo;
	}
	
	/**
	 * getter for the pdo connection
	 * @return PDO
	 */
	public function getConnection(){
		return $this->pdo;
	}
	
	/**
	 * getter for a hashed code
	 * used by the android version as an autenticator
	 * @return string
	 */
	public function getCode(){
		return $this->code;
	}
	
}