<?php
/***
 * Script that handles verification of data.
 * 
 * Meaning it will check if a given value exits in the database. Examples of such
 * values could be an email or a household name that need to be unique in order
 * for the Mobile app to work
 *
 * @author Israel Santiago
 * @version 1.0
 * @see mobileErrors.php; for possible error outputs
 */
include_once 'mobileIncludes.php';

$Connection = new Connection();
$pdo = $Connection->getConnection();

//expects a code that the app passes to the script in order to ensure users are registered
//through the app
$keyCode = $_POST[KeyStrings::$KEYCODE_KEY];
$dataToVerify = $_POST[KeyStrings::$VERIFY_KEY];


if (password_verify($keyCode, $Connection->getCode())) {
    
    $dataAsList = explode(KeyStrings::$UNPACK_KEY, $dataToVerify);
    $query = $dataAsList[0];
    $value = $dataAsList[1];
    
   
    
    $verfiyData =
    $pdo->prepare($query);
    
    if ($verfiyData->execute(array(':value'=>$value))) {
        $hashPW = $verfiyData->fetch(PDO::FETCH_ASSOC);
        if(empty($hashPW)) {
            echo "false";
        } else {
            echo 'true';
        }
    } else {
        echo Errors::$FAILED_TO_RETRIEVE_INFO;
    }
} else {
    echo "";
}

unset($Connection,$pdo,$keyCode,$dataToVerify,$dataAsList,$query,$value,$hashPW,$GLOBALS['ROOT_PATH']);