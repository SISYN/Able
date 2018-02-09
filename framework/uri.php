<?php
class URI {
  private $URI, $External;
  private $ExternalBaseURI = '/go/out/';
  private $Directory = []; // Holds all URIs and their labels, often imported with global $_INDEX
  /******************************************************************************************************************
  * __construct()
  *****************************************************************************************************************/
  function __construct($_uri) {
    $this->URI = $_uri;
    // Decide if its internal or external
    $this->External = preg_match('#^\/\/#', $_uri) ? true : false;
  }

  public function Get() {
    if ( $this->External )
      return $this->External();

    return (new SiteMap)->URI($this->URI);
  }


  private function External() {
    return $this->ExternalBaseURI . encode_string($this->URI).'/'.encode_string($_SERVER['REQUEST_URI']);
  }



  /******************************************************************************************************************
  * __destruct()
  *****************************************************************************************************************/
  function __destruct() {}
} // End of class URI
?>
