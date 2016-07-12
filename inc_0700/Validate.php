<?php

 class Validate{
 	
 	function __construct(){}
 	
 	public function pdoConnection(){
 		$pdo = new PDO(
 			'DB credentials go here');
 		return $pdo;
 	}
 	
 	/***
 	 * Proccess the login form
 	 * @param string $userName, the user's name
 	 * @param string $userPW, the password to be compared against the DB
 	 * @return string, used as feedback to the user
 	 */
 	public static function proccessLogin($userName, $userPW){
 		//string that holds feedback for user
 		$error = '';
 		
 		$pdo = Validate::pdoConnection();
 	
 		if ($userName == '' || $userPW == '') {//checks empty fields
 			$error = "Error! All fields are required";
 		} else {
 			//retrieves a password based on the username
 			$getPassQuery =
 			$pdo->prepare('SELECT UserPW FROM sp16_users
					WHERE Username =:username');
 				
 			if ($getPassQuery->execute(array('username'=>"$userName"))) {
 				$info = $getPassQuery->fetch(PDO::FETCH_ASSOC);
 	
 				if (password_verify($userPW, $info["UserPW"])) {
 					
 					unset($info);
 					$getUserInfo =
 					$pdo->prepare('
 							SELECT FirstName, UserID, UserLevel, HouseholdID
 							FROM sp16_users Where Username =:username');
 					
 					$getUserInfo->execute(array('username'=> $userName));
 					$info = $getUserInfo->fetch(PDO::FETCH_ASSOC);
 					
 					$_SESSION['user']['userid'] = $info['UserID'];
 					$_SESSION['user']['userlevel'] = $info['UserLevel'];
 					$_SESSION['user']['householdid'] = $info['HouseholdID'];
 					/*upon succesful validation two objects are created
 					 * and stored in the session
 					 */
 					/*$myUser = new User($userName,$this->currentMonth,$this->pdo);
 					$_SESSION["user"] = $myUser;
 						
 					$myHousehold = new Household($userName,$this->currentMonth, $this->pdo);
 					$_SESSION["household"] = $myHousehold;
 					*/	
 					//a cookie is also set with the user firstname
 					$name = $info['FirstName'];
 					setcookie("name",$name,time()+86400);
 					//user is redirected to overview page
 					header('Location: overview.php');
 				} else {
 					$error = 'Wrong username or/and password!';
 				}
 			} else {
 				$error = 'Wrong username or/and password!';
 			}
 		}
 		return $error;//an error is returned if something went wrong
 	}
 	
 	/***
 	 * validates the add bill form
 	 * @param string $array, takes the post array as parameter
 	 * @return array, returns an array containing the form varibles as well as
 	 * a string used for feedback
 	 */
 	public static function validateAddBillForm($results,$user,$household){
 		//used for feedback
 		$result = '';
 		//bill description
 		$desc = filter_var($results['desc'],FILTER_SANITIZE_STRING);
 		//bill amount
 		$amount = filter_var($results['amount'],FILTER_SANITIZE_STRING);
 		
	 	if ($desc == '' || $amount == '' ) {
 			$result = 'All fields are required';
 		} else if ($amount >= 10000) {
 			$result = 'The max amount should not exccede $9999.00.';
 		} else if ((int)($amount) == 0){//checks if the amount is not a number
 			$result = 'The amount must be a number from .00 to 9999.99';
 		} else if ($results['category'] == 'select'){//makes sure the select value is not set
 			$result = 'Please select on option for the category dropdown list';
 		} else {//tries to add the bill on success
 			//calls a method that belongs to the user class to add a bill
 			//it also pass the DB connection to it
 			$pdo = Validate::pdoConnection();
 			$userID = $user->getUserID();
 			$hhID = $household->getHhID();
 			$bill = new Bill(0, $desc, $results['category'], $amount, 0);
 			if ($bill->addBill($pdo,$userID,$hhID)) {
 				//upon successfuly adding a bill into the DB
 				//it refreshes the users bills, returns a success string,
 				//and sets two variables used to keep the users values to empty strings
 				//$user->getUserBills($this->currentMonth,$this->myPdo,TRUE);
 				$result = 'Bill successfully added';
 				$results =[];
 			} else {// if writing into the DB fails it tells the user tp try later
 				$result = 'Somethin went wrong... try again later';
 			}
 		}
 		//saves the feedback string and the user's values to be used by the form
 		$results['result'] = $result;
 		return $results;
 	}
 	
 	/***
 	 * Validates the field of the update bill form
 	 * @param string $array, an array that presumably contains POST data
 	 * @return string, contains feedback info
 	 **********************************/
 	public static function validateUpdateBillForm($array,$user){
 		
 		$result = '';
 		$userBills = $user->getUserBillsIDs();
 		$editID = $_SESSION['editbill'];
 	
 		if ($array['desc'] == '' || $array['amount'] == '' ){//checks empty
 			$result = 'All fields are required';
 		} else if (!(in_array($editID, $userBills))){
 			$result = 'Invalid data';
 		} else if ($array['amount'] >= 10000) {
 			$result = 'The max amount should not exccede $9999.00.';
 		} else if ((int)($array['amount']) == 0){
 			$result = 'The amount must be a number from .00 to 9999.99';
 		} else if ($array['category'] == 'select'){
 			$result = 'Please select on option for the category dropdown list';
 		} else {//calls the update bill info on success
 			$pdo = Validate::pdoConnection();
 			$bill = new Bill($editID);
 			if($bill->updateBill($array,$pdo)){
 				$result = 'Bill successfully updated';
 				$_SESSION['editbill'] = 0;
 				unset($array);
 			} else {
 				$result = 'Somethin went wrong... try again later';
 			}
 		}
 		$array['result'] = $result;
 		return $array;
 	}
 	
 	/***
 	 * Validates the delete bill form
 	 * @param string $array, expects a POST array
 	 * @return string[], an array containing a string with feedback
 	 */
 	public static function validateDeleteBillForm($results,$user){
 	
 		$result = '';
 		//retrieves the user's bills ids for reference
 		$userBills = $user->getUserBillsIDs();
 		//counts the bills deleted
 		$billsDeleted = 0;
 		//used to save valid bill's ids
 		$validBillIDs = array();
 	
 		//loops through the post array to save valid ids that belong to the user
 		foreach ($results as $key => $id) {
 			//checks that the key contains the word "bill"
 			//and that the id belongs to the user
 			if (strpos($key, 'bill')>= 0 && in_array($id, $userBills)) {
 				//saves the id in the valid ids array
 				array_push($validBillIDs, $id);
 			}
 		}
 		if (!(empty($validBillIDs))) {//if the valid ids array is not empty
 			foreach ($validBillIDs as $id) {//loops through the array and deletes bills
 				$pdo = Validate::pdoConnection();
 				$bill = new Bill($id);
 				$bill->deleteBill($pdo);
 				$billsDeleted ++;
 			}
 			//simple format for the word bill based on the number of bills
 			if ($billsDeleted == 1) {
 				$result = $billsDeleted . ' bill deleted';
 			} else {
 				$result = $billsDeleted . ' bills were deleted';
 			}
 			//refreshes the bill
 		} else {//if the valid array ids is empty returns this
 			$result = 'There is nothing checked for deletion...';
 		}
 		$results['result'] = $result;
 		return $results;
 	}
 	
 	/***
 	 * Validates the customReportForm
 	 * @param unknown $array, POST data
 	 */
 	public static function validateReportForm($postArray,$household){
 	
 		if(isset($postArray['user']) && isset($postArray['category']) && isset($postArray['month'])) {
 			$name = filter_var($postArray['user'],FILTER_SANITIZE_STRING);
 			$category = filter_var($postArray['category'],FILTER_SANITIZE_STRING);
 			$date = filter_var($postArray['month'],FILTER_SANITIZE_STRING);
 			$postArray = array('name'=> $name,'category'=> $category,'date'=> $date);
 			$pdo = Validate::pdoConnection();
 			return $household->retrieveReportData($postArray,$pdo);
 		} else {
 			header('Locate: report.php');
 		}
 	
 	}
 	
 	/**
 	 * cleans mobile number formattting () -
 	 * @param unknown $number
 	 */
 	public static function cleanseMobilePhone($number){
 		$removeList = array('(',')',' ','-');
 	
 		foreach ($removeList as $char) {
 			$number = str_replace($char, '', $number);
 		}
 	
 		return $number;
 	}
 	
 	/**
 	 * formats a mobile number (xxx) xxx-xxxx
 	 * @param unknown $number
 	 * @return string
 	 */
 	public static function formatMobileNum($number){
 		$first  = substr($number,0,3);
 		$middle = substr($number,3,3);
 		$last = substr($number,6,4);
 	
 		$number = '('. $first.') '. $middle . '-'.$last;
 		return $number;
 	}
 	
 	/**
 	 * validates the update password form
 	 * @param unknown $array, post array
 	 */
 	public static function validateUpdatePassForm($postArray,$user){
 		//checks if any fields are missing and set the below boolean to true
 		$areAnyFieldEmpty = false;
 		foreach ($postArray as $value){
 			if(empty($value)){
 				$areAnyFieldEmpty = true;
 				break;
 			}
 		}
 		$oldPass = $postArray['oldpw'];
 		$newPass = $postArray['newpw'];
 		$confirmNewPass = $postArray['confirmnewpw'];
 		
 		$pdo = Validate::pdoConnection();
 	
 		//retrieves the old password to verify update
 		$userID = $user->getUserID();
 		$getPassQuery =
 		$pdo->prepare('SELECT UserPW FROM sp16_users WHERE UserID =:userid');
 		$getPassQuery->execute(array(':userid'=>$userID));
 		$dbPass = $getPassQuery->fetch(PDO::FETCH_COLUMN);
 	
 		if($areAnyFieldEmpty){//empty fields
 			$result = 'All fields are required';
 		} else if (!(password_verify($oldPass, $dbPass))) {//old password matches
 			$result = 'Old password invalid';
 		} else if ($newPass != $confirmNewPass) {//new password and confirm are the same
 			$result = 'New passwords don\'t match';
 		} else {//updates the password
 			if($user->updatePW($confirmNewPass,$pdo)){
 				$result = 'Password updated';
 			} else {
 				$result = 'Failed to update pass...';
 			}
 		}
 		return $result;
 	}
 	
 	/**
 	 * validate the update contact info form
 	 * @param unknown $array, post array
 	 */
 	public static function validateUpdateContactInfo($array,$user){
 		//sets default type to email
 		$type = 'email';
 		$result = '';
 		//moves the new contact to this variable
 		$contactOp = $array['updateop'];
 		//if the new contact is a valid mobile number
 		if(Validate::isValidMobileNumber($contactOp)){
 			//sets its type to 'phone'
 			$type = 'phone';
 		}
 	
 		if(empty($contactOp)){//checks if the field is empty
 			$result = 'Enter an email or mobile number';
 		} else if(!(Validate::isValidEmail($contactOp)) &&
 				!(Validate::isValidMobileNumber($contactOp))){
 					//checks if the value is not a valid phone number or valid email
 					$result = 'That is not a valid Email or Mobile number...';
 		} else if (Validate::isEmailInUse($contactOp)){//checks if the email is in use
 			$result = 'That '. $type .' is already in use...';
 		} else {//runs the User class method: updateContactInfo
 			$pdo = Validate::pdoConnection();
 			$id = $_SESSION['editcontact'];
 			$result = $user->updateContactInfo($id,$contactOp,$type, $pdo);
 		}
 		$array['result'] = $result;
 		return $array;
 	}
 	
 	/**
 	 * validates the add contact info form
 	 * @param array, post array
 	 * @return array, array with results and user field values
 	 */
 	public static function validateAddContacInfoForm($array,$user){
 		$type = 'email';
 		$result = '';
 		$desc = $array['newoption'];
 		if(Validate::isValidMobileNumber($desc)){
 			$type = 'phone';
 		}
 	
 		if(empty($desc)){
 			$result = 'Enter an email or mobile number';
 		} else if (!(Validate::isValidEmail($desc)) && !(Validate::isValidMobileNumber($desc))) {
 			$result = 'That is not a valid Email or Mobile Number...';
 		} else if (Validate::isEmailInUse($desc)){
 			$result = 'That '. $type .' is already in use...';
 		} else {
 			$pdo = Validate::pdoConnection();
 			$result = $user->addContactInfo($desc,$type,$pdo);
 			$array['newoption'] ='';
 		}
 		$array['result'] = $result;
 		return $array;
 	}
 	
 	/**
 	 * validates the deletecontacform
 	 * @param unknown $id, the id related to the contactoption to delete
 	 * @return string, feedback
 	 */
 	public static function validateDeleteContactOption($id, $user){
 		$result = '';
 		//retrives the current user's contactoption ids
 		$listOfContactIDs = $user->listOfContactIDs();
 	
 		//ensures that the id to be deleted belong to the user
 		if(!(in_array($id, $listOfContactIDs))){
 			$result = 'Invalid Id';
 		} else {//runs the deletecontact option method that belongs to the user class
 			$pdo = Validate::pdoConnection();
 			$result = $user->deleteContactOption($id,$pdo);
 		}
 		return $result;
 	}
 	
 	/**
 	 * vaidates the set to primary form
 	 * @param unknown $array, post array
 	 * @return unknown, array with results
 	 */
 	public static function validateSetPrimary($array,$user){
 	
 		$id = $array['primary'];
 		$result = '';
 		$listOfContactIDs = $user->listOfContactIDs();
 	
 		//checks that the id belongs to the current user
 		if(!(in_array($id, $listOfContactIDs))){
 			$result = 'Invalid Id';
 		} else if($id == $user->getPrefContactId()){
 			//checks if the id is the same as the current set id
 			$result = 'That is the current notification preference';
 		} else {
 			$pdo = Validate::pdoConnection();
 			$result = $user->updatePrefNotOption($id,$pdo);
 		}
 		$array['result'] = $result;
 		return  $array;
 	}
 	
 	/**
 	 * Validates the field of the check rent amount form
 	 * @param unknown $amount, the new amount
 	 * @return string, feedback
 	 */
 	public static function validateChangeRentForm($amount,$household,$user){
 		if($amount == ''){//amount is empty
 			$result = 'Enter the new rent amount!';
 		} else if ((int)($amount)== 0){//amount is a number
 			$result = 'Invalid rent amount, number must be from $.01 to $9999.99!';
 		} else if ($household->getHhRent() == $amount){//amount is different
 			$result = 'Amount is the same...';
 		} else if ($amount> 9999.99){//amount is less than the limit
 			$result = 'Rent can\'t be greater than $9999.99!';
 		} else {//runs a method belonging to the Household class to update
 			$pdo = Validate::pdoConnection();
 			$result = $user->updateRentAmount($amount,$pdo,$household);
 		}
 		$result = '<script>
				alert("'. $result .'");
				window.location.href="admin.php";
				</script>';
 		return $result;
 	}
 	
 	public static function validateResetID($household, $user,$resetID){
 		$result = '';
 		$isValidID = false;
 		$isValidStatus = false;
 		
 		foreach ($household->getMembers() as $member){
 			if($member->getUserId() == $resetID){
 				$isValidID = true;
 				if($member->getUserStatus() == 'done'){
 					$isValidStatus = true;
 				}
 				break;
 			}
 		}
 		
 		if ($isValidID != true || $isValidStatus != true){
 			$result = '<script>
				alert("Invalid data!");
				window.location.href="admin.php";
				</script>';
 		} else {
 			$result = $user->resetUserStatus($resetID);
 		}
 		return $result;
 	}
 	
 	/**
 	 * validates a registration code by comparing it to the ones on the db
 	 * @param unknown $code, code to validate
 	 * @return boolean, true if
 	 */
 	function validateRegistrationCode($code){
 		$pdo = Validate::pdoConnection();
 		$validateCode =
 		$pdo->prepare('SELECT CodeValid
				FROM sp16_codes
				WHERE CodeNum = :codenum
				');
 		$validateCode->execute(array(':codenum'=>$code));
 		$id = $validateCode->fetch(PDO::FETCH_COLUMN);
 		//saves the valid code and an id related to the household
 		if($id != 0){
 			$_SESSION['hhID']= $id;
 			$_SESSION['code'] = $code;
 			return true;
 		} else {//if the id is equals to 0 it means the code is valid but the id has been used
 			return false;
 		}
 	}
 	
 	/**
 	 * validate the add new member registration form
 	 * @param unknown $array, post array
 	 * @return array, with user fields values and feedback
 	 */
 	public static function validateNewMemberRegistrationForm($postArray){
 		$firstName = filter_var($postArray['first'],FILTER_SANITIZE_STRING);
 		$lastName = filter_var($postArray['last'],FILTER_SANITIZE_STRING);
 		$username = filter_var($postArray['username'],FILTER_SANITIZE_STRING);
 		$email = $postArray['email'];
 		$pw = $postArray['pw'];
 		$confirmPW = $postArray['confirmpw'];
 		$result = '';
 		$results = $postArray;
 		$areAnyValuesMissing = false;
 		//checks if any values are missing
 		foreach ($postArray as $value){
 			if(empty($value)){
 				//if any are missing checks this booelan to true
 				$areAnyValuesMissing = true;
 				break;
 			}
 		}
 	
 		if($areAnyValuesMissing){//checks the boolean to see if anything is missing
 			$result = 'All fields are required';
 		} else if (Validate::isUsernameInUse($username)) {//checks if the username is in use
 			$result = 'Username already in use';
 		} else if (Validate::isEmailInUse($email)) {//checks if the email is in use
 			$result = 'Email already in use';
 		} else if ($pw != $confirmPW) {//checks if password don't match
 			$result = 'Passwords don\'t match';
 		} else {//runs the register user method
 			$member = new Member($firstName,$lastName);
 			$result = $member->register($username, $email, $pw);
 		}
 		$results['result'] = $result;
 		return $results;
 	}
 	
 	/**
 	 * sends notification to users that have not finished entering their bills
 	 */
 	function sendNotifications(){
 		$currentDayOFMonth = date('F jS');
 		$currentMonth = date('F, Y');
 		$notDoneUsers  = Validate::retrieveNotdoneUserInfo();
 	
 		foreach ($notDoneUsers as $user){
 				
 			//uses the simple mail function
 			$email = $user['ContactOpDesc'];
 			$name = $user['FirstName'];
 			$householdName = $user['HouseholdName'];
 			$type = $user['ContactOpType'];
 			$subject = 'Today is '.$currentDayOFMonth.', have you added all your bills?';
 				
 			$message = '
				<html>
				<body>
				  <h3>Hello '. $name .',</h3>
				  <p>This an automated generated '. $type .' to remind you to add your bills
				  		to balance and close '. $currentMonth .' of the '.
 					  		$householdName .'.</p>
				  <p>You will receive notifications until you
				  				set your status to "done" under the manage bills page.</p>
				  <a href="http://neoazareth.com/index.php" target="_blank">
				  		HH log in.</a>
				  <p>HHManagement web app.</p>
				</body>
				</html>
			';
 			$headers  = 'MIME-Version: 1.0' . "\r\n";
 			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
 					  		
 			//if the type is a phone sends a text message, it only works with att carrier
 			if($type == 'phone'){
 				$subject = '';
 				$email .= '@txt.att.net';
 				$message = 'Hello '. $name .
 					  ', this is a remider to add your bills and close the current month!';
 					  		
 				$header = "From: HhManage\r\n";
 			}
 			mail($email, $subject, $message,$headers); 					  		
 		}
 	}
 	
 	function resetUsersStatus(){
 		$pdo = Validate::pdoConnection();
 		$resetUsers = 
 		$pdo->prepare('UPDATE sp16_users 
 				SET UserStatus = "not done"');
 		$resetUsers->execute();
 	}
 	
 	function areAllUsersDone($hhID){
 		$pdo = Validate::pdoConnection();
 		$status = 
 		$pdo->prepare('SELECT FirstName FROM sp16_users WHERE UserStatus = "not done" AND HouseholdID = :id');
 		$status->execute(array(':id'=>$hhID));
 		$info = $status->fetchAll(PDO::FETCH_COLUMN);
 		if (empty($info)) {
 			return true;
 		} else {
 			return false;
 		}
 	}
 	
 	function mailMonthlySpreadsheet($id,$currentMonth){
 		
 		$pdo = Validate::pdoConnection();
 		$household = new Household($id, $currentMonth, $pdo);
 		
 		require_once 'PHPMailer/PHPMailerAutoload.php';
 		$admin = $household->getHouseholdAdmin();
 		$adminName = $admin->getUserFullName();
 		$adminEmail = $admin->getUserEmailAddress();
 		
 		$members = $household->getMembers();
 		$sheetName = createSpreadsheet($id,$currentMonth);
 		
 		$monthAndYear = date('F, Y');
 		$hhName = $household->getHhName();
 		
 		$subject = str_replace('.xlsx', '', $sheetName) . ' closing';
 		
 		$monthSummary = $household->formatMonthlyBalanceReport();
 		
 		$message = '<html>
				<body>
				  <h3>Hello <<name>>,</h3>
				  <p>This is an automated email to notify you that '. $monthAndYear .' </br>
				  		period of the '. $hhName .' has been
				  		closed and balanced!</p>
				  <p>Here are the results: </br></p>
				  				'.$monthSummary.'
				  <p>HHManagement web app.</p>
				</body>
				</html>';
 		$file_to_attach = 'spreadsheets/'.$sheetName;
 		
 		
 		//$userEmail = $member->getUserEmailAddress();
 		//$userName = $member->getUserFirstName();
 		$email = new PHPMailer();
 		$email->From = 'isrsan2@rainbow.dreamhost.com';
 		$email->FromName = $adminName . '(HhManage Admin)';
 		$email->Subject = $subject;
 		//$email->Subject = 'Yet, another test... ignore';
 		$email->msgHTML(str_replace('<<name>>', 'Israel', $message));
 		$email->addAddress( "neoazareth@gmail.com");
 		$email->addAttachment( $file_to_attach , $sheetName );
 		$email->Send();
 		
 		
 		/*
 		foreach ($members as $member) {
 			$userEmail = $member->getUserEmailAddress();
 			$userName = $member->getUserFirstName();
 			$email = new PHPMailer();
 			$email->From = 'isrsan2@rainbow.dreamhost.com';
 			$email->FromName = $adminName . '(HhManage Admin)';
 			$email->Subject = $subject;
 			//$email->Subject = 'Yet, another test... ignore';
 			$email->msgHTML(str_replace('<<name>>', $userName, $message));
 			$email->addAddress( $userEmail);
 			$email->addAttachment( $file_to_attach , $sheetName );
 			$email->Send();
 		}
 		*/
 	}
 	
 	/**
 	 * function that retrives all users that have the 'not done' status,
 	 * used to send notifications.
 	 */
 	function retrieveNotdoneUserInfo(){
 		$pdo = Validate::pdoConnection();
 		$retrieveUserInfo =
 		$pdo->prepare('
			SELECT ContactOpDesc, ContactOpType, FirstName, HouseholdName
			FROM sp16_users u
			INNER JOIN sp16_user_contact_options uco
			ON uco.ContactOptionID = u.PreferredNotID
			INNER JOIN sp16_households h
			ON h.HouseholdID = u.HouseholdID
			WHERE u.UserStatus = "not done";');
 		$retrieveUserInfo->execute();
 		$info = $retrieveUserInfo->fetchAll(PDO::FETCH_ASSOC);
 		return $info;
 	}
 	
 	/**
 	 * Checks if an username is already in use
 	 * @param unknown $username, the username to check
 	 * @return boolean, true if exists, false if not
 	 */
 	function isUsernameInUse($username){
 		$pdo = Validate::pdoConnection();
 		$checkDB =
 		$pdo->prepare('SELECT UserID
				FROM sp16_users WHERE Username = :username
				');
 		$checkDB->execute(array(':username'=>$username));
 		$info = $checkDB->fetchAll(PDO::FETCH_ASSOC);
 		if(empty($info)){
 			return false;
 		} else {
 			return true;
 		}
 	}
 	
 	/**
 	 * helper function that checks if an emails exists in the db
 	 * @param unknown $email, the email to be checked
 	 * @return boolean, true if is in use
 	 */
 	public static function isEmailInUse($email){
 		$pdo = Validate::pdoConnection();
 		$checkDB =
 		$pdo->prepare('SELECT ContactOptionID
				FROM sp16_user_contact_options WHERE ContactOpDesc = :email
				');
 		$checkDB->execute(array(':email'=>$email));
 		$info = $checkDB->fetchAll(PDO::FETCH_ASSOC);
 		if(empty($info)){
 			return false;
 		} else {
 			return true;
 		}
 	}
 	
 	/**
 	 * validates an email using the filter_var BIF
 	 * @param unknown $email
 	 */
 	public static function isValidEmail($email){
 		if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
 			return true;
 		} else {
 			return false;
 		}
 	}
 	
 	/**
 	 * valdites a mobile number
 	 * @param unknown $number
 	 */
 	public static function isValidMobileNumber($number){
 		//checks the mobile is an actual number and that is 10 digits long
 		if((int)($number)!= 0 && strlen($number) == 10){
 			return true;
 		} else {
 			return false;
 		}
 	}
 	
 	/**
 	 * used for debuging
 	 * var_dumps a variable and kills the program
 	 * @author based on an function created by Bill Newman
 	 * @param unknown $var
 	 */
 	public static function dumpDie($var){
 		echo "<pre>";
 		var_dump($var);
 		echo "</pre>";
 		die;
 	}
 }