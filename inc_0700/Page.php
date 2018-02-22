<?php
/**
 * Page.php is a class with public static methods that build the pages
 * forms, navbars and tables of the application. This class may seem unnecessary but it was build
 * as an excercise...
 * 
 * @author Israel Santiago
 * @package projectv2
 * @link neoazareth@gmail.com
 * @version 2.0
 */
class Page{
	
    /**
     * form that resets the users password
     * @param string $code, a code that is expected to allows password reset.
     * the code is consumed after update and is obtain from a link to an email belonging to
     * the User
     * @return string, html reset password form
     */
	public static function resetPwForm($code){
		$form = '
				<div class="col-lg-5">
				<h3 class="text-center">Reset Password</h3>
				<form action='.$_SERVER["PHP_SELF"].' method="post" class="form-horizontal">
		
				<div class="form-group">
			    <label for="inputNew" class="col-lg-4 control-label">New password</label>
			    <div class="col-lg-8">
			    <input type="password" name="newpw" class="form-control" id="inputNew">
				</div>
				</div>
		
				<div class="form-group">
			    <label for="inputCon" class="col-lg-4 control-label">
						Confirm new password</label>
			    <div class="col-lg-8">
			    <input type="password" name="confirmnewpw" class="form-control"
						id="inputCon">
				</div>
				</div>
				<input type="hidden" name="upsc" value="'.$code.'">
				<div class="form-group">
			    <div class="col-lg-4 col-lg-offset-8">
			    <button type="submit" name="resetpass" value="resetpass"
						class="btn btn-success">Reset password</button>
			    </div>
			    </div>
				</form>
				</div>
				';
		return $form;
	}
	
	/***
	 * outputs a html form with fields that allows a user to leave or delete a household
	 * 
	 * @param object $user, Member or Admin object
	 * @param object $household, Household object
	 * @return string
	 */
	public static function leaveOrDeleteForm($user,$household){
		if($user->getUserLevel()=='admin'){
			$action = 'Delete ';
		} else {
			$action = 'Leave ';
		}
		
		if($household->getHhID()>0){
		    $body = '
				<div class="col-lg-5 col-lg-offset-2">
				<h3 class="text-center">Household settings</h3>
				<div class="row">
				<form action='.$_SERVER["PHP_SELF"].' method="post" class="form-inline">
					<label>'.$action. $household->getHhName().' household!? </label>
			      <button type="submit" name="'.trim(strtolower($action))
			      .'Hh" class="btn btn-warning btn-xs">
				Yes!</button>
				</form>
			    </div>
				</div>
				';
		} else {
		    $body = '';
		}
		
		return $body;
	}
	
	/***
	 * Small form used to notify an user that their status is currently pending to join
	 * a household. It also provides an option to cancel such status and return to join or
	 * create form options
	 * 
	 * @param object $user, Member object
	 * @return string
	 */
	public static function cancelPendingOverview($user){
		$body = '
				<div class="row">
			<div class="col-lg-6 col-lg-offset-3">
							<h1 class="text-center">Overview page.</h1>
			</div>
		   	</div>
				
				<div class="row">
			<div class="col-lg-10 col-lg-offset-1">
				<h2>Hello <em class="text-primary">'. $user->getUserFullName().'</em>!</h2>
			</div>
			</div>
						
			<div class="row">
				<div class="col-lg-10 col-lg-offset-1">
					<h4> You are currently set to join a household.</h4>
				</div>
				<div class="col-lg-5 col-lg-offset-1">
				<form action='.$_SERVER["PHP_SELF"].' method="post" class="form-horizontal">
				
				<label>Cancel your request?</label>
			      <button type="submit" name="pending" class="btn btn-warning btn-xs">
				Cancel</button>
				</form>
				</div>
			</div>
				';
		return $body;
	}
	
