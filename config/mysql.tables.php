<?php
/******************************************************************************************************************
 * Able / config / mysql.tables.php
 * Contains all MySQL table references
 *
 * Used in:
 * framework/user.php
 * framework/membership.php
 * autoload/analytics.php
 *****************************************************************************************************************/

global $CONSTANTS;
$CONSTANTS = array_join_unique($CONSTANTS, [
  'MYSQL_TABLE_ANALYTICS'             =>    '_analytics',
  'MYSQL_TABLE_USER_ACCTS'            =>    '_usr',
  'MYSQL_TABLE_USER_ATTRS'            =>    '_usr_attr',
]);
?>
