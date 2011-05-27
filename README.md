Nested controllers in Zend Framework v1.x
=========================================

This project provides an application resource plug-in for Zend Framework v1 
to automate the construction of the routes necessary to be able to nest 
controllers within modules.  An example Zend_Application-based project is 
also provided to demonstrate how to use the resource plug-in.

The resource leverages Matthew Weier O'Phinney's work to backport the
classmap-based autoloader from Zend Framework 2 for use in ZFv1 projects. More
specifically, it uses the classmaps generated by the command-line tool to enumerate
all the controllers in each module and map routes to them accordingly.

The necessary code is linked in from Matthew's github repository via a git submodule
stored in `external/zf-examples`.  It currently links everything from the 
`feature/zf-classmap` branch his `zf-examples` repository, which is less than ideal
(i'm a bit of a git newbie, so if you can suggest a better way to do this, feel
free :))

## Props ##
Show these guys some love:

* Matthew Weier O'Phinney ([weierophinney](http://github.com/weierophinney)) for
  his work on the ZF2 classmap autoloader
* Ryan Mauger ([bittarman](http://github.com/bittarman)) for showing me that ZFv1
  could do nested controllers, and sharing an example: http://goo.gl/YDlBt

USAGE
-----

### Controller Setup ###

To create nested controllers, simply create sub-folders within the `controllers`
directory of the module you wish to add the controller(s) to.  The included example
application has the following structure inside the `application/modules` directory:

* admin
    * controllers
        * Page
            * SubPage
                * DisplayController.php   (Admin_Page_SubPage_DisplayController)
            * DisplayController.php   (Admin_Page_DisplayController)
        * StandardController.php  (Admin_StandardController)

When you add new nested controllers to a module in your project, you will need
to rebuild the classmap file for that module:

    cd bin;
    php module_classmap_generator.php --module <module name>

> To run this tool, please be sure to have a recent Zend Framework installation
> on your path

### Bootstrap Setup ###

To enable the application bootstrap resource which configures the routes for
our nested controllers you must add the following lines to your application.ini:

    ;; These configurations enable nested controller support
    autoloaderNamespaces[] = "CDLI_Standard_"
    pluginPaths.CDLI_Standard_Mvc_Resource = "CDLI/Standard/Mvc/Resource"
    resources.subcontrollers[] =

And you're done!  Your application should now be able to route directly to 
nested controllers

MODUS OPERANDI
--------------

Given a nested controller setup such as the following:

* admin
    * controllers
        * Page
            * SubPage
                * DisplayController.php   (Admin_Page_SubPage_DisplayController)
            * DisplayController.php   (Admin_Page_DisplayController)
        * StandardController.php  (Admin_StandardController)

and the associated classmap file (paths clipped for brevity):

    <?php
    $dirname_4ddfa1d65bbdc = dirname(__FILE__);
    return array (
      'Admin_StandardController' => ...,
      'Admin_Page_SubPage_DisplayController' => ...,
      'Admin_Page_DisplayController' => ...,
    );

The application bootstrap resource `CDLI_Standard_Mvc_Resource_Subcontrollers` will
construct a custom route for each of the nested controllers:

    admin/page/sub-page/display/:action -> Admin_Page_SubPage_DisplayController
    admin/page/display/:action -> Admin_Page_DisplayController

Non-nested controllers such as `Admin_StandardController` are not processed, as these
are routed properly by the default route.

DISCLAIMER
----------

This code is considered proof-of-concept, and has not been vetted or tested for
inclusion in a production environment.  Use of this code in such environments is
at your own risk. 

Released under the New BSD license:
http://framework.zend.com/license/new-bsd
