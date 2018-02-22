<?php
/**
 * this file contains all credetials nescessary to connect to the database
 * as well as some ways to protect access to them
 * @author Israel Santiago
 */
Class Config{
	
	//instance variables
	private $host = '';
	private $dbname = '';
	private $username = '';
	private $password = '';
	private $pass = '';
	private $conPass = '';
	
	/**
	 * constructor only allows access to instance variables when it is build with a
	 * specific string $pass
	 * @param string $string
	 */
	public function __construct($string){
		$this->conPass = $string;
	}
	
	/**
	 * getter for the database host
	 * @return string
	 */
	public function getHost(){
		if($this->pass == $this->conPass){
			return $this->host;
		} else {
			return '';
		}
	}
	
	/**
	 * getter for the database name
	 * @return string
	 */
	public function getDBName(){
		if($this->pass == $this->conPass){
			return $this->dbname;
		} else {
			return '';
		}
	}
	
	/**
	 * getter for the database username
	 * @return string
	 */
	public function getUsername(){
		if($this->pass == $this->conPass){
			return $this->username;
		} else {
			return '';
		}
	}
	
	/**
	 * getter for the database password
	 * @return string
	 */
	public function getPassword(){
		if($this->pass == $this->conPass){
			return $this->password;
		} else {
			return '';
		}
	}
}