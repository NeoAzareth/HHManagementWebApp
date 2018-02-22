<?php
/***
 * mobileErrors.php
 *
 * Contains a simple class with possible errors that the scripts may produce.
 * In some instances the scripts may produce a "<br />" output that is handled
 * by the mobile app as server down; However, there appears to be a possible output
 * that lets the Mobile app to create null objects that makes it crash...
 *
 * @author Israel Santiago
 * @see Mobile App ErrorHandler.java for the counter part of this simple class.
 */
class Errors {
    
    //Possible errors that the scripts may throw
    
    //wrong email or password
    static $WRONG_EMAIL_OR_PASSWORD = 0;
    //used so that the app knows the dirrence between retrival to verif info or run queries
    static $FAILED_TO_RETRIEVE_INFO = 1;
    //self explanatory
    static $NO_RECORDS = 2;
    //could not complete a query
    static $FAILED_TO_EXECUTE_TRANSACTION = 3;
    //this message is throw if a query intents to modify info related to an unexitsing
    //household, implemented on the unlikely possibility that the admin deletes
    //the household a few transaction before a member of that household tries to
    //interact with that household
    static $HOUSEHOLD_NO_LONGER_EXISTS = 4;
    //used when a code that emails something fails
    static $FAILED_TO_EMAIL = 5;
    //returned when the users are not done
    static $USERS_NOT_DONE = 6;
    //returned when there is no need to create a report
    static $NO_NEED_REPORT = 7;
}