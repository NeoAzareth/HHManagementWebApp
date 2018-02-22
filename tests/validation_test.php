<?php
require_once '/home/isrsan2/neoazareth.com/hhv3/inc_0700/Validate.php';
require_once '/home/isrsan2/neoazareth.com/hhv3/inc_0700/Connection.php';

class TestOfValidation extends UnitTestCase{
        
    /////////tests for Validate::isEmpty()
    
    function testIsEmptyAllEmptyVariables(){
        $a = '';
        $b = ' ';
        $c = '               ';
        
        $this->assertTrue(Validate::isEmpty($a,$b,$c));
    }
    
    function testIsEmptyAllNoneEmptyVariables(){
        $a = 'hello';
        $b = 'hello world ';
        $c = '           hello        world    ';
        
        $this->assertFalse(Validate::isEmpty($a,$b,$c));
    }
    
    function testIsEmptyForMixedVariables(){
        $a = 'Hello';
        $b = ' World';
        $c = '               ';
        
        $this->assertFalse(Validate::isEmpty($a,$b,$c));
    }
    
    /////////test for Validate::isValidAmount()
    
    function testIsValidAmountForTrue(){
        $num = 1345.00;
        $this->assertTrue(Validate::isValidAmount($num));
    }
    
    function testIsValidAmountForFalseOverTheRange(){
        $num = 100001;
        $this->assertFalse(Validate::isValidAmount($num));
    }
    
    function testIsValidAmountForFalseBelowTheRange(){
        $num = -1.0;
        $this->assertFalse(Validate::isValidAmount($num));
    }
    
    /////////tests for Validate::isTokenAvaialable()
    
    function testIsTokenAvailableForTrue(){
        $con = new Connection();
        $pdo = $con->getConnection();
        unset($con);
        
        $token = 'randomemail@nowhere.com';
        $id = 'UserId';
        $column = 'Email';
        $table = 'hhm_users';
        
        $this->assertTrue(Validate::isTokenAvailable($token, $id, $column, $table, $pdo));
        unset($pdo);
    }
    
    function testIsTokenAvailableForFalse(){
        $con = new Connection();
        $pdo = $con->getConnection();
        unset($con);
        
        $token = 'neoazareth@gmail.com';
        $id = 'UserId';
        $column = 'Email';
        $table = 'hhm_users';
        
        $this->assertFalse(Validate::isTokenAvailable($token, $id, $column, $table, $pdo));
        unset($pdo);
    }
    
    //////////////////test for Validate::passwordStrength()
    
    function testPasswordStrenghtForTrue(){
        $pass = 'Th1s1sAv3ry5tr0ngP433';
        
        $this->assertTrue(Validate::passwordStrenght($pass));
    }
    
    function testPasswordStrenghtForFalse(){
        $pass = 'password';
        
        $this->assertFalse(Validate::passwordStrenght($pass));
    }
    
}