<?php
require_once 'Zend/Application.php';

/**
 * PHPUnit Test Case
 */
abstract class ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        $this->bootstrap = new Zend_Application(
            APPLICATION_ENV,
            realpath(APPLICATION_PATH . '/configs/application.ini')
        );
        parent::setUp();
    }
    
}
