<?php

/**
 * @group Controllers
 */
class Default_SectionOne_PageOneControllerTest extends ControllerTestCase
{

    public function testCallingControllerWithoutActionShouldPullFromIndexAction()
    {
        $this->dispatch('/section-one/page-one');
        $this->assertResponseCode(200);
        $this->assertModule('default');
        $this->assertController('section-one_page-one');
        $this->assertAction('index');
    }
    
    public function testControllerAcceptsParamtersViaUri()
    {
        $this->dispatch('/section-one/page-one/index/one/unit/two/test');
        $this->assertResponseCode(200);
        $this->assertModule('default');
        $this->assertController('section-one_page-one');
        $this->assertAction('index');
        $this->assertSame('unit', $this->getRequest()->getParam('one'));
        $this->assertSame('test', $this->getRequest()->getParam('two'));
    }
    
    public function testCallingEditAction()
    {
        $this->dispatch('/section-one/page-one/edit');
        $this->assertResponseCode(200);
        $this->assertModule('default');
        $this->assertController('section-one_page-one');
        $this->assertAction('edit');
    }
    
    public function testCallShouldBeRoutedByRouteNamedForControllerClass()
    {
        $this->dispatch('/section-one/page-one');
        $this->assertRoute('Default_SectionOne_PageOneController');
    }
    
}