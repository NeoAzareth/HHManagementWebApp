<?php
/***
 * Script that handles info retrival from the database
 * 
 * Unlike its brother mobileMultiRetrieve.php this script will return a single
 * row of data from the database.
 * 
 * @author Israel Santiago
 * @version 1.0
 * @see mobileErrors.php; for possible error outputs
 */
//includes
include_once 'mobileIncludes.php';

$Connection = new Connection();
$pdo = $Connection->getConnection();

$email = $_POST[KeyStrings::$EMAIL_KEY];
$userPW = $_POST[KeyStrings::$USER_PW_KEY];
$dataToRetrieve = $_POST[KeyStrings::$RETRIEVE_KEY];


$getUserInfoQuery =
$pdo->prepare('SELECT UserPW FROM hhm_users
					WHERE Email =:email');
if ($getUserInfoQuery->execute(array(':email'=>$email))) {
    
    $hashPW = $getUserInfoQuery->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify($userPW, $hashPW["UserPW"])) {
        
        unset($hashPW);
        $unPackData = explode(KeyStrings::$UNPACK_KEY, $dataToRetrieve);
        unset($dataToRetrieve);
        $query = $unPackData[0];
        $value = $unPackData[1];
        
        $retrieveInfoQuery =
        $pdo->prepare($query);
        
        if ($retrieveInfoQuery->execute(array(':value'=>$value))) {
            
            $data = $retrieveInfoQuery->fetch(PDO::FETCH_ASSOC);
            unset($query,$value);
            
            if ($data) {
                $result = '';
                foreach ($data as $value) {
                    $result .= $value . '-c-';
                }
                unset($data);
                echo $result;
            } else {
                echo Errors::$NO_RECORDS;
            }
        } else {
            echo Errors::$FAILED_TO_RETRIEVE_INFO;
        }
    } else {
        echo Errors::$WRONG_EMAIL_OR_PASSWORD;
    }
} else {
    echo Errors::$FAILED_TO_RETRIEVE_INFO;
}

unset($email);
unset($userPW);
unset($pdo);
unset($Connection,$GLOBALS['ROOT_PATH']);