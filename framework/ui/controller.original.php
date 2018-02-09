<?php
namespace UI {
  class Controller {
      private $FilePathHandler;
      private $ObjectCache;
      private $Theme, $ThemeVersion, $PageData;
      /******************************************************************************************************************
       * __construct() - Accepts a Theme name/dir, or an object file name and accompanying data
       * One argument supplied means it will treat that argument as a Theme name/dir
       * Two arguments supplied means it will treat those arguments as the object file/data pair
       * Three arguments supplied means it will treat those arguments as object file, object data, object theme
       *****************************************************************************************************************/
      function __construct() {
          $this->ObjectCache = []; // implement later
          $this->FilePathHandler = new FilePathHandler();
          $this->Theme = false;
          $this->ThemeVersion = 'index';
          $this->PageData = [
              'page.title'=>'',
              'page.headers'=>'',
              'page.body'=>''
          ];
          $Params = func_get_args();
          if ( func_num_args() == 1 )
              $this->Theme($Params[0]);
          if ( func_num_args() == 2 )
              $this->Body($Params[0], $Params[1]);
          if ( func_num_args() == 3 )
              $this->Body($this->Output($Params[0], $Params[1], $Params[2]));
      }


      /******************************************************************************************************************
       * Theme() - Sets or gets current theme
       * @param string $ThemeName - The name of the theme you wish to switch to
       * @return mixed
       *****************************************************************************************************************/
      public function Theme($ThemeName='') {
          if ( strlen(trim($ThemeName)) > 0 ) {
              // Set theme and return $this if a valid theme
              $theme_as_ui_element = new UI_Element('theme', $ThemeName);
              if ( file_exists($theme_as_ui_element->Location()) ) {
                  $this->Theme = $ThemeName;
                  return $this;
              } else {
                  echo '[inst:ui][invalid theme `'.$ThemeName.'`]';
                  return $this;
              }
          }

          // No theme name set, return current theme
          if ( !$this->Theme )
              $this->Theme = DEFAULT_THEME;
          return $this->Theme;
      }

      /******************************************************************************************************************
       * Version() - Sets or gets the current theme version
       * @param string $VersionName - The name of the theme you wish to switch to
       * @return mixed
       *****************************************************************************************************************/
      public function Version($VersionName='') {
          if ( strlen(trim($VersionName)) > 0 ) {
              // Set version and return $this if a valid version
              $version_as_ui_element = new UI_Element('version', $VersionName);
              if ( file_exists($version_as_ui_element->Location()) ) {
                  $this->ThemeVersion = $VersionName;
                  return $this;
              } else {
                  echo '[inst:ui][invalid version]';
                  return $this;
              }
          }

          // No version name set, return current version
          return $this->ThemeVersion;
      }
      /******************************************************************************************************************
       * Set() - Sets the page parameter if using CompiledOutput()
       * @param string $Param - Param to set
       * @param string $Value - Value to set
       * @return Object
       *****************************************************************************************************************/
      public function Set($Param, $Value) {
          if ( $Param != null && $Value != null )
              $this->PageData[$Param] = $Value;
          return $this;
      }
      /******************************************************************************************************************
       * Title() - Sets the page title if using CompiledOutput()
       * @param string $Title - Title to set
       * @return Object
       *****************************************************************************************************************/
      public function Title($Title) {
          if ( $Title != null )
              $this->PageData['page.title'] = $Title;
          return $this;
      }
      /******************************************************************************************************************
       * Headers() - Sets the page headers if using CompiledOutput()
       * @param string $Headers - Headers to set
       * @return Object
       *****************************************************************************************************************/
      public function Headers($Headers) {
          if ( $Headers != null ) {
              // check how many args are supplied
              if ( func_num_args() > 1 ) {
                  foreach(func_get_args() as $arg)
                      $this->ProcessHeaderBlob($arg);
              } else {
                  $this->ProcessHeaderBlob($Headers);
              }
          }
          return $this;
      }


