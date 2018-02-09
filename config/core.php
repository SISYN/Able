<?php
/******************************************************************************************************************
 * Able / config / core.php
 * Contains all necessary system configuration settings
 *
 * Used in:
 * framework/system/path/*
 *****************************************************************************************************************/

global $CONSTANTS;
$CONSTANTS = array_join_unique($CONSTANTS, [
  'SYS_ABLE_PATH'    =>    realpath(dirname(__FILE__).'/../')
]);
?>
