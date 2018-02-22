<?php
/**
 * User class is the most basic user for the application
 * it allows login, registration and retrival of password
 * all methods are static
 * 
 * @author Israel Santiago
 * @version 1.0
 * @see Child Member Class
 */

class User {
	
	/**
	 * static function that handles login
	 * @param string $email 
	 * @param string $userPW
	 * @return string
	 */
	public static function processLogin($email, $userPW) {
		//string that handles errors
		$error = '';
		
		//connection object
		$connection = new Connection();
		$pdo = $connection->getConnection();
		unset($connection);
		
		if (Validate::isEmpty($email,$userPW)) {
			$error = "Error! All fields are required.";
		} else {
			$getPassQuery =
			$pdo->prepare('SELECT UserPW FROM hhm_users 
					WHERE Email =:email');
			
			if($getPassQuery->execute(array('email'=>"$email"))){
				$data = $getPassQuery->fetch(PDO::FETCH_ASSOC);
				
				if(password_verify($userPW, $data["UserPW"])) {
					unset($data);
					$getUserInfo =
					$pdo->prepare('
							SELECT FirstName, UserID, UserLevel, HouseholdID
							FROM hhm_users WHERE Email =:email');
					
					$getUserInfo->execute(array('email'=>"$email"));
					$data = $getUserInfo->fetch(PDO::FETCH_ASSOC);
					
					$_SESSION['user']['userid'] = $data['UserID'];
					$_SESSION['user']['userlevel'] = $data['UserLevel'];
					$_SESSION['user']['householdid'] = $data['HouseholdID'];
					
					$name = $data['FirstName'];
					setcookie(name,$name,time()+86400);
					
					header('Location: overview.php');
					
				} else {
					$error = 'Wrong username or/and password!';
				}
			} else {
				$error = 'Wrong username or/and password!';
			}
		}
		return $error;
		
	}
	
	/***
	 * mock method that validates the user registration form.
	 * does not actually allow user to register
	 * 
	 * @param array $data
	 * @return array
	 */
	public static function registerUser($data){
	    $connection = new Connection();
	    $pdo = $connection->getConnection();
	    unset($connection);
	    
	    $firstName = $data['first'];
	    $lastName = $data['last'];
	    $email = $data['email'];
	    $pw = $data['pw'];
	    $conPw = $data['confirmpw'];
	    $result = '';
	    
	    if (Validate::isEmpty($firstName,$lastName,$email,$pw,$conPw)){
	        $result = 'All fields are required!!!';
	    } else if(!Validate::isTokenAvailable($email, 'UserID', 'Email', 'hhm_users', $pdo)){
	        $result = 'Email address in use!';
	    } else if (!(Validate::passwordStrenght($pw))) {
	        $result = 'Password is not safe! <br>
					  must be 8-20 letters long <br>
					  must have at least one lower case letter <br>
					  must have at least one upper case letter <br>
                      must have at least one number';
	    } else if ($pw != $conPw){
	        $result = 'Passwords don\'t match!';
	    } else {
	        $result = "All data is Valid!,<br>
                   this form is only a test...<br>
                    if you wish to register download the android app!";
	    }
	    
	    $data['result'] = $result;
	    return $data;
	}
	
	/***
	 * method that validates a given email address. It checks the database to see if the
	 * email exists there and if so emails the user a link to reset the a password
	 * 
	 * @param string $email
	 * @return string
	 */
	public static function proccessResetPassForm($email){
		$connection = new Connection();
		$pdo = $connection->getConnection();
		unset($connection);
		$error = '';
		$link = 'index.php?pwRF=1';
		
		if(Validate::isEmpty($email)){
			$error = 'Enter the email address associated with your account!';
		} else if(Validate::isTokenAvailable($email, 'UserID', 'Email', 'hhm_users', $pdo)){
			$error = 'Account not found... try with a different email?';
		} else {
			
			$getUserinfo =
			$pdo->prepare('SELECT FirstName, LastName FROM hhm_users 
					WHERE Email =:email');
			
			$getUserinfo->execute(array(':email'=> $email));
			
			$info = $getUserinfo->fetch(PDO::FETCH_ASSOC);
			
			$first = $info['FirstName'];
			
			$code = $info['FirstName'].$info['LastName'] . $email;
			
			//hashes that code
			$hashCode = md5($code);
			
			//prepares the email
			$to  = $email;
			
			// subject
			$subject = 'BillTrack Password reset. Do not reply.';
			
			// message
			$message = '
				<html>
				<head>
				  <title>Reset password link.</title>
				</head>
				<body>
				  <h3>Hello '. $first.'!</h3>
				  <p>You are receiving this email because you have requested to reset your password!</p>
				  <a href="https://neoazareth.com/HHManageWebApp/reset_pw.php?upsc='.
							  $hashCode .'" target="_blank">
				  		Click here to reset your password.</a>
				</body>
				</html>
			';
			
			  // To send HTML mail, the Content-type header must be set
			  $headers  = 'MIME-Version: 1.0' . "\r\n";
			  $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			  
			  $headers .= 'From: <hhmanageadmin@neoazareth.com>';

			  //if sending the email is successful
			  if (mail($to, $subject, $message, $headers)) {
			  	//prepares the hashed code for table insertion along with the
			  	//household id for reference
			  $codeValid = $_SESSION['tokenID'];
			  	$insertCode =
			  	$pdo->prepare('INSERT INTO hhm_codes
					VALUES (NULL, :hash, :codevalid)');
			  	//if the code is successfully inserted the admin is notified
			  	if($insertCode->execute(array(':hash'=> $hashCode,
			  			':codevalid'=> $codeValid))){
			  			$error = 'Success, you should receive an email to reset your password!';
			  			$link = 'index.php';
			  	} else {
			  		$error = 'Failed to insert code...';
			  	}
			  } else {
			  	$error = 'Something went wrong, try again later...';
			  }
		}
		$message = '<script>
			alert("'.$error.'");
			window.location.href="'. $link .'";
			</script>';
		return $message;
	}
	
	/***
	 * validates the reset password form and resets the password on the database
	 * 
	 * @param array $post
	 * @return string
	 */
	public static function resetPassword($post){
		
		$link = 'reset_pw.php?upsc='.$post['upsc'];
		$userID = $_SESSION['tokenID'];
		
		if(Validate::isEmpty($post['newpw'],$post['confirmnewpw'])){
			$error = 'All fields are required!';
		} else if($post['newpw'] != $post['confirmnewpw']) {
			$error = 'Passwords do not match...';
		} else if(!Validate::passwordStrenght($post['newpw'])) {
			$error = 'Password must be 8-20 letters long, \n'.
			' have at least 1 lower case letter, \n'.
			' 1 upper case letter and 1 number';
		} else {
			$connection = new Connection();
			$pdo = $connection->getConnection();
			unset($connection);
			
			$newPass = password_hash($_POST['confirmnewpw'], PASSWORD_DEFAULT);
			$updatePass =
			$pdo->prepare('UPDATE hhm_users SET UserPW =:newpass
					WHERE UserID =:userid');
			if($updatePass->execute(array('newpass'=>$newPass,
					'userid'=>$userID))){
					$error = 'Password updated!';
					$link = 'index.php';
					$deleteCode = 
					$pdo->prepare('DELETE FROM hhm_codes WHERE CodeNum =:code');
					$deleteCode->execute(array('code'=>$post['upsc']));
			} else {
				$error = 'Failed to update pass...';
			}
			
		}
		
		$message = '<script>
			alert("'.$error.'");
			window.location.href="'. $link .'";
			</script>';
		return $message;
	}
}