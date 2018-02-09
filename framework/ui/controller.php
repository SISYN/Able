<?php
namespace UI {
  class Controller {
    private $Element, $Data;
    function __construct($element, $theme='', $data=[]) {
      $this->Element = new \UI\Element($element, $theme);
      $this->Data = $data;
    }

    public function Set($who, $what) {
      $this->Data[$who] = $what;
      return $this;
    }

    public function Output() {
      return (new \UI\Parser)->Source(  $this->Element->Source()  )->Data(  $this->Data  )->Output();
    }

    function __destruct() {}
  }
}
?>