      /******************************************************************************************************************
       * ProcessHeaderBlob() - Sets the page headers if using CompiledOutput()
       * @param string $Blob - Raw header data, could be HTML/CSS, could be file names, we need to find out
       * @return Object
       *****************************************************************************************************************/
      private function ProcessHeaderBlob($Blob) {
          // Check if its a file name
          $IsFileRegEx = '#^[\/\\\a-z0-9-\.]+\.(js|css|html|php|scss)$#';
          if ( preg_match($IsFileRegEx, $Blob) ) {
              $ext = preg_replace($IsFileRegEx, '$1', $Blob);
              if ( $ext == 'css' ) {
                  // if no backslash is included in the directory, assume they want to use the current theme dir
                  if ( !strstr($Blob, '/') && !strstr($Blob, '\\' ) )
                      $Blob = $this->FilePathHandler->Web($this->FilePathHandler->Themes() . '/'.$this->Theme().'/assets/css/'.$Blob);
                  $this->PageData['page.headers'] .= '<link href="'.$Blob.'" rel="stylesheet" />';
              }
              if ( $ext == 'js' ) {
                  // if no backslash is included in the directory, assume they want to use the current theme dir
                  if ( !strstr($Blob, '/') && !strstr($Blob, '\\' ) )
                      $Blob = $this->FilePathHandler->Slash($this->FilePathHandler->Themes() . '/'.$this->Theme().'/assets/js/'.$Blob);
                  $this->PageData['page.headers'] .= '<script type="text/javascript" src="'.$Blob.'"></script>';
              }
          } else {
              // it is not a file, its raw code, just insert it
              $this->PageData['page.headers'] .= $Blob;
          }
          return $this;
      }
      /******************************************************************************************************************
       * Body() - Sets the page body if using CompiledOutput(), can concatenate or start fresh with second param
       * @param null $Body - Accepts page object files (pg.page_name) or raw HTML
       * @param mixed $ForceFresh - If true, any second or subsequent call to Body will erase old body
       * @return Object
       *
       * Can also pass page object (pg.page_name) and data array as 1st/2nd params
       *****************************************************************************************************************/
      public function Body($Body=null, $ForceFresh=false) {
          if ( $Body != null ) {
              // Check if its an object
              $body_as_ui_element = new UI_Element($Body);
              if ( file_exists($body_as_ui_element->Location()) )
                  return $this->Body(
                      $body_as_ui_element->Parse($ForceFresh)
                  ); // In this case ForceFresh is being used to pass the data array
              if ( $ForceFresh )
                  $this->PageData['page.body'] = $Body;
              else
                  $this->PageData['page.body'] .= $Body;
          }
          return $this;
      }


      /******************************************************************************************************************
       * Output() - Outputs an object file with appropriate data, or a UI Object Instance
       * @param null $ObjectToOutput - An obj file name or a UI Object instance
       * @param [] $ObjectData - The obj file data if $ObjectToOutput is not a UI Object
       * @param null $ThemeDir - The Theme name/dir to be used while processing this object file
       * @param bool $ThemeStrict - If true, other themes will not be checked if object file does not exist
       * @return mixed
       *****************************************************************************************************************/
      public function Output($ObjectToOutput=null, $ObjectData=[], $ThemeDir=null, $ThemeStrict=true) {
          // If no arguments are supplied, return the compiled output for the entire class instance/object
          if ( func_num_args() == 0 || $ObjectToOutput == null )
              return $this->CompiledOutput();
          // If $ObjectToOutput is a string, treat it as an object file name
          if ( is_string($ObjectToOutput) ) {
              $output_element = new UI_Element($ObjectToOutput);
              return $output_element->Parse($ObjectData);
          }
          if ( is_object($ObjectToOutput) ) {
              // do $ObjectToOutput->Output()
              // modify this moethod so it can take 0 params and output the constructed page object stored within the class
              // the page is built by storing all pg. objects in a Pages array, then putting those into the BODY
              // of the tem.TEM_NAME file when the Output func is called
          }
          return false;
      }
      /******************************************************************************************************************
       * ThemeExists() - Returns true if theme is found, false if not
       * @param $LiveLoad - Whether to use SmartLoad to detect whether to print whole template or just central page
       * @return string
       *****************************************************************************************************************/
      private function CompiledOutput($LiveLoad=true) {
          // If LiveLoad is being used, check if this is a partial load request
          if ( $LiveLoad && $this->LiveLoadRequest() )
              return $this->LiveLoad();
          $version_ui_element = new UI_Element('version', $this->ThemeVersion);
          return $version_ui_element->Parse($this->PageData);
      }
      /******************************************************************************************************************
       * LiveLoadRequest() - Detect if LiveLoad is being requested
       * @return bool
       *****************************************************************************************************************/
      private function LiveLoadRequest() {
          return (
              ( isset($_POST[LIVELOAD_KEY]) && $_POST[LIVELOAD_KEY] == 1 ) ||
              ( isset($_GET [LIVELOAD_KEY]) && $_GET [LIVELOAD_KEY] == 1 )
          );
      }
      /******************************************************************************************************************
       * LiveLoad() - Return the page as a partial request via LiveLoad
       * @param $JSON - If false returns HTML only, if true returns JSON with title and HTML
       * @return bool
       *****************************************************************************************************************/
      private function LiveLoad($JSON=true) {
          if ( $JSON )
              return json_encode([
                  'title'=>$this->PageData['page.title'],
                  'headers'=>$this->PageData['page.headers'],
                  'html'=>$this->PageData['page.body']
              ]);
          return $this->PageData['page.body'];
      }

      function __destruct() {}
  }
}
?>
