<?php
/**
 * Household class
 * A class that creates household objects,
 * @author Israel Santiago
 * @package projectv2
 * @link neoazareth@gmail.com
 * @version 2.0
 */
Class Household{
	
	//instance fields
	Private $householdName;
	Private $householdId;
	Private $hhRentAmount;
	Private $members = [];
	
	/**
	 * php multiple constructor
	 */
	function __construct()
	{
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
	 * Constructor with one parameter
	 * @param int $hhID
	 */
	function __construct1($hhID){
		$this->householdId = $hhID;
	}
	
	
	/**
	 * Constructor with 2 parameters
	 * @param string $userName
	 * @param object $pdo, pdo connection
	 */
	function __construct2($hhID, $pdo){
	
		$getHhInfo =
		$pdo->prepare('SELECT HouseholdName, HhRentAmount
							FROM hhm_households
							WHERE HouseholdID =:householdid');
		$getHhInfo->execute(array('householdid'=>"$hhID"));
		$info = $getHhInfo->fetch(PDO::FETCH_ASSOC);
		
		$this->householdId = $hhID;
		$this->householdName = $info['HouseholdName'];
		$this->hhRentAmount = $info['HhRentAmount'];
		
		$this->getHouseholdMembers($pdo);
	}
	
	/**
	 * gets household members from db as User objects
	 * @param object $pdo, pdo connection
	 */
	function getHouseholdMembers($pdo){
		$getMembers =
		$pdo->prepare('SELECT UserID FROM hhm_users WHERE HouseholdID =:householdid');
		$getMembers->execute(array('householdid'=>"$this->householdId"));
		$info = $getMembers->fetchAll(PDO::FETCH_COLUMN);
		$members =[];
		foreach ($info as $key => $id){
			$member = new Member($id, $pdo);
			array_push($members, $member);
		}
	
		$this->members = $members;
	}
	
	/**
	 * getter for Householdid
	 */
	function getHhID(){
		return $this->householdId;
	}
	
	/**
	 * getter for the household name
	 */
	function getHhName(){
		return $this->householdName;
	}
	
	/**
	 * getter for the household rent
	 */
	function getHhRent(){
		return $this->hhRentAmount;
	}
	
	/**
	 * getter for the household members
	 */
	function getMembers(){
		return $this->members;
	}
	
	/**
	 * function that get the household admin
	 * @return object type Admin
	 */
	function getHouseholdAdmin(){
		$members = $this->getMembers();
		foreach ($members as $member) {
			$level = $member->getUserLevel();
			if($level == 'admin'){
				$admin = $member;
				break;
			}
		}
		return $admin;
	}
	
	/**
	 * return a list of the users fullname and the users id
	 */
	function listOfUsers(){
	
		$users = [];
		foreach ($this->members as $obj){
			$user = array('name'=> $obj->getUserFullName(),'id'=>$obj->getUserID());
			array_push($users, $user);
		}
		return $users;
	}
	
	/**
	 * returns the total number of members on the household
	 * @return number
	 */
	function getNumberOfMembers(){
		$number = 0;
		foreach ($this->members as $obj){
			$number++;
		}
		return $number;
	}
	
	/**
	 * function that sums all the users bills and rent for the total expenses
	 * @return int
	 */
	function getMonthTotal(){
		$total = $this->hhRentAmount;
		foreach ($this->members as $member){
			foreach ($member->getBills() as $bill){
				$total += $bill->getBillAmount();
			}
		}
		return $total;
	}
	
	/**
	 * function that returns the household data as an associative array
	 * with information to balance the current month
	 * @return array
	 */
	function monthlyBalancedData(){
		//results to be returned
		$results= [];
		//the total expense of all the month of all users
		$results['total'] = $this->getMonthTotal();
		//the fair amount to be paid by each member
		// total/number of members on household
		$results['fairAmount'] = $results['total']/$this->getNumberOfMembers();
		foreach ($this->members as $member) {
			//retrieves the individual expenses
			$name = $member->getUserFirstName();
			$results['members'][$name] = $member->billsDistribution();
		}
		return $results;
	}
	
	/**
	 * this function returns a string with the balanced data formated as html
	 * for the body of the email
	 * @return string
	 */
	function formatMonthlyBalanceReport(){
		$data = $this->monthlyBalancedData();
		$report = '<p>Period Total: $'. number_format($data['total'],2) .'.</br>
				Fair Amount: $'. number_format($data['fairAmount'],2) .'.</p>';
	
		foreach ($data['members'] as $name => $value) {
			$total = $value['total'];
			$toRent = round($data['fairAmount'] - $total,2);
			if ($total > $data['fairAmount']) {
				$report .= '<p>'. $name .' has exceeded the fair amount by $'. number_format(abs($toRent),2) .
				'; *see that he/she gets this amount</p>';
			} else {
				if(substr($name, strlen($name)-1,1) == 's'){
					$name = $name . '\'';
				} else {
					$name = $name . '\'s';
				}
				$report .= '<p>'. $name .' part of the rent is $'. number_format($toRent,2) .'.</p>';
			}
		}
		return $report;
	}
	
	
	/*************************** mark for removal
	/**
	 * function that shows the given month's current standing.
	 * that is, it creates a horizontal bar chart with each of the users
	 * expenses as well as the current part of the rent
	 * @param string $month
	 * @return string
	 *
	function expensesDistribution($month){
	
		//sets the title
		$overviewTitle = 'Current Month';
		if ($month != 'current'){
			$overviewTitle = 'Past Month';
		}
	
		//assumes there are no results
		if ($this->getMonthTotal() == $this->getHhRent()){
			return '<div>
					<h3 class="text-center">'. $overviewTitle .'</h3>
					<h4 class="text-danger text-left">There are no results...</h4>
					</div>';
		}
	
		//gets total and fair amount
		$total  = $this->getMonthTotal();
		$fairAmount = round($total/$this->getNumberOfMembers(),2);
	
		//begins to build the chart
		$chart = '<div>
				<h3 class="text-center">'. $overviewTitle.'</h3>
				<h4 class="text-info text-left">Hover the mouse over the segments to see details</h4>
				</div>
				<div class="background-custom">';
	
		foreach ($this->members as $member){
			$cats = $member->billsDistribution();
			$catsPercent = [];
				
			foreach ($cats as $key => $value){
				if ($cats['total']> $fairAmount){
					$catsPercent["$key"] = $this->calculatePercent($value, $cats['total']);
					$overLimit = 'Over fair amount: $'. ($cats['total'] - $fairAmount);
				} else {
					$catsPercent["$key"] = $this->calculatePercent($value, $fairAmount);
					$overLimit = '';
				}
			}
			$rent = round($fairAmount - $cats['total'],2);
			$rentPercent = 100 - $catsPercent['total'];
			$chart .='<div>
					<div class="row">
					<div class="col-lg-6">
					<h4>'. $member->getUserFullName() .'</h4>
					</div>
					<div class="col-lg-6">
							<h4 class="text-right text-custom">'. $overLimit .'</h4>
					</div>
					</div>
					<div class="progress">
						  <span title="Food: $'. $cats['food'] .'">
						  		<div class="progress-bar progress-bar-info" style="width: '
							  		. $catsPercent['food'] .'%"><p class="text-center-ver">'. round($catsPercent['food']) .'%</p></div>
						  </span>
						  <span title="Maintenance: $'. $cats['maintenance'] .'">
						  		<div class="progress-bar progress-bar-warning" style="width: '
							  		. $catsPercent['maintenance'] .'%"><p class="text-center-ver">'. round($catsPercent['maintenance']) .'%</p></div>
						  </span>
						  <span title="Utility: $'. $cats['utility'] .'">
						  		<div class="progress-bar progress-bar-danger" style="width: '
							  		. $catsPercent['utility'] .'%"><p class="text-center-ver">'. round($catsPercent['utility']) .'%</p></div>
						  </span>
						  <span title="Other: $'. $cats['other'] .'">
						  		<div class="progress-bar progress-bar-success" style="width: '
							  		. $catsPercent['other'] .'%"><p class="text-center-ver">'. round($catsPercent['other']) .'%</p></div>
						  </span>
						  <span title="Rent: $'. $rent .'">
						  <div class="progress-bar" style="width: '. $rentPercent .'%"><p class="text-center-ver">'. round($rentPercent) .'%</p></div>
						  </span>
					</div>
					</div>';
		}
		$chart .= '<h4>Period Total: <em class="text-custom">$'. $this->getMonthTotal().'</em></h4>';
		$chart .= '<h4>Fair Amount: <em class="text-success">$'. $fairAmount.'</em></h4>
				</div>';
		return $chart;
	}*/
	
	/**
	 * calculates the percentage of a number given the hundred(refers to the 100%)
	 * 20% = (100 * 2)/ 10
	 * number 2 is the 20% of 10(variable defined as hundred)
	 * @param unknown $number
	 * @param unknown $hundred
	 * @return number
	 *
	function calculatePercent($number,$hundred){
		$result = (100 * $number)/$hundred;
		return $result;
	}
	
	/**
	 * returns a list of users' ids
	 * @return array
	 */
	function listOfUserIDs(){
		$ids = [];
		foreach ($this->members as $obj){
			$id = $obj->getUserID();
			array_push($ids, $id);
		}
		return $ids;
	}
	
	/**
	 * returns a table with the household user's status as a
	 * formated html table
	 * @return string
	 */
	function showUsersStatus($user){
	    if (sizeof($this->members) == 1){
	        $table = '<h4 class="text-warning">
                        No other members here...</h4>';
	    } else {
	        $table = '<table class="table table-striped table-hover">
				<tHead>
				<tr>
				<th>Name</th><th>Status</th><th>Level</th>
				</tr>
				</tHead>
				<tBody>';
	        foreach ($this->members as $member){
	            if($member->getUserID() != $user->getUserID()){
	                $table .= $member->getUserStatusAsRow();
	            }
	        }
	        $table .= '
				</tBody></table>';
	    }
		
		return $table;
	}
	
	/**
	 * retrieves custom report from Database
	 * @param array $postArray
	 * @param object $pdo, PDO object connection
	 * @return string, error message or formatted html table with results
	 */
	function retrieveReportData($postArray, $pdo){
		$id = $this->householdId;
		$date = '%'.$postArray['date'].'%';
		$name = '';
		$category = '';
	
		$constrainUser = '';
		$constrainCategory = '';
	
		$tokensArray = array(':hhID'=> $id, ':date'=> $date);
	
		if($postArray['name'] != 'all'){
			$constrainUser = ' AND u.UserID = :userid ';
			$name = $postArray['name'];
			$tokensArray[':userid'] = $name;
		}
		if($postArray['category'] != 'all'){
			$constrainCategory = ' AND BillCategory = :category';
			$category = $postArray['category'];
			$tokensArray[':category'] = $category;
		}
	
	
		$getCustomReport =
		$pdo->prepare('
				SELECT LastName, FirstName, BillDesc, BillAmount, BillCategory, BillDate
				FROM hhm_users u
				INNER JOIN hhm_bills b
				ON u.UserID = b.UserID
				WHERE b.HouseholdID = :hhID AND BillDate LIKE :date
				'. $constrainUser .'
				'. $constrainCategory .'
				');
		$getCustomReport->execute($tokensArray);
		$info = $getCustomReport->fetchAll(PDO::FETCH_ASSOC);
	
		if(empty($info)){
			$table = '<h4 class="text-danger">
					There are no results with those parameters...</h4>';
			return $table;
		} else {
			return $this->formatReport($info, 'Query results:');
		}
	}
	
	/**
	 * formats a given array with elements belonging to a query report
	 * and outputs the array as a neat html table
	 * @param array $array
	 * @param string $string
	 * @return string
	 */
	function formatReport($array,$string){
	
		$total = 0.0;
		$table = '<h3 class="text-success">'. $string .'</h3>
				<table class="table table-striped table-hover">
				<tHead>
				<tr>
				<th>Name</th>
				<th>Bill Description</th>
				<th>Bill Amount</th>
				<th>Bill Category</th>
				<th>Bill Date</th>
				</tr>
				</tHead>
				<tBody>';
		foreach ($array as $value){
			$total += floatval($value['BillAmount']);
			$shortDate = explode(" ", $value['BillDate']);
			$shortDate = $shortDate[0];
			$table .= '<tr>
					<td>'. $value['FirstName'].' '.$value['LastName'] .'</td>
					<td>'. $value['BillDesc'] .'</td>
					<td>'. $value['BillAmount'] .'</td>
					<td>'. $value['BillCategory'] .'</td>
					<td>'. $shortDate .'</td>
					</tr>';
		}
		$table .= '
				<tr class="danger">
				<td></td>
				<th class="text-right">Total:</th><th>$'.$total.'</th><td></td><td></td>
				</tr>
				</tBody></table>';
	
		return $table;
	}
	
	/***
	 * methods that loops through the members and determines if there is need
	 * to create a report.
	 * 
	 * Avoids creating a report when there are none bills to do so...
	 * @return boolean
	 */
	function needReport() {
	    $produceReport = false;
	    foreach ($this->members as $member) {
	        $array = $member->getBills();
	        
	        if ($array != null && sizeof($array) != 0) {
	            $produceReport = true;
	            break;
	        }
	    }
	    return $produceReport;
	}
	
	/**
	 * function that checks if all members of this household are done adding their bills
	 * returns true if so
	 * it is intented that this function is run everytime an user changes their status to "done"
	 * and at the end of each month
	 * @return boolean
	 */
	function areAllUsersDone(){
		$connection = new Connection();
		$pdo = $connection->getConnection();
		unset($connection);
		
		$status =
		$pdo->prepare('SELECT FirstName FROM hhm_users 
				WHERE UserStatus = "not done" AND HouseholdID = :id');
		$status->execute(array(':id'=>$this->householdId));
		$info = $status->fetchAll(PDO::FETCH_COLUMN);
		if (empty($info)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * function that validates the report form and calls the appropiate function
	 * to query the database
	 * @param array $postArray
	 * @return string
	 */
	function processReportForm($postArray){
		if(isset($postArray['user']) 
				&& isset($postArray['category']) && isset($postArray['month'])) {
 			$name = filter_var($postArray['user'],FILTER_SANITIZE_STRING);
 			$category = filter_var($postArray['category'],FILTER_SANITIZE_STRING);
 			$date = filter_var($postArray['month'],FILTER_SANITIZE_STRING);
 			$postArray = array('name'=> $name,'category'=> $category,'date'=> $date);
 			$connection = new Connection();
 			$pdo = $connection->getConnection();
 			unset($connection);
 			return $this->retrieveReportData($postArray,$pdo);
 		} else {
 			header('Locate: report.php');
 		}
	}
	
	/**
	 * mails a monthly spreadsheet to users of the current household
	 * @uses PHPMailer Function uses PHPMailer credits to:
     * https://github.com/PHPMailer/PHPMailer
	 */
	function mailMonthlySpreadsheet(){
	    
	    require_once $GLOBALS['ROOT_PATH'] . 'PHPMailer/PHPMailerAutoload.php';
		
		$admin = $this->getHouseholdAdmin();
		$adminName = $admin->getUserFullName();
		$adminEmail = $admin->getEmail();
		
		$currentMonth = date('Y-m');
		
		$members = $this->getMembers();
		$sheetName = $this->createSpreadsheet();
		
		$subject = str_replace('.xlsx', '',$sheetName). ' closing.';
		
		$monthSummary = $this->formatMonthlyBalanceReport();
		
		$message = '<html>
				<body>
				  <h3>Hello <<name>>,</h3>
				  <p>This is an automated email to notify you that '. $currentMonth .' </br>
				  		period of the '. $this->getHhName() .' has been
				  		closed and balanced!</p>
				  <p>Here are the results: </br></p>
				  				'.$monthSummary.'
				  <p>HHManagement web app.</p>
				</body>
				</html>';
		$file_to_attach = $GLOBALS['ROOT_PATH'] .'spreadsheets/'.$sheetName;
		
		foreach ($members as $member) {
			$userEmail = $member->getEmail();
			$userName = $member->getUserFirstName();
			$email = new PHPMailer();
			$email->From = 'hhmanageadmin@neoazareth.com';
			$email->FromName = $adminName . '(HhManage Admin)';
			$email->Subject = $subject;
			//$email->Subject = 'Yet, another test... ignore';
			$email->msgHTML(str_replace('<<name>>', $userName, $message));
			$email->addAddress( $userEmail);
			$email->addAttachment( $file_to_attach , $sheetName );
			$email->Send();
		}
		
		if(file_exists($file_to_attach)){
			unlink($file_to_attach);
			return true;
		} else {
		    return false;
		}
	}
	
	/**
	 * function that uses php excel to create a spreadsheet with the
	 * month summary to be mailed to each user
	 * @return string
	 * @uses PHPExcel, Function uses PHPExcel credits to:
     * https://github.com/PHPOffice/PHPExcel
	 */
	function createSpreadsheet(){
		
	
		error_reporting(E_ALL);
		ini_set('display_errors', TRUE);
		ini_set('display_startup_errors', TRUE);
	
		define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
	
		/** Include PHPExcel */
		//require_once dirname(__FILE__) . '/../Classes/PHPExcel.php';
		
		require_once $GLOBALS['ROOT_PATH'].'Classes/PHPExcel.php';
	
	
		PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );
	
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		$currentMonth = date('Y-m');
		$title = $this->getHhName() . ' ' . $currentMonth;
		
		// Set document properties
		$objPHPExcel->getProperties()->setCreator("HhManagement")
		->setLastModifiedBy("HhManagement")
		->setTitle($title)
		->setSubject($currentMonth);
	
		//default styles
		$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
	
	
		//styles....
	
		//fonts
		//font red bold italic centered
		$fontRedBoldItalicCenter = array (
				'font' => array (
						'bold' => true,
						'italic' => true,
						'color' => array(
								'argb' => 'FFF40202',
						)
				),
				'alignment' => array (
						'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
				)
		);
	
		//font red bold
		$fontRedBold = array (
				'font' => array (
						'bold' => true,
						'color' => array(
								'argb' => 'FFF40202',
						)
				)
		);
	
		//font red
		$fontRed = array (
				'font' => array (
						'color' => array(
								'argb' => 'FFF40202',
						)
				)
		);
	
		//font Green
		$fontGreen = array (
				'font' => array (
						'color' => array(
								'argb' => '0008B448',
						)
				)
		);
	
		//font Bold Italic
		$fontBoldItalic = array (
				'font' => array (
						'bold' => true,
						'italic' => true,
				)
		);
	
		//font Bold Italic Centered
		$fontBoldItalicCenter = array (
				'font' => array (
						'bold' => true,
						'italic' => true,
				),
				'alignment' => array (
						'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
				)
		);
	
		//background fillings
		//fill red
		$fillRed = array (
				'fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'startcolor' => array(
								'argb' => 'FFF40202',
						),
				),
		);
	
		//fill yellow
		$fillYellow = array (
				'fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'startcolor' => array(
								'argb' => 'FFF2E500',
						),
				),
		);
	
		//fill green
		$fillGreen = array (
				'fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'startcolor' => array(
								'argb' => 'FF92D050',
						),
				),
		);
	
		//fill gray
		$fillGray = array (
				'fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'startcolor' => array(
								'argb' => 'FFD9D9D9',
						),
				),
		);
	
		//fill cream
		$fillCream = array (
				'fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'startcolor' => array(
								'argb' => 'FFC4BD97',
						),
				),
		);
	
		//sets the heading for the first table
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($fillCream);
		$objPHPExcel->getActiveSheet()->setCellValue('B1','Equal AMT');
		$objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($fontRedBoldItalicCenter);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($fillCream);
		$objPHPExcel->getActiveSheet()->setCellValue('C1','Ind. bills');
		$objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($fontRedBoldItalicCenter);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($fillCream);
		$objPHPExcel->getActiveSheet()->setCellValue('D1','To rent');
		$objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($fontRedBoldItalicCenter);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($fillCream);
	
		$numberOfMembers = $this->getNumberOfMembers();
		$monthTotal = $this->getMonthTotal();
		$rent = $this->getHhRent();
		$col = 65;//starts at column A
		$row = 2;//the table starts at row 2
	
		//array used to associate the bills with the respective user
		$array =[];
	
		//sets the members names fair amount and value
		$members = $this->getMembers();
		foreach ($members as $member) {
			$name = $member->getUserFirstName();
			$cellName = chr($col) . $row;
			$objPHPExcel->getActiveSheet()->setCellValue($cellName,$name);
			$objPHPExcel->getActiveSheet()->getStyle($cellName)->applyFromArray($fontBoldItalic);
	
			$cellInd = chr($col+2) . $row;
			$objPHPExcel->getActiveSheet()->setCellValue($cellInd,'0.0');
			$objPHPExcel->getActiveSheet()->getStyle($cellInd)->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
			$objPHPExcel->getActiveSheet()->getStyle($cellInd)->applyFromArray($fillRed);
	
			$cellFair = chr($col+1) . $row;
			$objPHPExcel->getActiveSheet()->getStyle($cellFair)->applyFromArray($fontRed);
			$objPHPExcel->getActiveSheet()->getStyle($cellFair)->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
			$objPHPExcel->getActiveSheet()->getStyle($cellFair)->applyFromArray($fillGray);
	
			$cellRent = chr($col+3) . $row;
			$objPHPExcel->getActiveSheet()->setCellValue($cellRent,'=SUM('. $cellFair .'-'. $cellInd .')');
			$objPHPExcel->getActiveSheet()->getStyle($cellRent)->applyFromArray($fontGreen);
			$objPHPExcel->getActiveSheet()->getStyle($cellRent)->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
			$objPHPExcel->getActiveSheet()->getStyle($cellInd)->applyFromArray($fillYellow);
	
			$array[$name]['cell'] = $cellInd;
			$row++;
		}
	
		//inserts the sum of the fair amounts to compare to the one below
		$endCell = chr($col+1) . ($row-1);
		$cell = chr($col+1) . $row;
		$objPHPExcel->getActiveSheet()->setCellValue($cell,'=SUM(B2:'.$endCell.')');
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fontRed);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->getNumberFormat()
		->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fillGray);
	
		//insert the rent check values
		$cell = chr($col+2) .$row;
		$objPHPExcel->getActiveSheet()->setCellValue($cell,'Rent');
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fontBoldItalic);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fillYellow);
		$cell = chr($col+3) . $row;
		$endCell = chr($col+3) .($row-1);
		$objPHPExcel->getActiveSheet()->setCellValue($cell,'=SUM(D2:'.$endCell.')');
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fontRedBold);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->getNumberFormat()
		->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fillYellow);
	
		//inserts the bill and amount labels
		$row += 2;
		$cellMergeEnd = chr($col+1) . $row;
		$cell = chr($col) . $row++;
		$objPHPExcel->getActiveSheet()->setCellValue($cell,'House bills');
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fontBoldItalicCenter);
		$objPHPExcel->getActiveSheet()->mergeCells($cell.':'.$cellMergeEnd);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fillRed);
	
		$cell = chr($col) . $row;
		$objPHPExcel->getActiveSheet()->setCellValue($cell,'Bill');
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fontBoldItalicCenter);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fillGreen);
	
		$cell = chr($col+1) . $row++;
		$objPHPExcel->getActiveSheet()->setCellValue($cell,'Amount');
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fontBoldItalicCenter);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fillGreen);
	
	
		//inserts the bills
		$startCell = chr($col+1) . $row;
		foreach ($members as $member) {
			$name = $member->getUserFirstName();
			$col = 65;
			$bills = $member->getBills();
			$array[$name]['bills'] = [];
			foreach ($bills as $bill) {
				$desc = $bill->getBillDescription();
				$amount = $bill->getBillAmount();
				$objPHPExcel->getActiveSheet()->setCellValue(chr($col) . $row,$desc);
				$amountCell = chr($col+1) . $row++;
				$objPHPExcel->getActiveSheet()->setCellValue($amountCell,$amount);
				$objPHPExcel->getActiveSheet()->getStyle($amountCell)->getNumberFormat()
				->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
				array_push($array[$name]['bills'], $amountCell);
					
			}
		}
	
		$col = 65;
	
		//inserts rent
		$cell = chr($col) .$row;
		$objPHPExcel->getActiveSheet()->setCellValue($cell,'Rent');
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fillYellow);
	
		$cell = chr($col+1) . $row++;
		$objPHPExcel->getActiveSheet()->setCellValue($cell,$rent);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->getNumberFormat()
		->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fillYellow);
		$endCell = chr($col+1) . ($row-1);
	
		//inserts the total of bills
		$col = 65;
		$cell = chr($col) .$row;
		$objPHPExcel->getActiveSheet()->setCellValue($cell,'Total H-B');
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fontRedBoldItalicCenter);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fillCream);
	
		$cell = chr($col+1) .$row++;
		$objPHPExcel->getActiveSheet()->setCellValue($cell, '=SUM('. $startCell .':'. $endCell .')');
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fontRedBoldItalicCenter);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->getNumberFormat()
		->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fillCream);
	
		//inserts the fair amount
		$cell = chr($col) .$row;
		$objPHPExcel->getActiveSheet()->setCellValue($cell,'Fair Amount if ' . $numberOfMembers);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fillGray);
		$cell = chr($col+1) .$row;
		$objPHPExcel->getActiveSheet()
		->setCellValue($cell,'='. chr($col+1) .($row-1) . '/' . $numberOfMembers);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->getNumberFormat()
		->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
		$objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($fillGray);
		$fairAmountCell = chr($col+1) . $row;
	
		$row = 2;
		foreach ($members as $member) {
			$col = 66;
			$objPHPExcel->getActiveSheet()->setCellValue(chr($col) . $row++,'='. $fairAmountCell);
		}
	
		//inserts the individual bills
		foreach ($array as $value){
			$cell = $value['cell'];
			$sumOfBills = '';
				
			if (isset($value['bills'])) {
				$bills = $value['bills'];
				$counter = 1;
				foreach ($bills as $bill){
					if ($counter == 1){
						$sumOfBills .= $bill;
					} else {
						$sumOfBills .= '+' . $bill;
					}
					$counter++;
				}
			}
			$objPHPExcel->getActiveSheet()->setCellValue($cell,'=SUM('. $sumOfBills . ')');
		}
	
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	
		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle($title);
	
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
	
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->setPreCalculateFormulas(true);
		//$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
		$objWriter->save(str_replace('.php', '.xlsx', $GLOBALS['ROOT_PATH']. 'spreadsheets/'. $title .'.php'));
	
		return $title . '.xlsx';
	}
	
	/**
	 * Returns a list of only available months in the database.
	 * 
	 * Instead of creating a list with several dates in order to create reports
	 * this method queries the database to create a list of only available months
	 */
	function retrieveListOfMonths() {
	    $connection = new Connection();
	    $pdo = $connection->getConnection();
	    
	    $query = "SELECT DISTINCT LEFT(BillDate, LOCATE('-',BillDate,6)-1)
            FROM hhm_bills WHERE HouseholdID = :value";
	    
	    $availableMonths = $pdo->prepare($query);
	    if ($availableMonths->execute(array(':value'=>$this->householdId))) {
	        
	        $data = $availableMonths->fetchAll(PDO::FETCH_ASSOC);
	        $list = array();
	        if ($data) {
	            foreach ($data as $row) {
	                foreach ($row as $key => $value){
	                    array_push($list,$value);
	                }
	            }
	            $list = array_reverse($list);
	        } else {
	            array_push($list, date('Y-m'));
	        }
	        return $list;
	    } else {
	        return Page::listOfMonthsPriorToCurrent();
	    }	    
	}
}