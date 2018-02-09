<?php
namespace System\Path {
  $_SESSION['System\Path\File\Cache'] = [];
  class File {
      private $UseCache;
      /******************************************************************************************************************
       * __construct() -
       *****************************************************************************************************************/
      function __construct($cache=1) {
        $this->UseCache = $cache;
      }



      /******************************************************************************************************************
       * UI() - Returns the path to the specified ui file
       * @param $element - the ui element to find
       * @return string
       *****************************************************************************************************************/
      public function UI($element, $theme='') {
        return (new \UI\Path)->Get($element, $theme);
      } // End UI()









      function __destruct() {}
  }
}
?>