	/***
	 * displays a view of the options to create and join a household feature as a form
	 * 
	 * @param object $user, Member object
	 * @param array $results, array used to keep the users inputs in case of errors
	 * @return string
	 */
	public static function createOrJoinOverview($user,$results){
		
		$body ='
				<div class="row">
			<div class="col-lg-6 col-lg-offset-3">
							<h1 class="text-center">Overview page.</h1>
			</div>
		   	</div>
				
				<div class="row">
			<div class="col-lg-10 col-lg-offset-1">
				<h2>Hello <em class="text-primary">'. $user->getUserFullName().'</em>!</h2>
			</div>
			</div>
						
			<div class="row">
			<div class="col-lg-10 col-lg-offset-1">
			<h4> You don\'t currently belong to a household...</h4>			
			</div>
			</div>
			<div class="row">
				<div class="col-lg-5">
				<h3 class="text-center">Join a Household</h3>
				<form action='.$_SERVER["PHP_SELF"].' method="post" class="form-horizontal">
				<div class="form-group">
			    <label for="inputJoin" class="col-lg-4 control-label"> Existing Household</label>
			    <div class="col-lg-8">
			    <input type="text" name="joinHhName" class="form-control" id="inputJoin" 
						value="'.$results['joinHhName'].'">
				</div>
				</div>
	
				<div class="form-group">
			    <div class="col-lg-3 col-lg-offset-9">
			    <button type="submit" name="joinHh" value="joinHh"
						class="btn btn-success">Search & Join</button>
			    </div>
			    </div>
				<h4 class="text-danger">'. $results['resultJoin'] .'</h4>
				</form>
				</div>
						
				<div class="col-lg-5 col-lg-offset-2">
				<h3 class="text-center">Create a Household</h3>
				<form action='.$_SERVER["PHP_SELF"].' method="post" class="form-horizontal">
				<div class="form-group">
			    <label for="inputCreate" class="col-lg-4 control-label">New Household</label>
			    <div class="col-lg-8">
			    <input type="text" name="createHhName" class="form-control" id="inputCreate"
						value="'.$results['createHhName'].'">
				</div>
				</div>
	
				<div class="form-group">
			    <label for="inputRent" class="col-lg-4 control-label">Household Rent</label>
			    <div class="col-lg-8">
			    <input type="text" name="createHhRent" class="form-control" id="inputRent"
						value="'.$results['createHhRent'].'">
				</div>
				</div>
	
				<div class="form-group">
			    <div class="col-lg-3 col-lg-offset-9">
			    <button type="submit" name="createHh" value="updatepass"
						class="btn btn-success">Create</button>
			    </div>
			    </div>
				<h4 class="text-danger">'. $results['resultCreate'] .'</h4>
				</form>
				</div>
						
						
			</div>
				';
		return $body;
		
	}
	
	/***
	 * outputs a default overview with information of the household, user bills and other
	 * users status
	 * 
	 * @param object $user
	 * @param object $household
	 * @return string
	 */
	public static function defaultOverview($user,$household){
		$body = '
			 		<div class="row">
			<div class="col-lg-6 col-lg-offset-3">
							<h1 class="text-center">Overview page.</h1>
			</div>
		   	</div>

			<!--2 divs open and close -->

			<div class="row">
			<div class="col-lg-10 col-lg-offset-1">
				<h2>Hello <em class="text-primary">'. $user->getUserFullName().'</em>!</h2>
			</div>
			</div>

			<!--2 divs open and close -->

			<div class="row">
			<div class="col-lg-10 col-lg-offset-1">
			<h4> Welcome to <em class="text-success">'
						. $household->getHhName(). '</em> overview page.</h4>
			<h4> Rent as of '
								. date('F jS\, Y') . ': <em class="text-danger"> $'
										. $household->getHhRent().'</em></h4>
			</div>
			</div>
			
			<!--2 divs open and close -->
												
			<div class="row">
			<div class="col-lg-5 col-lg-offset-1">
			<h2 class="text-center">My bills</h2>'.
			
			$user->displayUserBills().
			
			'</div>
			<div class="col-lg-5">
			<h2 class="text-center">Member\'s Status</h2>'.

			$household->showUsersStatus($user).

			'</div>
		</div>
		<div class="row">
			<div class="col-lg-10 col-lg-offset-1">'.

										//echo $household->expensesDistribution($currentMonth);

			'</div>
			<div class="col-lg-10 col-lg-offset-1">'.

										//echo $household->expensesDistribution($pastMonth);

			'<div>
		</div>
	</div> <!--closes container -->
	</body>
	</html>
				';
		
		return $body;
	}
	
