<?php
/***
 * mobileLeaveHousehold.php
 *
 * Script that handles the action of a user leaving a hosuehold. The reason of
 * why this script is separated is to put less of a work to the app. The app
 * could run a query to retrieve a household id and return it to the Mobile app later to run
 * another query to delete all the user's bills related to that household.
 * This script handles both and returns a completed message or an error code int.
 *
 * @author Israel Santiago
 * @version 1.0
 * @see mobileErrors.php; for an explanation on error codes
 */
include_once 'mobileIncludes.php';

//connection object
$Connection = new Connection();
$pdo = $Connection->getConnection();

$email = $_POST[KeyStrings::$EMAIL_KEY];
$userPW = $_POST[KeyStrings::$USER_PW_KEY];
$dataToProcess = $_POST[KeyStrings::$PROCESS_KEY];


//retrieve password
$getUserInfoQuery =
$pdo->prepare('SELECT UserPW FROM hhm_users
					WHERE Email =:email');
if ($getUserInfoQuery->execute(array(':email'=>$email))) {
	
	$hashPW = $getUserInfoQuery->fetch(PDO::FETCH_ASSOC);

	if (password_verify($userPW, $hashPW["UserPW"])) {

		unset($hashPW);
		
		$unpackData = explode(KeyStrings::$UNPACK_KEY, $dataToProcess);
		$query = $unpackData[0];
		$value = $unpackData[1];
			
		$retrieveUserStatus =
		$pdo->prepare($query);
		
		$result = "";
		
		if ($retrieveUserStatus->execute(array(':userID'=>$value))) {
			
		    $deleteHousehold =
		    $pdo->prepare('DELETE FROM hhm_bills WHERE UserID = :userID');
		    
		    if ($deleteHousehold->execute(array(':userID'=>$value))) {
		        $result = "completed";//query completed
		    } else {
		        $result = Errors::$FAILED_TO_EXECUTE_TRANSACTION;
		    }
		} else {
			$result = Errors::$FAILED_TO_EXECUTE_TRANSACTION;//query failed
		}
		
		unset($dataToProcess,$unpackData,$query,$value);
		echo $result;
		
	} else {
		echo Errors::$WRONG_EMAIL_OR_PASSWORD;
	}//password authetication
} else {
	echo Errors::$FAILED_TO_RETRIEVE_INFO;
}//password retrival


//unset all variables
unset($email);
unset($userPW);
unset($pdo);
unset($Connection,$GLOBALS['ROOT_PATH']);