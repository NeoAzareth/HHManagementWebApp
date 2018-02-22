<?php
/***
 * Scripts that handles registration of a new user.
 * 
 * Nothing much to be said, takes paramters through the post and inserts the new
 * data into the database. Obviously, some security messures were implemented, but
 * their effectiveness has not been tested.
 * 
 * @author Israel Santiago
 * @version 1.0
 * @see mobileErrors.php; for possible error outputs
 */
//includes
include_once 'mobileIncludes.php';

//connection object
$Connection = new Connection();
$pdo = $Connection->getConnection();

//expects a code that the app passes to the script in order to ensure users are registered
//through the app
$keyCode = $_POST[KeyStrings::$KEYCODE_KEY];
$dataToProcess = $_POST[KeyStrings::$PROCESS_KEY];

if (password_verify($keyCode, $Connection->getCode())){
	
			//variables expected from the app
	$unPackData = explode(KeyStrings::$UNPACK_KEY, $dataToProcess);
	$lastName = $unPackData[0];
	$firstName = $unPackData[1];
	$email = $unPackData[2];
	$pw = $unPackData[3];
	//hash the incoming user password
	$pwHash = password_hash($pw, PASSWORD_DEFAULT);
	
	//prepare the query to register
	$registerNewUser =
	$pdo->prepare('INSERT INTO hhm_users
		VALUES (NULL,
		:lastName,
		:firstName,
		:email,
		:userPW,
		:userLevel,
		:userStatus,
		NULL)');
	
	//execute the query
	if ($registerNewUser->execute(array (
			':lastName' => $lastName,
			':firstName' => $firstName,
			':email' => $email,
			':userPW' => $pwHash,
			':userLevel' => 'member',
			':userStatus' => 'not in'
	))) {
		//echos a success message if completed
		echo 'completed';
	} else {
		//otherwise a failed message if not
		echo Errors::$FAILED_TO_EXECUTE_TRANSACTION;
	}	
	
} else {
	//page loads nothing if the keycode does not matches
	echo "";
}

//destroy all variables
unset($keyCode);
unset($lastName);
unset($firstName);
unset($email);
unset($pw);
unset($pwHash);
unset($pdo);
unset($Connection,$GLOBALS['ROOT_PATH']);