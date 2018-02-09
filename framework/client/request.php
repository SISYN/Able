<?php
namespace Client {
  class Request {
      function __construct() {

      }

      public function HTTP() {
        return new \Client\Request\HTTP;
      }


      function __destruct() { }
  }
}
?>
