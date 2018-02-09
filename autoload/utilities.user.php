<?php
/******************************************************************************************************************
 * Able / autoload / utilities.user.php
 * Defines a shorthand function to make using the User class object simpler
 *****************************************************************************************************************/

if ( !function_exists('User') ) {

    function User($user_id=0) {
      return (new User($user_id));
    }

}

?>
