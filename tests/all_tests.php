<?php
require_once '/home/isrsan2/neoazareth.com/hhv3/simpletest/autorun.php';

class AllTests extends  TestSuite {
    function AllTests() {
        parent::__construct();
        //add files as $this->addFile('test/filename.php');
        $this->addFile('/home/isrsan2/neoazareth.com/hhv3/tests/validation_test.php');
        
    }
}