<?php
/**
 * Member class; former User class
* A class that creates member objects, allows management of user settings, adding, deleting or updating
* user bills among other methods
* @author Israel Santiago
* @package projectv2
* @link neoazareth@gmail.com
* @version 2.0
*/
Class Member{

	//instance fields
	Private $lastName;
	Private $firstName;
	Private $userID;
	Private $userLevel;
	Private $userBillIDs = [];
	Private $userBills = [];
	Private $pastUserBills = [];
	Private $userStatus;
	Private $email;
	Private $pdo;

	//multiple php consructor
	public function __construct()
	{
		
		$controller = new Connection();
		$this->pdo = $controller->getConnection();
		unset($controller);
		
		//gets the arguments passed an stores them in the $a variable
		$a = func_get_args();

		//counts the arguments passed an stores the number in the $i variable
		//very important to name the other constructor with the number of
		//arguments they take
		$i = func_num_args();

		//calls the appropiate constructor based on the number of arguments
		if (method_exists($this,$f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		}
	}

	/**
	 * constructor with one parameter
	 * @param int $id
	 */
	function __construct1($id){
		$this->userID = $id;
	}

	/**
	 * Construtor with 2 parameters
	 * @param string $userName, used to construct the object from the DB
	 * @param object $pdo, a pdo object connection
	 */
	function __construct2($userID, $pdo){
		//prepare statement
		$getUserInfo =
		$pdo->prepare('SELECT LastName, FirstName, Email, UserLevel, UserStatus
							FROM hhm_users
							WHERE UserID =:userid');
		//execute statement
		$getUserInfo->execute(array('userid'=>"$userID"));
		//pull info
		$info = $getUserInfo->fetchAll(PDO::FETCH_ASSOC);
		$info = $info[0];

		$this->lastName = $info["LastName"];
		$this->firstName = $info["FirstName"];
		$this->email = $info['Email'];
		$this->userID = $userID;
		$this->userLevel = $info["UserLevel"];
		$this->userStatus = $info["UserStatus"];
		$this->userBills = $this->getUserBills($GLOBALS['currentMonth'], $pdo);
		//$this->pastUserBills = $this->getUserBills($pastMonth, $pdo);
		$this->setBillsIDs();
	}

	/**
	 * sets the user's bills ids in an array.
	 * used as a shortcut
	 */
	function setBillsIDs(){
		$billIds = [];		
		foreach ($this->userBills as $bill){
			$id = $bill->getBillID();
			array_push($billIds, $id);
		}
		$this->userBillIDs = $billIds;
	}
	
	/**
	 * retrieves bills from the database associated with the user
	 * and given date as $month. e.g./format "2016-12"
	 * @param string $month
	 * @param object $pdo
	 * @return array $bills
	 */
	function getUserBills($month,$pdo){
		
		$month = '%'.$month.'%';

		$getUserBills =
		$pdo->prepare('SELECT BillID, BillAmount, BillDesc, BillCategory, BillDate
				FROM hhm_bills
				WHERE UserID =:userid AND BillDate LIKE :month');
		$getUserBills->execute(array('userid' => $this->userID,'month' => $month));
		$info = $getUserBills->fetchAll(PDO::FETCH_ASSOC);

		$bills = [];
		foreach ($info as $key => $value) {
			$obj = new Bill($value['BillID'], $value['BillDesc'],
				$value['BillCategory'], $value['BillAmount'], $value['BillDate']);
			array_push($bills, $obj);
		}
		return $bills;
	}

	/**
	 * getter for user id
	 */
	function getUserID(){
		return $this->userID;
	}

	/**
	 * getter for the user level
	 */
	function getUserLevel(){
		return $this->userLevel;
	}

	/**
	 * getter for the user firstname
	 */
	function getUserFirstName(){
		return $this->firstName;
	}

	/**
	 * getter for user status
	 */
	function getUserStatus(){
		return $this->userStatus;
	}

	/**
	 * gets the user's full name
	 * @return string
	 */
	function getUserFullName(){
		return $this->firstName . ' ' . $this->lastName;
	}

	/**
	 * getter for the user's bills
	 */
	function getBills(){
		return $this->userBills;
	}
	
	function getEmail(){
		return $this->email;
	}
	
	/**
	 * validates the info passed by the form and calls the
	 * appropiate bill method to add it to the database
	 * @param array $results
	 * @return array
	 */
	function addBill($results){
		$result ='';
		
		$desc = filter_var($results['desc'],FILTER_SANITIZE_STRING);
		
		$amount = filter_var($results['amount'],FILTER_SANITIZE_STRING);
		
		if(Validate::isEmpty($desc,$amount)){
			$result = "All fields are required!";
		} else if (!Validate::isValidAmount($amount)){
			$result = "The amount must be greater than 0 and les than 10,000.";
		} else if ($results['category'] == 'select'){
			$result = "Select a Category!";
		} else {
			
			$connection = new Connection();
			$pdo = $connection->getConnection();
			unset($connection);
			
			$bill = new Bill($desc,$results['category'],$amount);
			if($bill->saveBill($pdo)){
				$result = 'Bill successfully added';
				$results = [];
				$this->userBills = $this->getUserBills($GLOBALS['currentMonth'], $pdo);
			} else {
				$result = 'Something went wrong... try again later.';
			}
			
		}
		$results['result'] = $result;
		return $results;
	}
	
	/**
	 * validates the delete bill option
	 * @param array $results
	 * @return array
	 */
	function deleteBills($results){
		
		$result = '';
		$billsDeleted = 0;
		$validBillIDs = array();
		
		//this step is optional. All it does is guarantee that the user is only able to delete
		//bills associated with the current user
		foreach ($results as $key => $id) {
			if(strpos($key, 'bill')>= 0 && in_array($id, $this->userBillIDs)){
				array_push($validBillIDs, $id);
			}
		}
		
		if(!(empty($validBillIDs))){
			$connection = new Connection();
			$pdo = $connection->getConnection();
			unset($connection);
			foreach ($validBillIDs as $id) {
				
				$bill = new Bill($id);
				$bill->deleteBill($pdo);
				$billsDeleted++;
			}
			
			if($billsDeleted == 1) {
				$result = $billsDeleted . ' bill deleted!';
			} else {
				$result = $billsDeleted . ' bills deleted!';
			}
			$this->userBills = $this->getUserBills($GLOBALS['currentMonth'],$pdo);
		} else {
			$result = 'There is nothing checked for deletion...';
		}
		$results['result'] = $result;
		return $results;
	}
	
	/**
	 * proccesses the update bill option of the manage bills page
	 * @param array $results
	 * @return array
	 */
	function updateBill($results){
		$result = '';
		$editID = $_SESSION['editbill'];
		
		if(Validate::isEmpty($results['desc'],$results['amount'])){
			$result = 'All fields are required!';
		} else if(!(in_array($editID, $this->userBillIDs))){
			$result = 'Invalid data';
		} else if (!Validate::isValidAmount($results['amount'])){
			$result = 'The amount must be greater than 0 and les than 10,000.';
		} else if ($results['category'] == 'select') {
			$result = 'Select a Category!';
		} else {
			$connection = new Connection();
			$pdo = $connection->getConnection();
			unset($connection);
			$bill = new Bill($editID);
			if($bill->updateBill($results, $pdo)){
				$result = 'Bill successfully updated!';
				$_SESSION['editbill'] = 0;
				unset($results);
				$this->userBills = $this->getUserBills($GLOBALS['currentMonth'], $pdo);
			} else {
				$result = 'Something went wrong... try again later.';
			}
		}
		$results['result'] = $result;
		return $results;
	}
	
	/**
	 * function that retrieves the user's expenses divided by category
	 * @return array
	 */
	function billsDistribution(){
		$amountsArray= array('food'=>0.0,
				'utility'=>0.0,
				'maintenance'=>0.0,
				'other'=>0.0,
				'total'=>0.0);
		foreach ($this->userBills as $bill) {
			$key = $bill->getBillCategory();
			$amount = $bill->getBillAmount();
			$amountsArray["$key"] += round($amount,2);
			$amountsArray['total'] += round($amount,2);
		}
		return $amountsArray;
	}


	/**
	 * displays a table with the current user's bills
	 * @return string, html table with user's bills
	 */
	function displayUserBills(){
		//if empty returns the following string
		if (empty($this->userBills)) {
			$table = '<h4 class="text-warning">There is nothing to show...</h4>';
		} else {
			$table = '<table class="table table-striped table-hover">
				<tHead>
					<tr>
						<th>Description</th><th>Amount</th><th>Category</th><th>Date</th>
					</tr>
				</tHead>
				<tBody>
				';
			//it calls the getbill as row method of the bill class
			foreach ($this->userBills as $key => $obj) {
				$table .= $obj->getBillAsRow();
			}
			$table .= '</tBody></table>';
		}
		return $table;
	}

	/**
	 * gets the user status formated as a row for a table
	 * @return string
	 */
	function getUserStatusAsRow(){
		$row = '<tr>
				<td>'. $this->getUserFullName() .'</td>
				<td>'. ucfirst($this->userStatus) .'</td>
				<td>'. ucfirst($this->userLevel) .'</td>
				</tr>';
		return $row;
	}

	/***
	 * Changes the user status: "done" to "not done"
	 * 
	 * @param string $url, and url to be redireted to
	 * @param string $status, the new status
	 * @return string
	 */
	function changeUserStatus($url,$status){
		$connection = new Connection();
		$pdo = $connection->getConnection();
		unset($connection);
		//prepare pdo statement
		$changeStatus =
		$pdo->prepare('UPDATE hhm_users SET UserStatus = :status
				WHERE UserID = :id');
		//execute the statement while checking if successful
		if ($changeStatus->execute(array(':id'=> $this->userID,':status'=> $status))) {
				
			$result = '<script>
			alert("Status Updated");
			window.location.href="'. $url .'";
			</script>';
				
			$id = $_SESSION['user']['householdid'];
			if ($status == 'done'){
				
				$tempHousehold = new Household($id,$pdo);
				
				if ($tempHousehold->areAllUsersDone()) {
					$tempHousehold->mailMonthlySpreadsheet();
				}
				unset($tempHousehold);
			}
				
		} else {
			$result = '<script>
			alert("Something went wrong...");
			window.location.href="'. $url .'";
			</script>';
		}
		return $result;
	}

	/**
	 * proccesses and validates updating the user password
	 * @param array $postArray
	 * @return string, error or success message
	 */
	function updatePassword($postArray){
		
		$oldPass = $postArray['oldpw'];
		$newPass = $postArray['newpw'];
		$confirmNewPass = $postArray['confirmnewpw'];
		
		$connection = new Connection();
		$pdo = $connection->getConnection();
		unset($connection);
		
		$getPassQuery =
		$pdo->prepare('SELECT UserPW FROM hhm_users WHERE UserID =:userid');
		$getPassQuery->execute(array(':userid'=>$this->userID));
		$dbPass = $getPassQuery->fetch(PDO::FETCH_COLUMN);
		
		if(Validate::isEmpty($oldPass,$newPass,$confirmNewPass)){
			$result = 'All fields are required!';
		} else if(!(password_verify($oldPass, $dbPass))) {
			$result = 'Invalid current password!';
		} else if($newPass != $confirmNewPass) {
			$result = 'Confirm password doesn\'t match!';
		} else if(!Validate::passwordStrenght($newPass)) {
			$result = 'Password is not safe! <br>
					  must be 8-20 letters long <br>
					  must have at least one lower case letter <br>
					  must have at least one upper case letter <br>
                      must have at least one number';
		} else {
			$newPass = password_hash($newPass, PASSWORD_DEFAULT);
			$updatePass = 
			$pdo->prepare('UPDATE hhm_users SET UserPW =:newpass
					WHERE UserID =:userid');
			if($updatePass->execute(array('newpass'=>$newPass,
					'userid'=>$this->userID))){
				$result = 'Password updated!';	
			} else {
				$result = 'Failed to update pass...';
			}
		}
		return $result;
	}
	
	/***
	 * function that allows a user to create a household
	 * 
	 * @param array $results
	 * @return string, error or success message
	 */
	function createHousehold($results){
		$hhName = $results['createHhName'];
		$hhRent = $results['createHhRent'];
		if(Validate::isEmpty($hhName,$hhRent)){
			$results['resultCreate'] = 'Enter a name and initial household rent!';
		} else if(!Validate::isValidAmount($hhRent)){
			$results['resultCreate'] = 'Rent amount must be greater than 0 and less than 10001';
		} else if (!Validate::isTokenAvailable($hhName, 
				'HouseholdID', 'HouseholdName', 'hhm_households', $this->pdo)){
			$results['resultCreate'] = 'Household name is taken...';
		} else {
			$insertHousehold = 
			$this->pdo->prepare('
					INSERT INTO hhm_households
					VALUES (NULL,:name,:rent)
					');
			if($insertHousehold->execute(array('name'=>$hhName,'rent'=>$hhRent))){
				$hhID = $this->pdo->lastInsertId();
				$updateUserInfo = 
				$this->pdo->prepare('
						UPDATE hhm_users 
						SET UserStatus = "not done", 
						UserLevel = "admin", 
						HouseholdID = :hhID
						WHERE UserID = :userID
						');
				if($updateUserInfo->execute(
						array('hhID'=>$hhID,'userID'=>$this->userID))){
							$_SESSION['user']['householdid'] = $hhID;
							$_SESSION['user']['userlevel'] = 'admin';
					header('Location: overview.php');
				}
			}
		}
		return $results;
	}
	
	/***
	 * function that allows a user to join a household, if the household
	 * exists, the user is set to a pending join status awaiting access from admin
	 * 
	 * @param array $results
	 * @return string, error or success message
	 */
	function joinHousehold($results){
		
		$hhName = $results['joinHhName'];
		
		if(Validate::isEmpty($hhName)){
			$results['resultJoin'] = 'Enter a household name!';
		} else if(Validate::isTokenAvailable($hhName,'HouseholdID'
				,'HouseholdName','hhm_households' ,$this->pdo)){
			$results['resultJoin'] = 'Household does not exists...';
		} else {
			$setPendingStatus =
			$this->pdo->prepare('
					UPDATE hhm_users 
					SET HouseholdID = :hhID, UserStatus = "pending"
					WHERE UserID = :userID
					');
			if($setPendingStatus->execute(array('hhID'=>$_SESSION['tokenID'],
					'userID'=>$this->userID))){
				header('Location: overview.php');
			}
		}
		return $results;
	}
	
	/***
	 * function that allows a user to cancel a pending join household status
	 */
	function cancelPending(){
		$cancelPending = 
		$this->pdo->prepare('
				UPDATE hhm_users 
				SET HouseholdID = null,
					UserStatus = "not in"
				WHERE UserID = :userID
				');
		if($cancelPending->execute(array('userID'=>$this->userID))){
			header('Location: overview.php');
		}
	}

	
	/********************flagged for removal
	/**
	 * Registers the user
	 * @param unknown $firstName
	 * @param unknown $lastName
	 * @param unknown $username
	 * @param unknown $email
	 * @param unknown $pw
	 * @return string
	 *
	function register($username,$email,$pw){
		$config = new Validate();
		$pdo = $config->pdoConnection();
		unset($config);
		$result ='';
		//hashes password
		$pw = password_hash($pw, PASSWORD_DEFAULT);
		//sets the household id to the id save in the session by the validate code method
		$householdID = $_SESSION['hhID'];
		//prepare register user in users table
		$registerUser =
		$pdo->prepare('INSERT INTO sp16_users
				VALUES (NULL,
				:lastname,
				:firstname,
				:username,
				:pass,
				:level,
				NULL,
				:status,
				:householdid
				)');
		//if successful registering user
		if($registerUser->execute(array(':lastname'=> $this->lastName,
				':firstname'=> $this->firstName,
				':username'=> $username,
				':pass'=> $pw,
				':level'=> 'member',
				':status'=> 'not done',
				':householdid'=>$householdID))){
				//prepares the statment to insert contact info into the respective table
		$userID = $pdo->lastInsertId();
		$insertEmail=
		$pdo->prepare('INSERT INTO sp16_user_contact_options
						VALUES(NULL,
						"email",
						:em,
						:userid
						)');
		//if successful insert of contact info
		if($insertEmail->execute(array(':em'=>$email,':userid'=>$userID))){
			//prepares to update the user preferred contact
			//to the just inserted email id
			$prefID = $pdo->lastInsertId();
			$updatePrefContactID=
			$pdo->prepare('UPDATE sp16_users
							SET PreferredNotID = :prefid
							WHERE UserID = :userid
							');
			//if successful
			if ($updatePrefContactID->execute(
					array(':prefid'=>$prefID,':userid'=>$userID))){
						//prepares to set the hashed code to invalid
						$code = $_SESSION['code'];
						$setCodeToInvalid =
						$pdo->prepare('UPDATE sp16_codes
								SET CodeValid = 0 WHERE CodeNum = :code');
						//if the code was successfully set to invalid
						if($setCodeToInvalid->execute(array(':code'=> $code))){
							$to  = $email;

							// subject
							$subject = 'HH Registration complete!';

							// message
							$message = '
								<html>
								<head>
								  <title>You have been successfully registered!</title>
								</head>
								<body>
								  <h3>
									Congratulations '. $firstName . ' ' . $lastName .'!
									</h3>
								  <p>You can now log in using your info,
								  		just click the link below</p>
								  <a href="http://neoazareth.com/itc285/project/index.php"
								  		target="_blank">HH log in</a>
								</body>
								</html>
							';

							// To send HTML mail, the Content-type header must be set
							$headers  = 'MIME-Version: 1.0' . "\r\n";
							$headers .= 'Content-type: text/html; charset=iso-8859-1'
									. "\r\n";

									//send an email to the user and redirects the user
									//to the login page
									if(mail($to, $subject, $message,$headers)){
										$result = '<script>
									alert("Registration complete!");
									window.location.href="index.php";
									</script>';
									} else {//the else are errors
										$result = 'Mail confirmation failed';
									}
						} else {
							$result = 'Code could not be deleted';
						}

			} else {
				$result = 'Update preferred notification failed';
			}
		} else {
			$result = 'Email registration failed';
		}
		} else {
			$result = 'Registration has failed';
		}
		return $result;
	}*/
	
	/***
	 * function that allows a user to leave the current Household
	 * 
	 * @return string, error or success message
	 */
	function leaveHousehold(){
		$leaveHh =
		$this->pdo->prepare('
				UPDATE hhm_users 
				SET HouseholdID = NULL, 
				UserStatus = "not in" 
				WHERE UserID = :id
				');
		if($leaveHh->execute(array('id'=>$this->userID))){
		    $deleteBills =
		    $this->pdo->prepare(
		        'DELETE FROM hhm_bills WHERE UserID = :userID');
		    
		    if ($deleteBills->execute(array(':userID'=>$this->userID))) {
		        header('Location: overview.php');
		    } else {
		        $error = 'Could not delete bills... try again later.';
		    }
		} else {
			$error = 'Could not complete... try again later.';
		}
		$result = '<script>
			alert("'.$error.'");
			window.location.href="overview.php";
			</script>';
		return $result;
	}
}