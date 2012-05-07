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

$moduleDir = $opts->getOption('md');
if ( empty($moduleDir) ) $moduleDir = 'modules';

$controllerDir = $opts->getOption('cd');
if ( empty($controllerDir) ) $controllerDir = 'controllers';

if ( is_dir($appdir . DIRECTORY_SEPARATOR . $moduleDir) )
{
    $srcdir = implode(DIRECTORY_SEPARATOR, array($appdir, $moduleDir));
} elseif ( is_dir($appdir . DIRECTORY_SEPARATOR . $controllerDir) ) {
    $srcdir = implode(DIRECTORY_SEPARATOR, array($appdir, $controllerDir));
} else {
    echo "Unable to locate module or controller directory!\n";
    exit(2);
}


if ( empty($srcdir) ) {
    echo "Unable to locate application directory!\n";
    echo "Tried: {$srcdir}\n";
    exit();
}

$cmd = "{$_SERVER['_']} " . dirname(__FILE__) . "/classmap_generator.php --overwrite --library {$srcdir} --output {$appdir}/{$outfile}";
echo shell_exec(escapeshellcmd($cmd));
