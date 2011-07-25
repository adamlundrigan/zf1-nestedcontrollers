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
 * @see Zend_Console_Getopt
 */
require_once "Zend/Console/Getopt.php";

/**
 * Generate module controller class maps for use with namespaced controllers
 */

$modulePath = dirname(__FILE__) . "/../application/modules";
if ( !is_dir($modulePath) ) {
    echo "Unable to locate modules directory!\n";
    exit(2);
}

$rules = array(
    'help|h'        => 'Get usage message',
    'module|m=s'    => 'Name of the module to generate classmap for',
    'module-dir|md-s'    => 'Name of the folder containing modules',
    'controller-dir|cd-s'    => 'Name of the folder containing controllers'
);

try {
    $opts = new Zend_Console_Getopt($rules);
    $opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    exit();
}

if ($opts->getOption('h')) {
    echo $opts->getUsageMessage();
    exit();
}

$moduleName = $opts->getOption('m');
if ( is_null($moduleName) ) {
    echo $opts->getUsageMessage();
    exit();  
}
//if ( $moduleName == 'default' ) {
//    echo "Nested controllers in default module is not currently supported\n";
//    exit(2);  
//}

$moduleDir = $opts->getOption('md');
if ( is_null($moduleDir) ) $moduleDir = 'modules';

$controllerDir = $opts->getOption('cd');
if ( is_null($controllerDir) ) $controllerDir = 'controllers';

$dir = realpath(implode(DIRECTORY_SEPARATOR, array(
    $modulePath,
    $moduleName,
    $controllerDir
)));

if ( empty($dir) ) {
    echo "Unable to locate controller directory!\n";
    echo "Tried: {$dir}\n";
    exit();
}

$cmd = "{$_SERVER['_']} " . dirname(__FILE__) . "/classmap_generator.php --overwrite --library {$dir}";
echo shell_exec(escapeshellcmd($cmd));