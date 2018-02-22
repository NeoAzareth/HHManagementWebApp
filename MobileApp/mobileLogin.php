<?php
/***
 * mobileLogin.php
 *
 * handles the user login by retrieving necessary information for the MobileApp
 * to create a Member Object or an error message if failed. On previous versions
 * this action was handled by another script that handled multiple actions.
 * Implemented in order to simplify the scripts.
 *
 * @author Israel Santiago
 * @version 1.0
 * @see mobileErrors.php; for an explanation on error codes
 */
include_once 'mobileIncludes.php';

$Connection = new Connection();
$pdo = $Connection->getConnection();



$email = $_POST[KeyStrings::$EMAIL_KEY];
$userPW = $_POST[KeyStrings::$USER_PW_KEY];

$getHashPW = 
$pdo->prepare('SELECT UserPW FROM hhm_users WHERE Email =:email');

if ($getHashPW->execute(array(':email'=>$email))) {
    
    
    $hashPW = $getHashPW->fetch(PDO::FETCH_ASSOC);
    
    
    if (password_verify($userPW, $hashPW['UserPW'])) {
        
        
        unset($hashPW);
        
        $retrieveUserInfo =
        $pdo->prepare('
            SELECT UserID, LastName, FirstName, UserLevel, UserStatus, HouseholdID
            FROM hhm_users
            WHERE Email = :email
            ');
        if ($retrieveUserInfo->execute(array(':email'=> $email))) {
            $hashPW = $retrieveUserInfo->fetch(PDO::FETCH_ASSOC);
            if ($hashPW) {
                $result = '';
                foreach ($hashPW as $value) {
                    $result .= $value . '-c-';
                }
                unset($hashPW);
                echo $result;
            } else {
                echo ERRORS::$NO_RECORDS;
            }
        } else {
            echo ERRORS::$FAILED_TO_RETRIEVE_INFO;
        }
    } else {
        echo ERRORS::$WRONG_EMAIL_OR_PASSWORD;
    }
} else {
    echo ERRORS::$WRONG_EMAIL_OR_PASSWORD;
}
unset($email);
unset($userPW);
unset($Connection);
unset($pdo,$GLOBALS['ROOT_PATH']);