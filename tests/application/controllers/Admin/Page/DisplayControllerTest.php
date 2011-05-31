<?php

/**
 * @group Controllers
 */
class Admin_Page_DisplayControllerTest extends ControllerTestCase
{

    public function testCallingControllerWithoutActionShouldPullFromIndexAction()
    {
        $this->dispatch('/admin/page/display');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('page_display');
        $this->assertAction('index');
    }
    
    public function testControllerAcceptsParamtersViaUri()
    {
        $this->dispatch('/admin/page/display/index/one/unit/two/test');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('page_display');
        $this->assertAction('index');
        $this->assertSame('unit', $this->getRequest()->getParam('one'));
        $this->assertSame('test', $this->getRequest()->getParam('two'));
    }
    
    public function testCallShouldBeRoutedByRouteNamedForControllerClass()
    {
        $this->dispatch('/admin/page/display');
        $this->assertRoute('Admin_Page_DisplayController');
    }
    
}