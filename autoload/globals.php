<?php
/******************************************************************************************************************
 * Able / autoload / init.php
 * Starts the output buffer, session, and defines basic globals to be used throughout the application
 *****************************************************************************************************************/
ob_start();
session_start();
define('ABLE_INITIAL_MICROTIME', microtime(true));

global $db;
$db = new \DB\MySQL;


?>
