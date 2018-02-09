<?php
/************************************************************************************************************************************************************************
 * Able / autoload / init.php
 * The base file that includes all other files
 ************************************************************************************************************************************************************************
 * Using realpath() instead of relative paths to prevent issues with shared directories..
 ************************************************************************************************************************************************************************/
// Allow importation of able files before local imoprts are included
function import($_file) {
  require_once realpath(dirname(__FILE__).'/../'.$_file);
}

function able_local_path($_full = false) {
  $_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
  $_locator = '/able/';

  if ( file_exists($_root . $_locator) )
    return $_root . $_locator;

  // Go up a dir and try again
  while( $_root != dirname($_root) && !file_exists($_root . $_locator) )
    $_root = dirname($_root);


  if ( file_exists($_root . $_locator) )
    return $_root . $_locator;


  new \System\Notice('Unable to detect able locale in framework/system/path/dir.');
  new \System\Log('Unable to find able local path on framework/system/path/dir : '.__LINE__);

  return '';
}

// Require any foriegn imports in the local adom/import dir
foreach (glob(able_local_path() . '/import/*.php') as $file)
  require_once $file;


// Load framework components
require_once 'load.php';


?>
