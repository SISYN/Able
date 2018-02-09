<?php
namespace UI {
  $_SESSION['UI\Element\Cache'] = [];
  class Element {
    private $Callsign, $Theme, $Ready;
    private $Prefix, $Delimiter, $Type, $Name, $Path;

    private $Interface_Type_Prefixes = [
      'theme'      =>  ['t', 'tem', 'template', 'templates', 'theme', 'themes', 'i', 'in', 'if', 'int', 'interface', 'interfaces'],
      'page'       =>  ['p', 'pg', 'page', 'pages'],
      'object'     =>  ['o', 'ob', 'obj', 'object', 'objects'],
      'extension'  =>  ['e', 'ex', 'ext', 'extension', 'extensions', 'a', 'auto']
    ];
    private $Interface_File_Extensions = [
      'php'  => ['extension'] ,
      'html' => ['theme' , 'page' , 'object']
    ];
    private $Interface_Prefix_Delimiters = [
      '.', '/'
    ];

    function __construct($callsign, $theme='') {
      $this->Ready = 0;
      $this->Theme = $theme;
      $this->Callsign = $callsign;
    }



    public function Callsign() {
      $this->Ready();
      return $this->Callsign;
    }
    public function Type($plural=false) {
      $this->Ready();
      if ( $plural )
        return $this->Type . 's';
      return $this->Type;
    }
    public function Name() {
      $this->Ready();
      return $this->Name;
    }
    public function File() {
      $this->Ready();
      $file_extension = array_parent($this->Type , $this->Interface_File_Extensions);
      return $this->Name . '.' . $file_extension;
    }

    public function Path($error_reporting=true) {
      // This one does not use Ready and is called independently as sometimes you only need the type
      if ( !$this->Path )
        $this->Path = (new \UI\Path)->Get($this->Callsign, $this->Theme);

      if ( !$this->Path && $error_reporting )
        new \System\Notice('UI element '.$this->Callsign.' not found. [UI\Element->Path:'.__LINE__.']');


      return $this->Path;
    }

    public function Source() {
      $cache_callsign = 'source:'.$this->Callsign.':'.$this->Theme;
      if ( !isset($_SESSION['UI\Element\Cache'][$cache_callsign]) ) {
        if ( !$this->Path() )
          return '[null]';
        $_SESSION['UI\Element\Cache'][$cache_callsign] = file_get_contents($this->Path());
      }

      return $_SESSION['UI\Element\Cache'][$cache_callsign];
    }

    // Makes sure its parsed before outputting any attributes
    private function Ready() {
      if ( !$this->Ready )
        $this->ParseCallsign();

      return $this;
    }

  /******************************************************************************************************************
   * ParseCallsign() - Dissects the callsign into appropriate pieces
   * @return string
   *****************************************************************************************************************/
    private function ParseCallsign() {

      // Store local versions as they'll be modified here
      $interface_prefix_delimiters = $this->Interface_Prefix_Delimiters;

      // Escape delimiter chars in prep for regex
      foreach($interface_prefix_delimiters as $k=>$delim) {
        // Treat the string as an array and escape all its characters
        $split = str_split($delim);
        foreach($split as $i=>$char)
          $split[$i] = '\\'.$char;

        $interface_prefix_delimiters[$k] = join('', $split);
      }

      // Join all prefixes for regex
      $all_prefixes = [];
      foreach($this->Interface_Type_Prefixes as $type=>$prefixes) {
        foreach($prefixes as $prefix)
          $all_prefixes[] = $prefix;
      }

      $all_prefixes_regex = join("|", $all_prefixes);
      $all_delims_regex = join('|', $interface_prefix_delimiters);

      $final_regex = '#^('.$all_prefixes_regex.')('.$all_delims_regex.')(.+)$#i';

      if ( !preg_match($final_regex, $this->Callsign) ) // Unknown prefix / type encountered , assume its a theme
        $this->Callsign = 'themes/'.$this->Callsign;

      preg_match($final_regex, $this->Callsign, $callsign_parts);
      $this->Prefix      =   $callsign_parts[1];
      $this->Delimiter   =   $callsign_parts[2];
      $this->Name        =   $callsign_parts[3];
      $this->Type        =   array_parent($this->Prefix , $this->Interface_Type_Prefixes);

      if ( !$this->Type ) {
        // Unknown prefix / type encountered
        $this->Type = 'unknown::matched';
        return $this;
      }

      $this->Ready = 1;

      return $this;
    }


    function __destruct() {}
  }
}
?>
