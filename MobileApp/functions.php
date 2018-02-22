<?php
/***
 * functions php holds random functions used by the mobile app
 */



/***
 * password retrival funcion used by the mobile app
 *
 * @param string $userEmail
 * @param string $pass
 * @todo merge with the User method with the sane purpose
 * @uses PHPMailer Function uses PHPMailer credits to:
 * https://github.com/PHPMailer/PHPMailer
 */
function sendPassword($userEmail,$pass){
    
    require_once '../PHPMailer/PHPMailerAutoload.php';
    
    $message = '<html>
				<body>
				  <h3>Hello HHMobileApp user,</h3>
				  <p>You have requested a new password through the reset password feature.</p>
				<p>Your new password: '. $pass .'</p>
				<p>We recommend you to change your password on the in-app update password feature.</p>
				  <p>HHManagement mobile app.</p>
				</body>
				</html>';
    
    
    $email = new PHPMailer();
    $email->From = 'hhmanageadmin@neoazareth.com';
    $email->FromName = 'Household Management Mobile App';
    $email->Subject = 'New temp password.';
    $email->msgHTML($message);
    $email->addAddress( $userEmail );
    return $email->Send();
}
