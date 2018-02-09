<?php
namespace UI {
  class Extension {
    private $Element, $Path, $DataKey;
    function __construct($callsign) {
      if ( preg_match('#\.php$#i', $callsign) ) // if its UI\Element(__FILE__)
        $callsign = 'extensions/' . str_replace('.php', '', basename($callsign));

      $this->Element = $this->Locate_UI_Element($callsign);

      $this->Path = $this->Element->Path();
      $this->DataKey = 'UI\Extensions\Data\\'.$this->Element->Name();

      //echo "Returned path for $callsign is ".$this->Path." <br />\n";


    }

    private function Locate_UI_Element($callsign) {
      $original_callsign = $callsign;
      if ( preg_match('#\.php$#i', $callsign) ) // if its UI\Element(__FILE__)
        $callsign = 'extensions/' . str_replace('.php', '', basename($callsign));

      $element = new \UI\Element($callsign);

      // Make sure it had a callsign and didn't default to a theme
      if ( $element->Type() != 'extension' )
        $element = new \UI\Element('extensions/'.$callsign);


      if ( !$element->Path() )
        $element = new \UI\Element('objects/'.(new \UI\Element($original_callsign))->Name());

      return $element;
    }

    public function Path() {
      return $this->Path;
    }

    public function Invoke($ObjectData = [] , $BufferOutput = true) {
      if ( !$BufferOutput ) {}

      if ( !$this->Element->Path() || !file_exists($this->Element->Path()) ) {
        $msg = 'Extension `'.$this->Element->Callsign().'` not found. [UI\Extension:'.__LINE__.']';
        new \System\Notice($msg);
        return $msg;
      }

      // At this point we know the path is good to go, lets see if its html or php

      if ( preg_match('#\.html$#i', $this->Element->Path()) )
        return file_get_contents($this->Element->Path());


      $_SESSION[$this->DataKey] = [];
      if ( $ObjectData )
        $_SESSION[$this->DataKey] = $ObjectData;

      // at this point we know its php, either buffer it or don't
      if ( $BufferOutput ) {
        ob_start();
        include $this->Element->Path();
        return ob_get_clean();
      }


      include $this->Element->Path();
      return $this;
    }

    function Data($attr='') {
      $data = (isset($_SESSION[$this->DataKey])) ? $_SESSION[$this->DataKey] : [];
      if ( !$attr )
        return $data;

      if ( isset($data[$attr]) )
        return $data[$attr];


      return '';
    }

    function __destruct() {}
  }
}
?>
