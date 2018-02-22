<?php
/**
 * Admin.php is a class that extends member objects to allow adminstration
 * priviliges such as remove or allow users into a household, update the household rent, or reset
 * the status of users
 *
 * @author Israel Santiago
 * @package HHManageWebApp
 * @see Member Class and User Class
 * @version 3.0
 *
 */
class Admin extends Member{
	
	/**
	 * multiple php constructor
	 */
	function __construct(){
		
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
	 * Constructor with 2 parameters
	 * 
	 * @see Member::__construct2()
	 */
	function __construct2($userID, $pdo){
		parent::__construct2($userID, $pdo);
	}
	
	/**
	 * function that validates and removes users from a given household
	 * @param array $postArray contains the ids of users to be removed
	 * @param object $household, the household from wich the users need to be removed
	 * @return string[]
	 */
	function removeUsers($postArray,$household){
		$result = '';
		$householdUserIDs = $household->listOfUserIDs();
		
		$validUserIDs = [];
		$usersRemoved = 0;
		
		foreach ($postArray as $key => $id){
			if(strpos($key, 'user')>= 0 && in_array($id, $householdUserIDs)) {
				array_push($validUserIDs, $id);
			}
		}
		
		if(!(empty($validUserIDs))){
			$connection = new Connection();
			$pdo = $connection->getConnection();
			unset($connection);
			
			foreach ($validUserIDs as $id){
				$removeUser = 
				$pdo->prepare('UPDATE hhm_users 
						SET UserStatus = "not in", HouseholdID = null 
						WHERE UserID = :id');
				$removeUser->execute(array('id'=>$id));
				$usersRemoved++;
			}
			if ($usersRemoved == 1){
				$result = $usersRemoved . ' user removed';
			} else {
				$result = $usersRemoved . ' users were removed';
			}
			
			$household->getHouseholdMembers($pdo);
		} else {
			$result = 'There is no user checked for removal...';
		}
		$results = array('result'=>$result);
		return $results;
	}
	
	/**
	 * function that updates the rent of a given household
	 * @param int $amount
	 * @param object $household
	 * @return string
	 */
	function updateRent($amount,$household){
		if(Validate::isEmpty($amount)){
			$result = 'Enter the new rent amount!';
		} else if (!Validate::isValidAmount($amount)){
			$result = 'Invalid rent amount, number must be greater than 0 or less 10,001';	
		} else if($household->getHhRent() == $amount){
			$result = 'Amount is the same...';
		} else {
			$connection = new Connection();
			$pdo = $connection->getConnection();
			unset($connection);
			$changeRent = 
			$pdo->prepare('UPDATE hhm_households SET HhRentAmount = :amount
					WHERE HouseholdID = :id');
			
			if($changeRent->execute(array('amount'=> $amount, 'id'=>$household->getHhID()))){
				$result = 'Rent has been updated!';
			} else {
				$result = 'Something went wrong...';
			}
		}
		$result = '<script>
				alert("'. $result .'");
				window.location.href="admin.php";
				</script>';
		return $result;
	}
	
	/**
	 * resets the user status of a given user id
	 * @param object $household, used to verify that the user belongs to the household
	 * @param int $resetID, user id to be reset
	 * @return string|string,
	 */
	function resetUserStatus($household,$resetID){
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
			
		if ($isValidID || $isValidStatus){
			$tempUser = new Member($resetID);
			$result = $tempUser->changeUserStatus('admin.php', 'not done');
		} else {
			$result = '<script>
				alert("Invalid data!");
				window.location.href="admin.php";
				</script>';
		}
		return $result;
	}
	
	/***
	 * function that deletes a household from the database
	 * it removes all users first
	 * @param int $hhID, the household id to be removed
	 * @return string
	 */
	function deleteHousehold($hhID){
		$result = '<script>
					alert("Something went wrong...");
					</script>';
		
		$removeUsers = 
		$this->pdo->prepare('
				UPDATE hhm_users 
				SET UserLevel = "member", 
				UserStatus = "not in", 
				HouseholdID = NULL 
				WHERE HouseholdID =:hhID
				');
		if($removeUsers->execute(array('hhID'=>$hhID))){
			$deleteHh = 
			$this->pdo->prepare('DELETE FROM hhm_households 
					WHERE HouseholdID =:hhID');
			if($deleteHh->execute(array('hhID'=>$hhID))){
				$_SESSION['user']['householdid'] = null;
				$_SESSION['user']['userlevel'] = 'member';
				header('Location: overview.php');
			} else {
				return $result;
			}
		} else {
			return $result;
		}
	}
}
