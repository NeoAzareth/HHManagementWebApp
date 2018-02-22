<?php
/***
 * mobileDeleteHousehold.php
 *
 * Script that handles deleting a household. The reason of why this script
 * is separated is to put less of a work to the app. The app could run a query to
 * delete the household and return a completed string to the Mobile app later to run
 * another query to update the user info so that the user is no longer related
 * to an unexisting Household. This script handles both and returns a
 * completed message or an error code int.
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
        $householdID = $unpackData[1];
        
        $retrieveUserStatus =
        $pdo->prepare($query);
        
        $result = "";
        
        if ($retrieveUserStatus->execute(array(':householdID'=>$householdID))) {
            
            $deleteHousehold =
            $pdo->prepare('DELETE FROM hhm_households WHERE HouseholdID = :householdID');
            
            if ($deleteHousehold->execute(array(':householdID'=>$householdID))) {
                $result = "completed";//query completed
            } else {
                $result = Errors::$FAILED_TO_EXECUTE_TRANSACTION;
            }
        } else {
            $result = Errors::$FAILED_TO_EXECUTE_TRANSACTION;//query failed
        }
        
        unset($dataToProcess,$unpackData,$query,$householdID);
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