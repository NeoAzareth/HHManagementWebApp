<?php
/***
 * Script that handles the insert, update and delete actions to the database.
 * 
 * As the previous sentence implies, the script will run any of the aforemention
 * actions. Unlike previous versions though, the script will also check if the 
 * household associated with action does still exists, if not it will return a 
 * message that the Mobile App will handle accordingly
 * 
 * @author Israel Santiago
 * @version 1.0
 * @see mobileErrors.php; for possible error outputs
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
	    
	    $checkIfHouseholdExists =
	    $pdo->prepare("SELECT HouseholdID FROM hhm_users WHERE Email = :email");
	    
	    if ($checkIfHouseholdExists->execute(array(':email'=>$email))) {
	        $householdID = $checkIfHouseholdExists->fetch(PDO::FETCH_ASSOC);
	        
	        if ($householdID['HouseholdID'] == NULL) {
	            echo Errors::$HOUSEHOLD_NO_LONGER_EXISTS;
	        } else {
	            unset($hashPW);
	            
	            $unpackData = explode(KeyStrings::$UNPACK_KEY, $dataToProcess);
	            $query = $unpackData[0];
	            $value = $unpackData[1];
	            
	            $retrieveUserStatus =
	            $pdo->prepare($query);
	            
	            $result = "";
	            
	            if ($retrieveUserStatus->execute(array(':value'=>$value))) {
	                $result = "completed";//query completed
	            } else {
	                $result = Errors::$FAILED_TO_EXECUTE_TRANSACTION;//query failed
	            }
	            
	            unset($dataToProcess,$unpackData,$query,$value);
	            echo $result;
	        }
	    } else {
	        echo Errors::$FAILED_TO_RETRIEVE_INFO;
	    }
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