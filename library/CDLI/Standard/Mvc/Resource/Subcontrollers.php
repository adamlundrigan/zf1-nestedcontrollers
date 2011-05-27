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
        $this->bootstrap->bootstrap('modules');
        
        $modules = $this->bootstrap->getPluginResource('modules');
        if ( $modules instanceof Zend_Application_Resource_Modules )
        {
            $bootstrapSet = $modules->getExecutedBootstraps();
            foreach ( $bootstrapSet as $moduleName=>$moduleBootstrap )
            {
                $routes = $this->processModuleBootstrap($moduleBootstrap);
                if ( count($routes) > 0 )
                {
                    $this->frontController->getRouter()->addRoutes(
                        $this->processModuleBootstrap($moduleBootstrap)
                    );
                }
            }
        }
    }
    
    /**
     * Create custom routes for the controllers in the module referenced by the bootstrap
     * @param Zend_Application_Module_Bootstrap $moduleBootstrap bootstrap
     * @return array Array of custom routes
     */
    public function processModuleBootstrap($moduleBootstrap)
    {
        $Routes = array();
        
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
                        // Extract the controller name part from the class name
                        if ( preg_match("/^{$namespace}_(.+)Controller$/", $className, $matches) )
                        {
                            $controllerName = $matches[1];
                            // Only process controllers which are stored in subdirectories
                            if ( preg_match('/_/', $controllerName) )
                            {
                                // Generate the route
                                $Routes[$className] = $this->createRouteFromController(
                                    $controllerName,
                                    $moduleBootstrap->getModuleName(),
                                    $namespace
                                );                                
                            }
                        }
                    }
                }
            }
        }
        
        return $Routes;
    }
    
    /**
     * Take information about a controller and build a route to it
     * @param type $controller Controller Part
     * @param type $module  Module Name
     * @param type $namespace Namespace
     * @return Zend_Controller_Router_Route 
     */
    public function createRouteFromController($controller, $module, $namespace)
    {
        // Strip off namespace and Controller suffix if it exists
        $controller = preg_replace(
            array("/^{$namespace}_/","/Controller$/"),
            "",
            $controller
        );
        
        // Build the URI used to route to this controller
        $controllerUri = $this->convertControllerPartToUri($controller);
        $uri = implode("/", array(
            $this->convertControllerPartToUri($namespace),
            $controllerUri,
            ':action',
            '*'
        ));

        // Construct the route
        return new Zend_Controller_Router_Route(
            $uri,
            array(
                'module'=>strtolower($module),
                'controller'=>str_replace("/","_",$controllerUri),
                'action'=>$this->frontController->getDefaultAction()
            )
        );
    }

    /**
     * Convert camelCased controller name to URI format
     * ie: PartOne_PartTwo -> part-one/part-two
     * @param string $controllerPart
     * @return string
     */
    public function convertControllerPartToUri($controllerPart)
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
    
    /**
     * Set the bootstrap to which the resource is attached
     *
     * @param  Zend_Application_Bootstrap_Bootstrapper $bootstrap
     * @return Zend_Application_Resource_Resource
     * @see Zend_Application_Resource_ResourceAbstract::setBootstrap
     */
    public function setBootstrap(Zend_Application_Bootstrap_Bootstrapper $bootstrap)
    {
        $this->bootstrap = $bootstrap;
        $this->frontController = Zend_Controller_Front::getInstance();
        parent::setBootstrap($bootstrap);
    }
    
}
