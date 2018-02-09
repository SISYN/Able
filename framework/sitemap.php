<?php
class SiteMap {
  private $MapRaw;
  private $MapCompiled;

  private $MapFileRaw = 'map.uri.raw.json'; // File location where raw map is stored
  private $MapFileCompiled = 'map.uri.compiled.json'; // File location where compiled map is stored

  private $MapRecompileThreshold = 86400; // Refresh site map files every 24hr

  /******************************************************************************************************************
  * __construct()
  *****************************************************************************************************************/
  function __construct($_force_new_compilation = false) {
    // Be sure site map is compiled before beginning
    $this->EnsureCompilation($_force_new_compilation);
  }




  /******************************************************************************************************************
  * URI() - Returns the URI for the supplied identifier
  * @param string $_identifier - String identifier that refers to the uri you're fetching
  * @return string - returns the identifier's URI
  *****************************************************************************************************************/
  public function URI($_identifier) {
    $_identifier = trim($_identifier, '/');
    if ( !isset($this->MapCompiled['uri'][$_identifier]) )
      return '/'. $_identifier;
    return $this->MapCompiled['uri'][$_identifier];
  }

  /******************************************************************************************************************
  * Data() - Returns the array of data (minus subsets) for the supplied URI
  * @param string $_uri - String URI for the data array you're fetching
  * @return array - returns the URI's data array (minus subsets)
  *****************************************************************************************************************/
  public function Data($_uri) {
    $_uri = single_slash($_uri);
    if ( !isset($this->MapCompiled['data'][$_uri]) )
      return [':unknown-uri-data'];
    return $this->MapCompiled['data'][$_uri];
  }

  /******************************************************************************************************************
  * Attr() - Returns an attribute for the supplied URI with possibility of inheritance
  * @param string $_attr - Attribute (array key) you wish to fetch
  * @param string $_uri - Attribute (array key) you wish to fetch
  * @param bool $_inherit - Set to true if you wish to backtrace for the chosen attribute if not found immediately
  * @param bool $_all - Set to true you want all instances of this inherited attribute returned
  * @return mixed - returns attribute, whatever type it is
  *****************************************************************************************************************/
  public function Attr($_attr , $_uri , $_inherit = true , $_all = false) {
    $data = $this->Data($_uri);


    if ( !$_inherit && !isset($data[$_attr]) )
      return ':attr-not-found';

    // Not found in this level, let's go back one level and check again
    return $this->InheritedAttr($_attr , $_uri . '/pseudo' , $_all);
  }


  /******************************************************************************************************************
  * InheritedAttr() - The method to fetch inherited attributes from public::Attr()
  * @param string $_attr - Attribute (array key) you wish to fetch
  * @param string $_uri - Attribute (array key) you wish to fetch
  * @param bool $_all - Set to true you want all instances of this inherited attribute returned
  * @param array $_backtrace - Stack of attributes if $_all is enabled
  * @return mixed - returns attribute, whatever type it is
  *****************************************************************************************************************/
  private function InheritedAttr($_attr , $_uri , $_all = false , $_backtrace = []) {
    // Go up one level in uri
    $new_uri = str_replace('\\', '/', dirname($_uri)); // Prevent Windows servers base dirs of \ being used instead of / like on Linux/Unix
    $data = $this->Data($new_uri);
    if ( $new_uri == $_uri ) { // Ran out of room to go back
      if ( !$_all ) // failed
        return ':inherited-attr-not-found';
      return $_backtrace;
    }

    if ( isset($data[$_attr]) ) {
      if ( !$_all )
        return $data[$_attr];
      $_backtrace[] = $data[$_attr];
    }

    return $this->InheritedAttr($_attr, $new_uri , $_all , $_backtrace);

  }

  /******************************************************************************************************************
  * Raw() - Returns the raw site map
  * @return array - returns the raw site map
  *****************************************************************************************************************/
  public function Raw() {
    return $this->MapRaw;
  }

  /******************************************************************************************************************
  * Compiled() - Returns the compiled site map
  * @return array - returns the compiled site map
  *****************************************************************************************************************/
  public function Compiled() {
    return $this->MapCompiled;
  }