	/***
	 * Displays the first form for user login
	 * @param $error, string that is passed to inform the user about errors
	 */
	public static function logInForm($error){
		
		if(isset($_GET['pwRF']) && $_GET['pwRF'] == 1) {
			$body = '<legend>Enter your email address to reset your password:</legend>
						<div class="form-group">
				      		<label for="inputDefault" class="col-lg-2 control-label">
						Email</label>
				      		<div class="col-lg-10">
				        		<input type="email" name="passRe" class="form-control"
						id="inputDefault" placeholder="Email">
				      		</div>
				    	</div>
						<div class="form-group">
						<div class="col-lg-8">
						<label>
							<a href="index.php">Back to login form!</a>
				      	</label>
						</div>
				      		<div class="col-lg-3 col-lg-offset-1">
				        		<button type="submit" name="resetPass" class="btn btn-primary">
						Reset password</button>
				      		</div>
				    	</div>';
		} else {
			$body = '<legend>Enter your credentials</legend>
						<div class="form-group">
				      		<label for="inputDefault" class="col-lg-2 control-label">
						Email</label>
				      		<div class="col-lg-10">
				        		<input type="email" name="em" class="form-control"
						id="inputDefault" placeholder="Email">
				      		</div>
				    	</div>
						<div class="form-group">
				    		<label for="inputPassword" class="col-lg-2 control-label">
						Password</label>
				      		<div class="col-lg-10">
				        		<input type="password" name="pw" class="form-control"
						id="inputPassword" placeholder="Password">
				      		</div>
				    	</div>
						<div class="form-group">
						<div class="col-lg-8">
						<label>
							<a href="register.php">Register</a> or <a href="index.php?pwRF=1">Forgot password?</a>
				      	</label>
						</div>
				      		<div class="col-lg-2 col-lg-offset-2">
				        		<button type="submit" name="login" class="btn btn-primary">
						Log in</button>
				      		</div>
				    	</div>';
		}
 			
	
		$form ='
		<div class="row">
			<div class="col-sm-6 col-sm-offset-3">
				<form action='.$_SERVER["PHP_SELF"].' method="post" class="form-horizontal">
					<fieldset>
						'.$body.'
					</fieldset>
					<h4 class="text-danger">'. $error .'</h4>
				</form>
			</div>
		</div>';
		return $form;
	}
	
	/***
	 * Display head. contains openning tags for an html page
	 * @return string
	 */
	public static function header(){
		$htmlHead = '<!DOCTYPE html>
		<html lang="en">
		<head>
		  <title>BillTrack</title>
		  <meta charset="utf-8">
		  <meta name="viewport" content="width=device-width, initial-scale=1">
		  <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
		  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js">
				</script>
		  <script src="js/bootstrap.min.js"></script>
        <link rel="apple-touch-icon" sizes="180x180" href="/HHManageWebApp/favicons/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/HHManageWebApp/favicons/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/HHManageWebApp/favicons/favicon-16x16.png">
        <link rel="manifest" href="/HHManageWebApp/favicons/manifest.json">
        <link rel="mask-icon" href="/HHManageWebApp/favicons/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="theme-color" content="#ffffff">
		</head>
		<body>
		<header>
			<div class="row">
				<div class="col-lg-12">
					<h1 class="text-center">BILLTRACK</h1>
				</div>
			</div>
		</header>';
	
		return $htmlHead;
	}
	
