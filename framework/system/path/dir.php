<?php
namespace System\Path {
  class Dir {
      /******************************************************************************************************************
       * __construct() -
       *****************************************************************************************************************/
      function __construct() {

      }

      /******************************************************************************************************************
       * Base() - Returns the base path of Able, regardless if local or global
       *****************************************************************************************************************/
      public function Base() {
        return SYS_ABLE_PATH;
      }

      /******************************************************************************************************************
       * Local() - Returns the local base path of Able
       *****************************************************************************************************************/
      public function Local() {
        return able_local_path();
      }

      /******************************************************************************************************************
       * Central() - Returns the central base path of Able (if it exists)
       *****************************************************************************************************************/
      public function Central() {
        return SYS_ABLE_PATH;
      }


      /******************************************************************************************************************
       * Web() - Returns web accessible root
       *****************************************************************************************************************/
      public function Web() {
        return realpath($this->Local().'/../');
      }

      /******************************************************************************************************************
       * Log() - Returns local log dir
       *****************************************************************************************************************/
      public function Log() {
        return realpath($this->Local().'/../../log');
      }


      /******************************************************************************************************************
       * UI() - Returns the base path of the ui dir being used
       * @param $mode - default , local , or central
       * @return string
       *****************************************************************************************************************/
      public function UI($mode='default') {
        if ( strtolower($mode) == 'local' )
          return ($this->Local().'/ui');
        if ( strtolower($mode) == 'central' )
          return ($this->Central().'/ui');

        return ($this->Local().'/ui');
      }









      function __destruct() {}
  }
}
?>