  /******************************************************************************************************************
  * EnsureCompilation() - Forces new compilation if current one is not valid
  * @return object - returns $this
  *****************************************************************************************************************/
  private function EnsureCompilation($_force_new_compilation = false) {
    if ( $_force_new_compilation || !$this->ValidateCompilation() )
      $this->NewCompilation();

    return $this;
  }

  /******************************************************************************************************************
  * EnsureCompilation() - Returns true/false depending on the compiled map's validity
  * @return bool - true if the current compilation is valid, false otherwise
  *****************************************************************************************************************/
  private function ValidateCompilation() {
    // Look for compiled file
    $compiled = $this->CompiledMapFileContents();
    if ( !$compiled )
      return false;

    // Check modified time for compiled map file
    $mod_time = filemtime($this->MapFilePath('compiled'));
    if ( $mod_time < time() - $this->MapRecompileThreshold )
      return false;

    $this->MapCompiled = json_decode($compiled, 1);

    return true;
  }

  /******************************************************************************************************************
  * NewCompilation() - Forces new compilation
  * @return object - returns $this
  *****************************************************************************************************************/
  private function NewCompilation() {
    //echo "Starting new compile...\n";
    $this->NewRawMap();
    $compiler = new SiteMapCompiler($this->MapRaw);
    $this->MapCompiled = $compiler->Compile()->Output();
    $this->CompiledMapFileContents(json_encode($this->MapCompiled));

    return $this;
  }

  /******************************************************************************************************************
  * NewRawMap() - Forces new/refreshed raw map to be loaded
  * @return object - returns $this
  *****************************************************************************************************************/
  private function NewRawMap() {
    // If defined in this scope, get it locally
    $raw = ( defined('SITE_MAP') ) ? unserialize(SITE_MAP) : $this->RawMapFileContents();
    if ( !$raw ) {
      //echo "ERROR : New raw map not found...\n";
      return $this;
    }

    if ( is_string($raw) && is_json($raw) )
      $raw = json_decode($raw , 1);

      //echo "New raw map:: ";
      //print_r($raw);

    $this->MapRaw = $raw;
    $this->RawMapFileContents(json_encode($this->MapRaw));

    return $this;
  }


  /******************************************************************************************************************
  * MapFileBasePath() - Returns the base path for site map files
  * @return string - returns file path base
  *****************************************************************************************************************/
  private function MapFileBasePath() {
    return (new \System\Path\Dir)->Local() . '/maps/';
  }


  /******************************************************************************************************************
  * MapFilePath() - Returns the path for the chosen site map file
  * @param string $_type - Attribute (array key) you wish to fetch
  * @return string - returns file path
  *****************************************************************************************************************/
  private function MapFilePath($_type) {
    $member_var = 'MapFile' . ucwords($_type);
    return $this->MapFileBasePath() . $this->$member_var;
  }

  /******************************************************************************************************************
  * MapFileContents() - Returns the contents for the chosen site map file
  * @param string $_type - Type of map you're wanting (raw / compiled)
  * @param string $_contents - File contents if you wish to write to the map file
  * @return string - returns file contents
  *****************************************************************************************************************/
  private function MapFileContents($_type = 'compiled' , $_contents = '') {
    $path = $this->MapFilePath($_type);
    $file_contents = !file_exists($path) ? '' : file_get_contents($path);

    if ( $_contents ) {
      // Write contents to file
      $map_file_resource = fopen($path, 'wb');
      fwrite($map_file_resource, $_contents);
      fclose($map_file_resource);
    }

    return $file_contents;
  }

  /******************************************************************************************************************
  * RawMapFileContents() - Returns the raw map file contents (blank if non-existent)
  * @param string $_contents - File contents if you wish to write to the map file
  * @return string - returns file contents
  *****************************************************************************************************************/
  private function RawMapFileContents($_contents = '') {
    return $this->MapFileContents('raw' , $_contents);
  }

  /******************************************************************************************************************
  * CompiledMapFileContents() - Returns the compiled map file contents (blank if non-existent)
  * @param string $_contents - File contents if you wish to write to the map file
  * @return string - returns file contents
  *****************************************************************************************************************/
  private function CompiledMapFileContents($_contents = '') {
    return $this->MapFileContents('compiled' , $_contents);
  }






  /******************************************************************************************************************
  * __destruct()
  *****************************************************************************************************************/
  function __destruct() {}
} // End of class URI
?>
