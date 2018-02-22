<?php
/***
 * mobileJoinHousehold.php
 *
 * Script that handles adding a user to a household. The reason of why this script
 * is separated is to put less of a work to the app. The app could run a query to
 * retrieve a household id and return it to the Mobile app later to run
 * another query to update the user info so that the user is set to pending to
 * join that household. This script handles both and returns a
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
        $value = $unpackData[0];
        $householdName = $unpackData[1];
        
        $retrieveHouseholdId =
        $pdo->prepare('SELECT HouseholdID FROM hhm_households WHERE HouseholdName = :householdName');
        
        if ($retrieveHouseholdId->execute(array(':householdName'=>$householdName))) {
            
            $queryResult = $retrieveHouseholdId->fetch(PDO::FETCH_ASSOC);
            
            $householdId = $queryResult['HouseholdID'];
            
            $retrieveUserStatus =
            $pdo->prepare('UPDATE hhm_users 
                SET UserStatus = :status , HouseholdID = :householdId 
                WHERE UserID = :value');
            
            $result = "";
            
            if ($retrieveUserStatus->execute(array(':status'=>'pending',':householdId'=>$householdId,':value'=>$value))) {
                $result = "completed";//query completed
            } else {
                $result = Errors::$FAILED_TO_EXECUTE_TRANSACTION;//query failed
            }
            
            unset($dataToProcess,$unpackData,$value,$householdId,$householdName);
            echo $result;
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