	/***
	 * Displays a nav based on the user's level
	 * @return string, contains the nav bar as html
	 */
	public static function navBar($user){
		//gets the name of the current page to set the nav bar tab to active
		$currentPage = $_SERVER['PHP_SELF'];
		$currentPage = str_replace('/HHManageWebApp/', '', $currentPage);
		$currentPage = str_replace('.php', '', $currentPage);
	
		$links = [];
		if($user->getUserLevel() === 'admin'){
			//if the user is an admin it adds an admin tab
			$links = array("overview","manage_bills","report","settings","admin","log_out");
		} else if($user->getUserStatus() == 'not in' || $user->getUserStatus() == 'pending'){
			$links = array("overview","settings","log_out");
		} else {//otherwise a member nav is created
			$links = array("overview","manage_bills","report","settings","log_out");
		}
	
		$nav = '
		<div class="row">
			<div class="col-lg-12">
				<nav class="navbar navbar-default">
					<div class="container-fluid">
						<div class="navbar-header">
							<button type="button" class="navbar-toggle"
									data-toggle="collapse"
									data-target="#bs-example-navbar-collapse-1"
									aria-expanded="true">
						        <span class="sr-only">Toggle navigation</span>
						        <span class="icon-bar"></span>
						        <span class="icon-bar"></span>
						      	<span class="icon-bar"></span>
						    </button>
							<a class="navbar-brand" href="#">BillTrack</a>
						</div>
						<div class="navbar-collapse collapse"
							id="bs-example-navbar-collapse-1"
							aria-expanded="false" style="height: 1px;">
					<ul class="nav navbar-nav">';
	
		foreach ($links as $link){
			$active = '';
			if($link == $currentPage){
				$active = ' class="active"';
			}
			$nav .= '<li'. $active .'>
					<a href="'. $link .'.php">'.
						str_replace("_"," ",ucfirst($link)) .'
					</a>
					</li>';
		}
		$nav .= '			</ul>
							</div>
						</div>
					</nav>
				</div>
			</div>';
		return $nav;
	}
	
