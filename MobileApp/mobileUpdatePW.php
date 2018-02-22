<?php
/***
 * Script that handles updating a user password.
 * 
 * Unlike the password reset script that email the new password to the user, this
 * will only update the password with the one provided by the user.
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
        
        unset($hashPW);
        
        $updateUserInfo =
        $pdo->prepare("UPDATE hhm_users SET UserPW = :pw WHERE Email = :em");
        
        $newHashPW = password_hash($dataToProcess, PASSWORD_DEFAULT);
        
        $result = "";
        
        if ($updateUserInfo->execute(array(':pw'=>$newHashPW,':em'=>$email))) {
            $result = "completed";//query completed
        } else {
            $result = Errors::$FAILED_TO_EXECUTE_TRANSACTION;//query failed
        }
        
        unset($dataToProcess,$newHashPW);
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