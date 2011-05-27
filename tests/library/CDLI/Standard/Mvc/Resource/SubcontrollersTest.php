<?php
/**
 * CDLI Standard Library for Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   CDLI
 * @package    Standard
 * @subpackage Mvc
 * @copyright  Copyright (c) 2011 Government of Newfoundland and Labrador
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Application Resource to manage MVC routing to subcontrollers
 *
 * @category   CDLI
 * @package    Standard
 * @subpackage Mvc
 * @author Adam Lundrigan <adam@lundrigan.ca>
 * @copyright  Copyright (c) 2011 Government of Newfoundland and Labrador
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class CDLI_Standard_Mvc_Resource_SubcontrollersTest extends TestCase
{
    
    public function setUp()
    {
        parent::setUp();        
        $this->resource = new CDLI_Standard_Mvc_Resource_Subcontrollers();        
        $this->resource->setBootstrap($this->application->getBootstrap());
    }
    
    /**
     * @param string $arg Argument
     * @param string $expectedResult Expected result
     * @dataProvider providerConvertControllerPartToUri
     */
    public function testConvertControllerPartToUri($arg, $expectedResult)
    {
        $actualResult = $this->resource->convertControllerPartToUri($arg);
        $this->assertSame($expectedResult, $actualResult);
    }
    
    /*
     * Data provider for testConvertControllerPartToUri
     * @return array
     */
    public function providerConvertControllerPartToUri()
    {
        return array(
            /* Controller part, Expected URI part */
            array('Index','index'),
            array('Index_Sub','index/sub'),
            array('Index_SubPage','index/sub-page'),
            array('Index_SubPage_AnotherSubPage','index/sub-page/another-sub-page')
        );
    }
    
    /**
     * @param string $arg Argument
     * @param string $expectedResult Expected result
     * @dataProvider providerCreateRouteFromController
     */
    public function testCreateRouteFromController($args, $expectedResults)
    {
        $route = $this->resource->createRouteFromController(
            $args['controller'],
            $args['module'],
            $args['namespace']
        );
        $route instanceof Zend_Controller_Router_Route;
        $actualResults = $route->getDefaults();
        $this->assertEquals($actualResults, $expectedResults);
    }
    
    /*
     * Data provider for testCreateRouteFromController
     * @return array
     */
    public function providerCreateRouteFromController()
    {
        $defaultAction = Zend_Controller_Front::getInstance()->getDefaultAction();
        return array(
            // Admin_Index_SubController
            array(
                array(
                    'controller'=>'Index_Sub',
                    'module'=>'admin',
                    'namespace'=>'Admin'
                ),
                array(
                    'module'=>'admin',
                    'controller'=>'index_sub',
                    'action'=>$defaultAction
                )
            ),
            // Admin_Index_SubPageController
            array(
                array(
                    'controller'=>'Index_SubPage',
                    'module'=>'admin',
                    'namespace'=>'Admin'
                ),
                array(
                    'module'=>'admin',
                    'controller'=>'index_sub-page',
                    'action'=>$defaultAction
                )
            ),
            // Admin_Index_SubPage_AnotherSubPageController
            array(
                array(
                    'controller'=>'Index_SubPage_AnotherSubPage',
                    'module'=>'admin',
                    'namespace'=>'Admin'
                ),
                array(
                    'module'=>'admin',
                    'controller'=>'index_sub-page_another-sub-page',
                    'action'=>$defaultAction
                )
            ),
            // Admin_IndexController
            array(
                array(
                    'controller'=>'Index',
                    'module'=>'admin',
                    'namespace'=>'Admin'
                ),
                array(
                    'module'=>'admin',
                    'controller'=>'index',
                    'action'=>$defaultAction
                )
            )
,
            // Admin_IndexController w/ improperly specified controller part
            array(
                array(
                    'controller'=>'Admin_IndexController',
                    'module'=>'admin',
                    'namespace'=>'Admin'
                ),
                array(
                    'module'=>'admin',
                    'controller'=>'index',
                    'action'=>$defaultAction
                )
            )
        );
    }
    
}