	/**
	 * displays a form to manage users bills
	 * @param string $array, an array used to store user fields values and feedback
	 * @return string
	 */
	public static function manageBillsForm($results,$user){
		if (isset($_SESSION['editbill'])) {
			$editBillID = $_SESSION['editbill'];
		}
		
		$editMode = false;
		
		$desc = '';
		$amount = '';
	
		if (!(empty($results))) {
			$desc = $results['desc'];
			$amount = $results['amount'];
			$result = $results['result'];
		}
	
		//opens a form tag and adds the table heading
		$table = '<form action='.$_SERVER["PHP_SELF"].' method="post" class="form-inline">
				<table class="table table-striped table-hover">
				<tHead>
				<tr>
				<th>Delete?</th>
				<th>Description</th>
				<th>Amount</th>
				<th>Category</th>
				<th>Date</th>
				<th>Edit?</th>
				</tr>
				</tHead>
				<tBody>
				';
		//the bills are added as rows with a checkbox and an edit link
		$userBills = $user->getBills();
		foreach ($userBills as $obj) {
			$id = $obj->getBillID();
			$table .= '<tr><td>
						<div class="checkbox">
				          <label>
							<input type="checkbox" name="bill'. $id .'" value="'. $id .'">
									delete
				          </label>
				        </div>
						</td>';
			if($id == $editBillID){
				$row = $obj->getBillAsForm();
				$table .= $row;
				$table .= '
				<td>
				<button type="submit" name="update" class="btn btn-success">
				Update Bill</button>
				</td>
				<td>
				<button type="submit" name="cancel" class="btn btn-danger"
				">Cancel</button>
				</td>';
				$editMode = true;
			} else {
				
				$row = str_replace('<tr>', '', $obj->getBillAsRow());
				$row = str_replace('</tr>', '', $row);
				$table .= $row;
				$table .= '<td class="text-center">
						<a href="manage_bills.php?editbill='. $id .'" class="btn btn-success btn-xs">
								edit</a>
						</td></tr>';
			}
		}
		//adds a form to add a bill
		if (!$editMode) {
			$table .= '<tr></tr><tr>
					<td></td>
					<td>
					<input type="text" name="desc" value="'. $desc .'"
							class="form-control" id="inputText" placeholder="Description">
				    </td>
					<td>
					<input type="text" name="amount" value="'. $amount .'"
							class="form-control" id="inputText" placeholder="Amount">
				    </td>
					<td>
					<select name="category" class="form-control" id="select">
			        <option value="select">Select one</option>
					<option value="food">Food</option>
					<option value="utility">Utility</option>
					<option value="maintenance">Maintenance</option>
					<option value="other">Other</option>
					</select>
					</td>
					<td><button type="submit" name="add" class="btn btn-primary">
							Add bill</button></td>
					<td>
					</td>
					</tr>';
		}
		//adds a button to delete selected bills
		$table .= '<tr>
				<td>
				<button type="submit" name="delete" class="btn btn-danger btn-sm">
					Delete Selected</button>
			    </td>
				<td></td><td></td><td></td><td></td><td></td>
				</tr>
				</tbody>';
		//closes the table and adds another button to set the user status to done
		$table .= '</table>
				<label>Done adding your bills? </label>
			      <button type="submit" name="status" class="btn btn-warning btn-xs">
				Yes</button>
			    <p class="text-danger">'. $result .'</p>
				</form>';
		return $table;
	}
	
	/**
	 * displays custom report form
	 * @return string
	 */
	public static function reportForm($household){
		$listOfMonths = $household->retrieveListOfMonths();
		$users = $household->listOfUsers();
		$categories = array('food','utility','maintenance','other');
	
		$form = '<form action='.$_SERVER["PHP_SELF"].' method="post" class="form-inline">
				<legend>Custom Reports:</legend>
				<div class="form-group">
				<label for="inputSelUser" class="control-label">By member:</label>
				<select name="user" class="form-control" id="inputSelUser">
				<option value="all">All</option>';
	
		foreach ($users as $user){
			$form .= '
					<option value="'. $user['id'] .'">'. $user['name'] .'</option>
					';
		}
	
		$form .= '</select>
				</div>
				<div class="form-group">
				<label for="inputSelCat" class="control-label">By category:</label>
				<select name="category" class="form-control" id="inputSelUser">
				<option value="all">All</option>';
	
		foreach ($categories as $category){
			$form .= '
					<option value="'. $category .'">'. ucfirst($category) .'</option>
					';
		}
	
		$form .= '</select>
				</div>
				<div class="form-group">
				<label for="inputSelMonth" class="control-label">By month:</label>
				<select name="month" class="form-control" id="inputSelMonth">
				';
	
		foreach ($listOfMonths as $value){
			$form .= '
					<option value="'. $value .'">'. $value .'</option>
					';
		}
	
		$form .= '</select>
				</div>
				<button type="submit" name="get_custom" class="btn btn-primary">GO</button>
				</form>';
		return $form;
	}
	
