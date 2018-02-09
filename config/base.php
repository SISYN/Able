<?php
/******************************************************************************************************************
 * Able / config / base.php
 * Contains all necessary and basic configuration settings
 *
 * Used in:
 * framework/autoload/analytics.php
 *****************************************************************************************************************/

// Set default timezone to EST
date_default_timezone_set('America/New_York');

global $CONSTANTS;
$CONSTANTS = array_join_unique($CONSTANTS, [
  'SYS_ENABLE_ANALYTICS'    =>    TRUE
]);
?>
