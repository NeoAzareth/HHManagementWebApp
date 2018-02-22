<?php
/***
 * Script in charge of generating a Excel spreadsheet once all the users of 
 * an associated id are done.
 * 
 * Validates the access to the process, users not been done, need for report 
 * (in case none of the users have bills) and fail to mail report.
 * 
 * @author Israel Santiago
 * @version 1.0
 */

include_once 'mobileIncludes.php';
include_once $GLOBALS['ROOT_PATH'].'inc_0700/Household.php';
include_once $GLOBALS['ROOT_PATH'].'inc_0700/Member.php';
include_once $GLOBALS['ROOT_PATH'].'inc_0700/Bill.php';

$keyCode = $_POST[KeyStrings::$KEYCODE_KEY];
$householdID = $_POST[KeyStrings::$VERIFY_KEY];

$conn = new Connection();


if (password_verify($keyCode, $conn->getCode())){
    $pdo = $conn->getConnection();
    $currentMonth = date('Y-m');
    $household = new Household($householdID,$pdo);
    
    if ($household->areAllUsersDone()) {
        if ($household->needReport()) {
            if ($household->mailMonthlySpreadsheet()) {
                echo "completed";
            } else {
                echo Errors::$FAILED_TO_EMAIL;
            }
        } else {
            echo Errors::$NO_NEED_REPORT;
        }
    } else {
        echo Errors::$USERS_NOT_DONE;
    }
} else {
    echo "";
}

unset($keyCode,$householdID,$conn,$pdo,$currentMonth,$GLOBALS['ROOT_PATH']);