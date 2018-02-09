<?php
/******************************************************************************************************************
 * Able / autoload / utilities.ui.php
 * Defines a shorthand function to make using the UI class object simpler
 *****************************************************************************************************************/

if ( !function_exists('UI') ) {

    function UI() {
       if ( func_num_args() == 0 )
           return new \UI\Builder;

       if ( func_num_args() == 2 )
        return (new \UI\Controller(func_get_arg(0), '', @func_get_arg(1)))->Output();

       return (new \UI\Controller(func_get_arg(0), @func_get_arg(1), @func_get_arg(2)))->Output();
    }

}

?>
