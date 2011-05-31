<?php

/**
 * @group Controllers
 */
class Default_IndexControllerTest extends ControllerTestCase
{

    public function testCallingControllerWithoutControllerOrActionShouldPullFromIndexControllerIndexAction()
    {
        $this->dispatch('/');
        $this->assertResponseCode(200);
        $this->assertModule('default');
        $this->assertController('index');
        $this->assertAction('index');
    }
    
    public function testControllerAcceptsParamtersViaUri()
    {
        $this->dispatch('/default/index/index/one/unit/two/test');
        $this->assertResponseCode(200);
        $this->assertModule('default');
        $this->assertController('index');
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