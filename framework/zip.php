<?php
class Zip {
  private $Overwrite, $Zipper, $Files = [];
  /******************************************************************************************************************
  * __construct()
  *****************************************************************************************************************/
  function __construct($_overwrite = true) {
    $this->Zipper = new ZipArchive;
    $this->Overwrite = $_overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE;
  }


  public function Files($_files = [] , $_reset = false) {
    if ( !$_files )
      return $this->Files;

    if ( !is_array($_files) )
      $_files = [$_files];

    $this->Files = $_reset ? $_files : array_merge($this->Files , $_files);
    // Validate files
    foreach($this->Files as $k => $file) {
      if ( !file_exists($file) )
        unset($this->Files[$k]);
    }

    $this->Files = array_values($this->Files);

    return $this;
  }

  public function Save($_destination) {
    // Validate the destination
    if ( $this->Zipper->open($_destination , $this->Overwrite) !== true ) {
      new \System\Notice('Unable to validate zip arhive destination.');
      new \System\Log('Unable to write to zip arhive in framework/zip : '.__LINE__);
      return $this;
    }

    foreach($this->Files as $file)
      $this->Zipper->addFile($file , $file);

    $this->Zipper->close();

    // Validate the destination once more
    if ( !file_exists($_destination) ) {
      new \System\Notice('Creation of zip arhive failed.');
      new \System\Log('Creation of the zip archive failed in framework/zip : '.__LINE__);
      return $this;
    }

    return $this;
  }


  /******************************************************************************************************************
  * __destruct()
  *****************************************************************************************************************/
  function __destruct() {}
} // End of class Zip

?>
