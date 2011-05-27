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
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';


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
class CDLI_Standard_Mvc_Resource_Subcontrollers extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Application Bootstrap
     * @var Zend_Application_Bootstrap_Bootstrap
     */
    protected $bootstrap;
    
    /**
     * Front Controller
     * @var Zend_Controller_Front
     */
    protected $frontController;
    
    /**
     * Name of the file containing classmaps
     * @var string
     */
    protected $classmapFilename = ".classmap.php";
    
    /**
     * Resource Initialization
     */
    public function init()
    {
        $this->bootstrap = $this->getBootstrap();   
        $this->bootstrap->bootstrap('modules');
        
        $this->frontController = $this->bootstrap->getResource('frontcontroller');
        $this->frontController instanceof Zend_Controller_Front;
        
        $modules = $this->bootstrap->getPluginResource('modules');
        if ( $modules instanceof Zend_Application_Resource_Modules )
        {
            $bootstrapSet = $modules->getExecutedBootstraps();
            foreach ( $bootstrapSet as $moduleName=>$moduleBootstrap )
            {
                $this->_processModuleBootstrap($moduleBootstrap);
            }
        }
    }
    
    /**
     * Add a custom route for the controllers in the module referenced by the bootstrap
     * @param Zend_Application_Module_Bootstrap $moduleBootstrap bootstrap
     */
    protected function _processModuleBootstrap($moduleBootstrap)
    {
        if ( $moduleBootstrap instanceof Zend_Application_Module_Bootstrap )
        {
            $moduleLoader = $moduleBootstrap->getResourceLoader();
            $namespace = $moduleLoader->getNamespace();
            
            // Build path to the classmap file
            $classmapFile = implode(DIRECTORY_SEPARATOR, array(
                $moduleLoader->getBasePath(),
                $this->frontController->getModuleControllerDirectoryName(),
                $this->classmapFilename
            ));
            if ( Zend_Loader::isReadable($classmapFile) )
            {
                // Load the classmap file
                $classmap = include($classmapFile);
                if ( count($classmap) > 0 )
                {
                    // Iterate over each controller in the classmap
                    foreach ( $classmap as $className=>$fileName )
                    {
                        // Only process controllers which are stored in subdirectories
                        // Extract the controller name part from the class name
                        if ( preg_match("/^{$namespace}_(.+)Controller$/", $className, $matches) )
                        {
                            $controllerName = $matches[1];
                            // Process only sub-controllers (ie: name-part has _)
                            if ( preg_match('/_/', $controllerName) )
                            {
                                // Build the URI used to route to this controller
                                $controllerUri = $this->_convertControllerPartToUri($controllerName);
                                $uri = implode("/", array(
                                    $this->_convertControllerPartToUri($namespace),
                                    $controllerUri,
                                    ':action',
                                    '*'
                                ));
                                
                                // Construct the route
                                $this->frontController->getRouter()->addRoute(
                                    $className,
                                    new Zend_Controller_Router_Route(
                                        $uri,
                                        array(
                                            'module'=>strtolower($moduleBootstrap->getModuleName()),
                                            'controller'=>str_replace("/","_",$controllerUri),
                                            'action'=>$this->frontController->getDefaultAction()
                                        )
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Convert camelCased controller name to URI format
     * ie: PartOne_PartTwo -> part-one/part-two
     * @param string $controllerPart
     * @return string
     */
    protected function _convertControllerPartToUri($controllerPart)
    {
        $uri = array();
        $parts = explode("_", $controllerPart);
        if ( count($parts) > 0 )
        {
            foreach ( $parts as $controllerPart )
            {
                $uri[] = preg_replace("/^-/", "", preg_replace("/([A-Z])/e", "'-'.strtolower('\\1')", $controllerPart));
            }
        }
        return implode("/", $uri);
    }
    
}
