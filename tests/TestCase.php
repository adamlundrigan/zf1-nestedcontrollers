<?php
require_once 'Zend/Application.php';

/**
 * PHPUnit Test Case
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
    public $application;

    public function setUp()
    {

        $this->application = new Zend_Application(
            APPLICATION_ENV,
            realpath(APPLICATION_PATH . '/configs/application.ini')
        );

        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
    }

    public function appBootstrap()
    {
        $this->application->bootstrap();
    }
    
}
