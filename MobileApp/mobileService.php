<?php
/***
 * Scripts that handles the notification service of the Mobile App
 * 
 * Simply takes the user id and returns the status of the user associated with
 * that id.
 * @author Israel Santiago
 * @version 1.0
 * @see mobileErrors.php; for possible error outputs
 */
include_once 'mobileIncludes.php';

$Connection = new Connection();
$pdo = $Connection->getConnection();
$value = $_POST[KeyStrings::$SERVICE_KEY];
$keyCode = $_POST[KeyStrings::$KEYCODE_KEY];

if (password_verify($keyCode, $Connection->getCode())) {
    $retrieveUserStatus = 
    $pdo->prepare("SELECT UserStatus FROM hhm_users WHERE UserID = :userID");
    
    if ($retrieveUserStatus->execute(array(':userID'=>$value))) {
        echo $retrieveUserStatus->fetch(PDO::FETCH_COLUMN);
    } else {
        echo Errors::$FAILED_TO_RETRIEVE_INFO;
    }
} else {
    echo "";
}

unset($keyCode);
unset($pdo);
unset($value);
unset($Connection,$GLOBALS['ROOT_PATH']);