	/**
	 * displays the update password form
	 * @param string $result, used to display feedback
	 * @return string, html form
	 */
	public static function updatePasswordForm($result){
		$form = '
				<div class="col-lg-5">
				<h3 class="text-center">Update password</h3>
				<form action='.$_SERVER["PHP_SELF"].' method="post" class="form-horizontal">
				<div class="form-group">
			    <label for="inputOld" class="col-lg-4 control-label">Old password</label>
			    <div class="col-lg-8">
			    <input type="password" name="oldpw" class="form-control" id="inputOld">
				</div>
				</div>
	
				<div class="form-group">
			    <label for="inputNew" class="col-lg-4 control-label">New password</label>
			    <div class="col-lg-8">
			    <input type="password" name="newpw" class="form-control" id="inputNew">
				</div>
				</div>
	
				<div class="form-group">
			    <label for="inputCon" class="col-lg-4 control-label">
						Confirm new password</label>
			    <div class="col-lg-8">
			    <input type="password" name="confirmnewpw" class="form-control"
						id="inputCon">
				</div>
				</div>
	
				<div class="form-group">
			    <div class="col-lg-3 col-lg-offset-9">
			    <button type="submit" name="updatepass" value="updatepass"
						class="btn btn-success">Update</button>
			    </div>
			    </div>
				<h4 class="text-danger">'. $result .'</h4>
				</form>
				</div>
				';
		return $form;
	}
	
	/**
	 * Displays the member management form of the admin page
	 * @param array $array, an array that keeps the user's field in case of an Error
	 */
	public static function displayMemberManagementForm($results,$household){
		$first = $results['first'];
		$last = $results['last'];
		$email = $results['email'];
		$result = $results['result'];
	
	
		$table = '
				<h3>Manage members:</h3>
				<form action='.$_SERVER["PHP_SELF"].' method="post" class="form-inline">
				<table class="table table-striped table-hover">
				<tHead>
				<tr>
				<th>Remove/reject?</th><th>Name</th><th>Current Status</th><th>Edit/allow?</th>
				</tr>
				</tHead>
				<tBody>
				';
		//call the household members to display their info
		foreach ($household->getMembers() as $member){
			$id = $member->getUserID();
			
			if($id == $household->getHouseholdAdmin()->getUserID()){
				$checkbox = 'Can\'t remove Admin';
			} else {
				$checkbox = '<input type="checkbox" name="user'. $id .'"
								value="'. $id .'"> remove';
			}
			
			if($member->getUserStatus()== 'done'){
				$button = '<a href="admin.php?reset='. $id .'"
							class="btn btn-warning btn-xs">reset</a>';
			} else if($member->getUserStatus()=='pending'){
				$button = '<a href="admin.php?reset='. $id .'"
							class="btn btn-success btn-xs">allow</a>';
				$checkbox = '<input type="checkbox" name="user'. $id .'"
								value="'. $id .'"> reject';
			} else {
				$button = 'can\'t reset';
			}
			
			//row containing a checkbox, name, status and editstatus link
			$table .= '<tr>
					<td>
					<div class="checkbox">
			          <label>
						'.$checkbox.'
			          </label>
			        </div>
					</td>
					<td>'. $member->getUserFullName() .'</td>
					<td>'. $member->getUserStatus() .'</td>
					<td>
					'.$button.'
					</tr>';
		}
		//row containing delete button
		$table .= '<tr>
				<td>
				<button type="submit" name="remove"
				class="btn btn-danger btn-sm">Remove Selected</button>
				</td>
				</tr></tBody></table><h4 class="text-danger">'. $result .'</h4></form>';
		//adds the send registration link form to register a new member
		/*$table .= '
				<h3>Send a registration link to a new household member:</h3>
				<div class="form-group">
			    <label for="inputFN" class="control-label">First Name</label>
			    <input type="text" name="first" value="'. $first .'"
			    		class="form-control" id="inputFN">
				</div>
	
			    <div class="form-group">
			    <label for="inputLN" class="control-label">Last Name</label>
			    <input type="text" name="last" value="'. $last .'" class="form-control"
			    		id="inputLN">
				</div>
	
			    <div class="form-group">
			    <label for="inputEmail" class="control-label">Email</label>
			    <input type="email" name="email" value="'. $email .'" class="form-control"
			    		id="inputEmail">
				</div>
	
			    <button type="submit" name="add" class="btn btn-primary">Send</button>
				<h4 class="text-danger">'. $result .'</h4>
				';
		*/
		return $table;
	}
	
