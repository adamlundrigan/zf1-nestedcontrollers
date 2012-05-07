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
     * Use a single classmap file?
     * @var boolean
     */
    protected $useSingleClassmapFile = false;

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
        $this->processOptions();

        $Routes = array();
        if ( $this->useSingleClassmapFile() )
        {
        }
        else
        {
            $modules = $this->bootstrap->getPluginResource('modules');
            if ( $modules instanceof Zend_Application_Resource_Modules )
            {
                $bootstrapSet = $modules->getExecutedBootstraps();
                foreach ( $bootstrapSet as $moduleName=>$moduleBootstrap )
                {
                    $Routes = array_merge(
                        $Routes, 
                        $this->processModuleBootstrap($moduleBootstrap)
                    );
                }
            }
        }
        if ( count($Routes) > 0 )
        {
            $this->frontController->getRouter()->addRoutes($Routes);
        }
    }

    /**
     * Process options from application configuration file
     */
    protected function processOptions()
    {
        $options = $this->getOptions();
        foreach ( $options as $key=>$option )
        {
            switch ( $key )
            {
                case 'singleClassmap':
                {
                    $this->setUseSingleClassmapFile($option);
                    break;
                }
                case 'classmapFilename':
                {
                    $this->setClassmapFilename($option);
                    break;
                }
            }
        }
    }

    /**
     * Choose Classmap File operation
     *   true  = use single, application-wide classmap file
     *   false = use one clasmap file per module
     *
     * @param bool $tf 
     * @return fluent interface
     */
    public function setUseSingleClassmapFile($tf)
    {
        $this->useSingleClassmapFile = ($tf == true);
        return $this;
    }

    /**
     * Getter for determining if we're using a single classmap file
     *
     * @return bool true = single file, false = one file per module
     */
    public function useSingleClassmapFile()
    {
        return $this->useSingleClassmapFile === true;
    }
    
    /**
     * Set the name of the file to load classmaps from
     * @param string $filename
     * @return fluent interface
     */
    public function setClassmapFilename($filename)
    {
        $this->classmapFilename = $filename;
        return $this;
    }

    /**
     * Get the name of the classmap file
     *
     * @return string
     */
    public function getClassmapFilename()
    {
        return $this->classmapFilename;
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
            $Routes = $this->processClassmapFile(
                $classmapFile,
                $moduleBootstrap->getModuleName(),
                $namespace
            );
        }
        
        return $Routes;
    }

    /**
     * Process a classmap file, generating routes for each controller class
     * @param string $classmapFile Path to the classmap file
     * @param string|null $module Name of module controller belongs to
     * @param string|null $moduleNamespace Module namespace
     */
    public function processClassmapFile($classmapFile, $module=NULL, $moduleNamespace=NULL)
    {
        $Routes = array();

        if ( Zend_Loader::isReadable($classmapFile) )
        {
            // Load the classmap file
            $classmap = include($classmapFile);
            if ( count($classmap) > 0 )
            {
                // Move all IndexController's to the top of the stack
                // so that it doesn't override other controllers at the
                // same directory level
                // @todo This should be performed when generating classmap file
                foreach ( $classmap as $className=>$fileName ) {
                    if (preg_match('/IndexController.php$/i', $fileName)) {
                        unset($classmap[$className]);
                        $classmap = array_merge(array($className=>$fileName), $classmap);
                    }
                }

                // If we were given a specific module namespace to load, inform the preg
                $pregPrefix = is_null($moduleNamespace) ? '' : "{$moduleNamespace}_";
                    
                // Iterate over each controller in the classmap
                foreach ( $classmap as $className=>$fileName )
                {
                    // Extract the controller name part from the class name
                    if ( preg_match("/^{$pregPrefix}(.+)Controller$/", $className, $matches) )
                    {
                        $controllerName = $matches[1];
                        // Only process controllers which are stored in subdirectories
                        if ( preg_match('/_/', $controllerName) )
                        {
                            // Generate the route
                            $Routes[$className] = $this->createRouteFromController(
                                $controllerName,
                                $module,
                                $moduleNamespace
                            );                                
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
        
        // Build the vanity URI used to route to this controller
        $controllerUri = $this->convertControllerPartToUri($controller);
        
        // Build the URI we're aliasing over
        $controllerName = str_replace("/","_",$controllerUri);
        
        // If the last sub-part of the controller name is index (ie: Something_IndexController)
        // then don't include that part in the vanity URI
        $controllerUri = preg_replace("/\/index$/i", "", $controllerUri);

        // Merge it
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
                'controller'=>$controllerName,
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
            foreach ( $parts as $nr=>$controllerPart )
            {
                $part = preg_replace("/^-/", "", preg_replace("/([A-Z])/e", "'-'.strtolower('\\1')", $controllerPart));
                // If first part is the default module, don't include it
                if ( ! ( $nr == 0 && $part == $this->frontController->getDefaultModule() ) ) {
                    $uri[] = $part;
                }
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
