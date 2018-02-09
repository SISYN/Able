<?php
/******************************************************************************************************************
 * Able / autoload / constants.php
 * Intiates the $COSNTANTS created in the config files
 *****************************************************************************************************************/
 global $CONSTANTS;
 foreach($CONSTANTS as $CONST=>$VAL) {
  if ( !defined($CONST) )
   define($CONST, $VAL);
 }
 

?>