	/**
	 * displays the change rent amount form
	 * @return string, the form.
	 */
	public static function displayChangeRentAmountForm($household){
		$form = '
				<form action='.$_SERVER["PHP_SELF"].' method="post" class="form-inline">
				<h3>Change household rent:</h3>
				<div class="form-group">
			    <label for="inputRent" class="control-label">Current rent amount:</label>
			    <input type="text" name="rent" value="'. $household->getHhRent() .
				    '" class="form-control" id="inputRent">
				</div>
			    <button type="submit" name="change" class="btn btn-success">Change</button>
			    </form>
				';
		return $form;
	}
	
	/**
	 * helper function that builds a list of 12 months prior to the current month
	 * it is used to provide a dropdown list for the user report form
	 * 
	 */
	public static function listOfMonthsPriorToCurrent(){
	
		$year = date('Y');
		$month = date('m');
		$list = [];
		array_push($list, $year.'-'.$month);
	
		for($x = 1;$x <12;$x++){
			if((int)($month) == 1){
				$year --;
				$month = 12;
			} else if ((int)($month) > 1 && (int)($month) <= 10){
				$month = '0' . (string)((int)($month)-1);
			} else {
				$month = (int)$month - 1;
			}
			array_push($list, $year.'-'.$month);
		}
		return $list;
	}
	
	/**
	* displays a form for new member registration
	* @param unknown $array, an array containg user field values in case of error
	* @return string, the form.
	*/
	public static function displayNewMemberRegistrationForm($results){
	    $firstName = $results['first'];
	    $lastName = $results['last'];
	    $userName = $results['username'];
	    $email = $results['email'];
	    $result = $results['result'];
	    
	    $form = '<h3 class="text-warning">Enter your info. All fields are required!</h3>
				<form action='.$_SERVER["PHP_SELF"].' method="post" class="form-horizontal">
				    
				<div class="form-group">
			    <label for="inputFN" class="col-lg-4 control-label">First name</label>
			    <div class="col-lg-8">
			    <input type="text" name="first" value="'. $firstName .'"
			    		class="form-control" id="inputFN" placeholder="Enter your first name">
				</div>
				</div>
			        
			    <div class="form-group">
			    <label for="inputLN" class="col-lg-4 control-label">Last name</label>
			    <div class="col-lg-8">
			    <input type="text" name="last" value="'. $lastName .'"
			    		class="form-control" id="inputFN" placeholder="Enter your last name">
				</div>
				</div>
			        
			    <div class="form-group">
			    <label for="inputEM" class="col-lg-4 control-label">Email</label>
			    <div class="col-lg-8">
			    <input type="email" name="email" value="'. $email .'"
			    		class="form-control" id="inputEM"
			    		placeholder="Enter a valid email address">
				</div>
				</div>
			        
			    <div class="form-group">
			    <label for="inputPW" class="col-lg-4 control-label">Password</label>
			    <div class="col-lg-8">
			    <input type="password" name="pw" class="form-control" id="inputPW"
			    		placeholder="Enter a password">
				</div>
				</div>
			        
			    <div class="form-group">
			    <label for="inputCPW" class="col-lg-4 control-label">Confirm password</label>
			    <div class="col-lg-8">
			    <input type="password" name="confirmpw" class="form-control" id="inputCPW"
			    		placeholder="Confirm password">
				</div>
				</div>
			        
 			    <div class="form-group">
                <div class="col-lg-7">
                <label>
				    <a href="index.php">Back to login page?</a>
				</label>
                </div>
			    <div class="col-lg-3 col-lg-offset-2">
			    <button type="submit" name="register" value="register"
			    		class="btn btn-success">Register</button>
			    </div>
			    </div>
			    </form>
				<h4 class="text-danger">'. $result .'</h4>
				';
	    return $form;
	}
}