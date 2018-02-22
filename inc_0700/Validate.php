<?php
/**
 * this class contains static methods to validate user input
 */

class Validate{
	
	/**
	 * validates any number of variables for empty input 
	 * @return boolean
	 */
	public static function isEmpty(){
		$args = func_get_args();
		foreach ($args as $value){
			if($value == '' || $value == ' '){
				return true;
			}
		} return false;
	}
	
	/**
	 * validates an int to be in a range of 0 to 10000
	 * @param int $amount
	 * @return boolean
	 */
	public static function isValidAmount($amount){
		if((double)($amount) >= 10000.00 || (double)($amount) <= 0.0 ){
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * this method is complex in a way that generalizes queries to find if something
	 * exists in the database given the token as the thing that you are looking for.
	 * 
	 * SELECT $id FROM $table WHERE $column = $token
	 * 
	 * further explanation below
	 * 
	 * @param string $token, usually a string but it could be a number! Why not!?
	 * @param string $id, used to validate the existence of such token among other things... 
	 * @param string $column, column where the possible token may be
	 * @param string $table, the name of the table where the toke may be located
	 * @param object $pdo, PDO object Connection
	 * @return boolean
	 */
	public static function isTokenAvailable($token,$id,$column,$table,$pdo){
		
	    //prepare basic query
		$checkHhName =
		$pdo->prepare('
				SELECT '.$id.' FROM '.$table.'
				WHERE '.$column.' = :token
				');
		//execute query
		$checkHhName->execute(array('token'=>$token));
		
		//fetch data
		$info = $checkHhName->fetch(PDO::FETCH_ASSOC);
		
		//this could be use to make sure somthing does not exists
		//if empty it provides knowledge that for example an email address is unique
		if(empty($info)){
			return true;
		} else {//or that there is something to proceed
		    //the id data from the database is saved to continue with some other process
		    //e.g. this is useful to reset the password of a user that we do not know anything at all
		    //see User::resetPassword() where the user id is pulled from this same variable!!
			$_SESSION['tokenID'] = $info["$id"];
			return false;
		}
	}
	
	/**
	 * checks for the strenght of a given password using regular expressions
	 * 
	 * @param string $password
	 * @return boolean
	 */
	public static function passwordStrenght($password){
		if (preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#", $password)){
			return true;
		} else {
			return false;
		}
	}
}