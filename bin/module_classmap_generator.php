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

$rules = array(
    'help|h'        => 'Get usage message',
    'module|m=s'    => 'Name of the module to generate classmap for',
    'appdir|ad-s'   => 'Path to application root',
    'module-dir|md-s'    => 'Name of the folder containing modules',
    'controller-dir|cd-s'    => 'Name of the folder containing controllers',
    'classmap-file|cf-s' => 'Name of file to write classmap into'
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

$appdir = $opts->getOption('ad');
if ( empty($appdir) )
{
    $appdir = dirname(__FILE__) . "/../application";
}
else
{
    $appdir = realpath($appdir);
}

if ( $appdir === false || !is_dir($appdir) )
{
    echo "Unable to locate application directory!\n";
    exit(2);   
}

$outfile = $opts->getOption('cf');
if ( empty($outfile) )
{
    $outfile = '.classmap.php';
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
if ( empty($moduleDir) ) $moduleDir = 'modules';

$controllerDir = $opts->getOption('cd');
if ( empty($controllerDir) ) $controllerDir = 'controllers';

$modulePath = "{$appdir}/{$moduleDir}";
if ( !is_dir($modulePath) ) {
    echo "Unable to locate modules directory!\n";
    exit(2);
}

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

$cmd = "{$_SERVER['_']} " . dirname(__FILE__) . "/classmap_generator.php --overwrite --library {$dir} --output {$outfile}";
echo shell_exec(escapeshellcmd($cmd));
