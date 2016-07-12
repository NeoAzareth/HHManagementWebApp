<?php
//holds page forms and

class Page{
	
	
	/***
	 * Displays the first form for user login
	 * @param $error, a string that is passed to inform the user about errors
	 */
	public static function logInForm($error){
	
		$form ='
		<div class="row">
			<div class="col-sm-6 col-sm-offset-3">
				<form action='.$_SERVER["PHP_SELF"].' method="post" class="form-horizontal">
					<fieldset>
						<legend>Enter your credentials</legend>
						<div class="form-group">
				      		<label for="inputDefault" class="col-lg-2 control-label">
						Username</label>
				      		<div class="col-lg-10">
				        		<input type="text" name="un" class="form-control"
						id="inputDefault" placeholder="Username">
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
				      		<div class="col-lg-2 col-lg-offset-10">
				        		<button type="submit" name="login" class="btn btn-primary">
						Log in</button>
				      		</div>
				    	</div>
					</fieldset>
					<h4 class="text-danger">'. $error .'</h4>
				</form>
			</div>
		</div>';
		return $form;
	}
	
	/***
	 * Display head contains openning tags for an html page
	 * @return string
	 */
	public static function header(){
		$htmlHead = '<!DOCTYPE html>
		<html lang="en">
		<head>
		  <title>Household Management App</title>
		  <meta charset="utf-8">
		  <meta name="viewport" content="width=device-width, initial-scale=1">
		  <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
		  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js">
				</script>
		  <script src="js/bootstrap.min.js"></script>
		</head>
		<body>
		<header>
			<div class="row">
				<div class="col-lg-12">
					<h1 class="text-center">HOUSEHOLD MANAGEMENT WEB APP</h1>
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
		$currentPage = str_replace('', '', $currentPage);
		$currentPage = str_replace('.php', '', $currentPage);
	
		$links = [];
		if($user->getUserLevel() === 'admin'){
			//if the user is an admin it adds an admin tab
			$links = array("overview","manage_bills","report","settings","admin","log_out");
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
							<a class="navbar-brand" href="#">HhManage</a>
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
		$listOfMonths = Page::listOfMonthsPriorToCurrent();
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
	 * displays the notification settings form
	 * @param unknown $array
	 * @todo improve
	 */
	public static function notificationSettingsForm($array,$user) {
		//if the editcontact variable is active in the session
		//the form creates an update contact field instead of the add bill form
		if (isset($_SESSION['editcontact'])) {
			$editID = $_SESSION['editcontact'];
		}
	
		$validID = false;
	
		$result = $array['result'];
		$newoption = $array['newoption'];
	
		$contactOptions = $user->getContactOptions();
		$form = '
				<div class="col-lg-5 col-lg-offset-2">
				<h3 class="text-center">Notification settings</h3>
				<form action='.$_SERVER["PHP_SELF"].' method="post" class="form-inline">
				<table class="table table-striped table-hover">
				<tHead>
				<tr>
				<th>Type</th><th>Details</th><th></th><th></th>
				</tr>
				</tHead>
				<tBody>';
		foreach ($contactOptions as $option) {
			$id = $option['ContactOptionID'];
			$edit = '<a href="settings.php?editcontact='. $id .'"
					class="btn btn-success btn-xs">Edit</a>';
			$delete = '<a href="settings.php?delete='.$id.'"
					class="btn btn-danger btn-xs">Delete</a>';
			$details = $option['ContactOpDesc'];
			$type = $option['ContactOpType'];
				
				
			if ($id == $user->getPrefContactID()) {
				$edit = '<label class="text-warning">Set for reminders</label>';
				$delete = '';
			}
			if ($id == $editID) {
				if ($type == 'phone') {
					$details = Validate::cleanseMobilePhone($details);
				}
				$validID = true;
				$form .= '<tr>
				<td></td>
				<td>
				<input type="text" name="updateop" value="'. $details .'"
						class="form-control input-sm" id="inputUpOp">
				</td>
				<td>
				<button type="submit" name="update" class="btn btn-success btn-sm">
						Update</button>
				</td>
				<td>
				<button type="submit" name="cancel" class="btn btn-danger btn-sm">
						Cancel</button>
				</td>
				</tr>
				';
			} else {
				if ($type == 'phone') {
					$details = Validate::formatMobileNum($details);
				}
				$form .= '<tr>
					<td>'. $type .'</td>
					<td>'. $details .'</td>
					<td>'. $edit .'</td>
					<td>'. $delete .'</td>
					</tr>';
			}
		}
		if (!($validID)) {
			$form .= '<tr>
				<td></td>
				<td>
				<input type="text" name="newoption" value="'.$newoption.'"
						class="form-control input-sm" placeholder="Email or Mobile Number">
				</td>
				<td>
				<button type="submit" name="addoption" class="btn btn-primary btn-sm">
						Add</button>
				</td>
				<td></td>
				</tr>
				</tBody>
				</table>
				<div class="form-group text-center">
				<label for"select" class="control-label">Set reminders to: </label>
				<select name="primary" class="form-control" id="select">';
			foreach ($contactOptions as $option) {
				$selected = '';
				$desc = $option['ContactOpDesc'];
				if ($option['ContactOpType'] == 'phone') {
					$desc = Validate::formatMobileNum($desc);
				}
				if ($option['ContactOptionID'] == $user->getPrefContactID()) {
					$selected = ' selected';
				}
				$form.= '
						<option value="'.$option['ContactOptionID'].'"'.$selected.'>
								'.$desc.'</option>
						';
			}
			$form .='</select>
				</div>
				<button type="submit" name="setprimary" class="btn btn-primary">Set</button>
				</form>
				<h4 class="text-danger">'. $result .'</h4>
				</div>';
		} else {
			$form .= '</tBody>
				</table>
				</form>
				<h4 class="text-danger">'. $result .'</h4>
				</div>';
		}
	
		return $form;
	}
	
	/**
	 * Displays the member management form of the admin page
	 * @param unknown $array, an array that keeps the user's field in case of an Error
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
				<th>Delete?</th><th>Name</th><th>Current Status</th><th>Edit Status?</th>
				</tr>
				</tHead>
				<tBody>
				';
		//call the household members to display their info
		foreach ($household->getMembers() as $member){
			$id = $member->getUserID();
			//row containing a checkbox, name, status and editstatus link
			$table .= '<tr>
					<td>
					<div class="checkbox">
			          <label>
						<input type="checkbox" name="user'. $id .'"
								value="'. $id .'"> delete
			          </label>
			        </div>
					</td>
					<td>'. $member->getUserFullName() .'</td>
					<td>'. $member->getUserStatus() .'</td>
					<td>
					<a href="admin.php?reset='. $id .'"
							class="btn btn-warning btn-xs">reset</a>
					</tr>';
		}
		//row containing delete button
		$table .= '<tr>
				<td>
				<button type="submit" name="delete"
				class="btn btn-danger btn-sm">Delete Selected</button>
				</td>
				</tr>';
		//adds the send registration link form to register a new member
		$table .= '</tBody></table>
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
				</form>';
	
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
			    <label for="inputUN" class="col-lg-4 control-label">Username</label>
			    <div class="col-lg-8">
			    <input type="text" name="username" value="'. $userName .'"
			    		class="form-control" id="inputUN" placeholder="Enter a username">
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
			    <div class="col-lg-3 col-lg-offset-9">
			    <button type="submit" name="register" value="register"
			    		class="btn btn-success">Register</button>
			    </div>
			    </div>
			    </form>
				<h4 class="text-danger">'. $result .'</h4>
				';
		return $form;
	}
	
	/**
	 * builds a list of 12 months prior to the current month
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
}