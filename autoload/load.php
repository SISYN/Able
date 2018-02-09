<?php
/************************************************************************************************************************************************************************
 * Able / autoload / load.php
 * The base file that prioritizes the inclusion of system files
 ************************************************************************************************************************************************************************
 * Using realpath() instead of relative paths to prevent issues with shared directories..
 ************************************************************************************************************************************************************************/
// Load error handling first
require_once 'errors.php';

// Load utils functions next
require_once 'utilities.php';

// Now load all config files and  then initiate the $CONSTANTS array
foreach (glob(dirname(__FILE__).'/../config/*.php') as $file)
  require_once $file;

// Next load the constants that were declared in configs
require_once 'constants.php';

// Next load the system autoloader class
require_once realpath(dirname(__FILE__).'/../framework/system/autoload.php');

// Next load classes/objects
require_once 'objects.php';

// Load globals next
require_once 'globals.php';

// Now load all other files
foreach (glob(dirname(__FILE__).'/*.php') as $file)
  require_once $file;

  
?>
