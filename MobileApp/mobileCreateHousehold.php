<?php
/***
 * mobileCreateHousehold.php
 *
 * Script that handles creating a new household. In previous version the
 * Mobile android app used to query another script that handled multiple things
 * at once. However, in order to simplify the communication, that code was split
 * so that each one of them handle one action. The reason of why this script
 * is separated is to put less of a work to the app. The app could run a query to
 * insert the household and retrieve a household id from the script later to run
 * another query to update the user info so that the two -User and household,
 * have a relationship in the database. This script handles both and returns a
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

//get post data
$email = $_POST[KeyStrings::$EMAIL_KEY];
$userPW = $_POST[KeyStrings::$USER_PW_KEY];
$dataToProcess = $_POST[KeyStrings::$PROCESS_KEY];


//retrieve password
$getUserInfoQuery =
$pdo->prepare('SELECT UserPW FROM hhm_users
					WHERE Email =:email');
if ($getUserInfoQuery->execute(array(':email'=>$email))) {//attempt pull password
    
    $hashPW = $getUserInfoQuery->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify($userPW, $hashPW["UserPW"])) {//verify password
        
        unset($hashPW);
        
        $unpackData = explode(KeyStrings::$UNPACK_KEY, $dataToProcess);
        $householdName = $unpackData[0];
        $householdRent = $unpackData[1];
        
        $retrieveUserStatus =
        $pdo->prepare('INSERT INTO hhm_households 
            VALUES(NULL,:householdName,:householdRent)');
        
        $result = "";
        
        if ($retrieveUserStatus->execute(array(//attempt household insertion
            ':householdName'=>$householdName, 
            ':householdRent'=>$householdRent))) {
            
            $hhID = $pdo->lastInsertId();
            $level = 'admin';
            $status = 'not done';
            
            $updateUserInfo =
            $pdo->prepare("UPDATE hhm_users
						SET HouseholdID = :hhId,
						UserLevel = :level,
						UserStatus = :status
						WHERE Email = :email");
            if ($updateUserInfo->execute(array(//attempt update of user info
                ':hhId' => $hhID,
                ':email' => $email,
                ':level' => $level,
                ':status' => $status
                
            ))) {
                $result = "completed";//query completed
            } else {
                $result = Errors::$FAILED_TO_EXECUTE_TRANSACTION;
            }
        } else {
            $result = Errors::$FAILED_TO_EXECUTE_TRANSACTION;//query failed
        }
        
        unset($dataToProcess,$unpackData,$householdName,$householdRent,$hhID);
        echo $result;//ouput the result
        
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