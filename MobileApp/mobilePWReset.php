<?php
/***
 * Script that handles user password reset.
 *
 * Previosuly, the code was placed in another script file that handled more than
 * one action based on the parameters passed to it. Moved to a single file to 
 * simplify the scripts
 *
 * @author Israel Santiago
 * @version 1.0
 * @see mobileErrors.php; for possible error outputs
 */
include_once 'mobileIncludes.php';
include_once 'functions.php';

//connection object
$Connection = new Connection();
$pdo = $Connection->getConnection();


$keyCode = $_POST[KeyStrings::$KEYCODE_KEY];
$newPW = $_POST[KeyStrings::$USER_PW_KEY];
$email = $_POST[KeyStrings::$EMAIL_KEY];

if (password_verify($keyCode, $Connection->getCode())) {
    $newHashedPW=password_hash($newPW,PASSWORD_DEFAULT);
    if (sendPassword($email, $newPW)) {
        $retrieveUserStatus =
        $pdo->prepare("UPDATE hhm_users SET UserPW = :pw WHERE Email = :em");
        if ($retrieveUserStatus->execute(array(':pw'=>$newHashedPW,':em'=>$email))) {
            echo "completed";//transaction completed
        } else {
            echo Errors::$FAILED_TO_EXECUTE_TRANSACTION;//transaction failed
        }
    } else {
        echo Errors::$FAILED_TO_EMAIL;
    }
} else {
    echo Errors::$FAILED_TO_RETRIEVE_INFO;
}

unset($Connection);
unset($pdo);
unset($keyCode);
unset($email);
unset($newPW);
unset($newHashedPW,$GLOBALS['ROOT_PATH']);