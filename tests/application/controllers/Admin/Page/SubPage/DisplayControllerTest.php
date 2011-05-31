<?php

/**
 * @group Controllers
 */
class Admin_Page_SubPage_DisplayControllerTest extends ControllerTestCase
{

    public function testCallingControllerWithoutActionShouldPullFromIndexAction()
    {
        $this->dispatch('/admin/page/sub-page/display');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('page_sub-page_display');
        $this->assertAction('index');
    }
    
    public function testControllerAcceptsParamtersViaUri()
    {
        $this->dispatch('/admin/page/sub-page/display/index/one/unit/two/test');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('page_sub-page_display');
        $this->assertAction('index');
        $this->assertSame('unit', $this->getRequest()->getParam('one'));
        $this->assertSame('test', $this->getRequest()->getParam('two'));
    }
    
    public function testCallingControllerWithEditActionInUriShouldPullFromEditAction()
    {
        $this->dispatch('/admin/page/sub-page/display/edit');
        $this->assertResponseCode(200);
        $this->assertController('page_sub-page_display');
        $this->assertModule('admin');
        $this->assertAction('edit');
    }
    
    public function testCallShouldBeRoutedByRouteNamedForControllerClass()
    {
        $this->dispatch('/admin/page/sub-page/display');
        $this->assertRoute('Admin_Page_SubPage_DisplayController');
    }
    
}