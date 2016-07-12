<?php
class Admin extends Member{
	
	function __construct($userID, $month, $pdo){
		parent::__construct3($userID, $month, $pdo);	
	}
	
	/**
	 * validates the fields to add a new user and send the link on success
	 * @param unknown $array, post array
	 * @return String[], an array containing a result and the fields in case of failure
	 */
	function sendRegistrationLink($array,$household) {
	
	
		$first = $array['first'];
		$last = $array['last'];
		$email = $array['email'];
		$result = '';
		//checks if any field is missing
		if (empty($array['first']) || empty($array['last'] || empty($array['email']))){
			$result = 'All fiels are required';
		} else if (Validate::isEmailInUse($email)) {//checks if the email is in use
			$result = 'That email is already in use...';
		} else {
	
			//creates a unique code based on the first name, last name and email
			$code = $first . $last . $email;
			//hashes that code
			$hashCode = md5($code);
				
			//prepares the email
			$to  = $email;
				
			// subject
			$subject = 'HH Registration link';
				
			// message
			$message = '
				<html>
				<head>
				  <title>Registration link</title>
				</head>
				<body>
				  <h3>Hello '. $first.'!</h3>
				  <p>Click the link and enter your info to register!</p>
				  <a href="http://neoazareth.com/register.php?reg='.
					  $hashCode .'" target="_blank">
				  		Registration Link</a>
				</body>
				</html>
			';
					  	
					  // To send HTML mail, the Content-type header must be set
					  $headers  = 'MIME-Version: 1.0' . "\r\n";
					  $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					  	
					  // Additional headers
					  //$headers .= 'To: '. $array['first'] .' <'. $email .'>' . "\r\n";
					  //$headers .= 'From: '. $_SESSION['user']->getUserFirstName() . "\r\n";
					  	
					  //if sending the email is successful
					  if (mail($to, $subject, $message, $headers)) {
					  	$config = new Validate();
					  	$pdo = $config->pdoConnection();
					  	//prepares the hashed code for table insertion along with the
					  	//household id for reference
					  	$codeValid = $household->getHhID();
					  	$insertCode =
					  	$pdo->prepare('INSERT INTO sp16_codes
				VALUES (NULL, :hash, :codevalid)');
					  	//if the code is successfully inserted the admin is notified
					  	if($insertCode->execute(array(':hash'=> $hashCode,
					  			':codevalid'=> $codeValid))){
					  			$result = 'Registration link successfully sent!';
					  			$first ='';
					  			$last ='';
					  			$email ='';
					  	} else {
					  		$result = 'Failed to insert code...';
					  	}
					  } else {
					  	$result = 'Something went wrong';
					  }
		}
		$results = array('first'=>$first,'last'=>$last,'email'=>$email,'result'=>$result);
		return $results;
	}
	
	/***
	 * Validate the delete user form,
	 * @param string $array, expects the POST array
	 * @return string[], an array that contains a string with feedback
	 */
	function deleteUsers($array, $household,$month){
		$result='';
		//retrieves a list of user ids that belong to the current household
		$householdUserIDs = $household->listOfUserIDs();
		$validUserID = [];
		$usersDeleted = 0;
	
		//saves the valid ids into the valid user id array
		foreach ($array as $key => $id){
			//checks that the key has the "user" word and belongs to the current household
			if(strpos($key, 'user')>= 0 && in_array($id, $householdUserIDs)){
				array_push($validUserID, $id);
			}
		}
		if(!(empty($validUserID))){
			$config = new Validate();
			$pdo = $config->pdoConnection();
			foreach ($validUserID as $id){
				$deleteUser =
				$pdo->prepare("DELETE FROM sp16_users WHERE UserID = :id");
				$deleteUser->execute(array(':id' => $id));
				$usersDeleted++;
			}
			if ($usersDeleted == 1){
				$result = $usersDeleted . ' user deleted';
			} else {
				$result = $usersDeleted . ' users were deleted';
			}
			$household->getHouseholdMembers($pdo,$month);
		} else {
			$result = 'There is nothing checked for deletion...';
		}
		$results = array('result'=>$result);
		return $results;
	}
	
	/**
	 * update the household rent on DB
	 * @param double $amount, the new rent
	 * @param object $pdo, pdo connection
	 */
	function updateRentAmount($amount,$pdo,$household){
		$id = $household->getHhID();
	
		$changeRent =
		$pdo->prepare('UPDATE sp16_households
				SET HhRentAmount = :amount
				WHERE HouseholdID = :id
				');
		if($changeRent->execute(array(':amount'=> $amount ,':id'=> $id))){
			$result = 'Rent has been updated';
			$household->setHhRent($amount);
		} else {
			$result = 'Something went wrong';
		}
		return $result;
	}
	
	function resetUserStatus($resetID){
		$member = new Member($resetID);
		$result = $member->changeUserStatus('admin.php', 'not done');
		return $result;
	}
}