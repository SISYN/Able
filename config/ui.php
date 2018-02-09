<?php
/******************************************************************************************************************
 * Able / config / ui.php
 * Contains all necessary UI configuration settings
 *
 * Used in:
 * framework/ui/path.php (RecursiveElementSearch)
 *****************************************************************************************************************/

global $CONSTANTS;
$CONSTANTS = array_join_unique($CONSTANTS, [
  'SYS_UI_AUTO_PARSER'    =>    '_auto'
]);
?>
