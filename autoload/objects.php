<?php
/******************************************************************************************************************
 * Able / autoload / objects.php
 * Contains all class/object autoloading measures
 *****************************************************************************************************************/
 spl_autoload_register(function($ClassName) {
   (new \System\AutoLoad)->Import($ClassName);
 });

?>
