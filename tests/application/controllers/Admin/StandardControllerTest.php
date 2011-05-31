<?php

/**
 * @group Controllers
 */
class Admin_StandardControllerTest extends ControllerTestCase
{

    public function testCallingControllerWithoutActionShouldPullFromIndexAction()
    {
        $this->dispatch('/admin/standard');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('standard');
        $this->assertAction('index');
    }
    
    public function testControllerAcceptsParamtersViaUri()
    {
        $this->dispatch('/admin/standard/index/one/unit/two/test');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('standard');
        $this->assertAction('index');
        $this->assertSame('unit', $this->getRequest()->getParam('one'));
        $this->assertSame('test', $this->getRequest()->getParam('two'));
    }
    
    public function testCallShouldBeRoutedByDefaultRoute()
    {
        $this->dispatch('/admin/standard');
        $this->assertRoute('default');
    }
    
}