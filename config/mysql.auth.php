<?php
/******************************************************************************************************************
 * Able / config / mysql.auth.php
 * Contains all MySQL authentication configs
 *
 * Used in:
 * framework/db/mysql/*
 *****************************************************************************************************************/

global $CONSTANTS;
$CONSTANTS = array_join_unique($CONSTANTS, [
  'MYSQL_AUTH_HOST'            =>    'localhost',
  'MYSQL_AUTH_USER'            =>    '[should be set in an imported file (local/able/import/file.php)]',
  'MYSQL_AUTH_PASS'            =>    '[should be set in an imported file (local/able/import/file.php)]',
  'MYSQL_AUTH_DB'              =>    '[should be set in an imported file (local/able/import/file.php)]'
]);
?>
