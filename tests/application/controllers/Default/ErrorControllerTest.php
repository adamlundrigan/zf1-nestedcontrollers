<?php

/**
 * @group Controllers
 */
class Default_ErrorControllerTest extends ControllerTestCase
{

    public function testDispatchErrorAction()
    {
        $this->dispatch('/error/error');
        $this->assertResponseCode(200);
        $this->assertModule('default');
        $this->assertController('error');
        $this->assertAction('error');
    }
    
    public function testDispatchNonexistentPathTriggers404ResponseCode()
    {
        $this->dispatch('/some-path-that-doesnt-actually-exist');
        $this->assertResponseCode(404);
        $this->assertModule('default');
        $this->assertController('error');
        $this->assertAction('error');
    }
    
}