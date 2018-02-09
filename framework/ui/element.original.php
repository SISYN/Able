<?php
/******************************************************************************************************************
 * Known Issues:
 * 1. Placing an unused static variable inside a shorthand JS array declaration results in the first [ bracket
 * being eaten which causes a JS fatal syntax error.
 * eg: var `js_var = [[[var.unused]]]` when compiled results in `var js_var = ];`
 *
 * 2. Calling AutoUI elements without space around it fails to recognize it as variable
 *****************************************************************************************************************/
namespace UI {
  class Element {
      private $FilePathHandler, $CallSign, $Type, $Name, $FilePath;
      /******************************************************************************************************************
       * __construct() - Accepts an element name such as theme.name, page.name, obj.name, etc
       * If one param is specified, uses that param as a callsign
       * If two params are specified, uses the first as a type and the second as the name
       *****************************************************************************************************************/
      function __construct() {
          // Initiate Path instance
          $this->FilePathHandler = new FilePathHandler;
          $this->Type = false;

          if ( sizeof(func_get_args()) == 1 )
              $call_sign = func_get_arg(0);
          else if ( sizeof(func_get_args()) == 2 )
              $call_sign = func_get_arg(0) . '.' . func_get_arg(1);
          else
              return false;

          // Set the specified call sign
          $this->CallSign = $call_sign;

          // Split CallSign into Type and Name
          // Determine if it has pre-specified type/tag
          $element_name_parts = preg_split('#[:\.]+#', $this->CallSign);
          if ( sizeof($element_name_parts) > 1 && $this->IsValidType($element_name_parts[0]) ) {
              // Element type is specified and valid
              $this->Type = $this->IsValidType($element_name_parts[0], true);
              unset($element_name_parts[0]);
              $this->Name = join('.', $element_name_parts);
          } else {
              $this->Name = $this->CallSign;
              // Search all possible mock paths to see if any exist
              $mocks = [
                  'theme'=>$this->MockPath('theme'),
                  'version'=>$this->MockPath('version'),
                  'page'=>$this->MockPath('page'),
                  'object'=>$this->MockPath('object'),
                  'auto'=>$this->MockPath('auto')
              ];
              foreach($mocks as $type=>$mock) {
                  if ( file_exists($mock) ) {
                      $this->Type = $type;
                      break;
                  }
              }

          }


          // If Type has not been set an error will display
          if ( !$this->Type ) {
              if ( preg_match('#[\<\>]#i', $call_sign) )
                  return $this;
              echo '<!-- [inst:ui.element][element `'.$this->CallSign.'` not found]] -->';
              $this->Type = '';
          }

          if ( $this->Type == 'theme' )
              $_SESSION['adom.theme.last'] = $this->Name;


          $this->FilePath = $this->MockPath($this->Type);


          return $this;
      }

      public function Location() {
          return $this->FilePath;
      }

      public function Source() {
          if ( !file_exists($this->Location()) ) {
              echo '<!-- [inst:ui.element][element `'.$this->CallSign.'` source not found]] -->';
              return '';
          }
          return file_get_contents($this->FilePath);
      }

      public function Vars($AllStaticOrAuto='all') {
          if ( !file_exists($this->Location()) ) {
              echo '<!-- [inst:ui.element][element `'.$this->CallSign.'` vars not found]] -->';
              return '';
          }

          $regex = '#(\[[\[\~][^~\]]+[~\]]\])#';
          if ( $AllStaticOrAuto == 'auto' )
              $regex = '#(\[~[^~\]]+~\])#';
          if ( $AllStaticOrAuto == 'static' )
              $regex = '#(\[\[[^~\]]+\]\])#';

          $source = $this->Source();
          preg_match_all($regex, $source, $vars);
          return array_unique($vars[0]);
      }

      public function Parse($StaticData) {
          if ( !file_exists($this->Location()) ) {
              echo '<!-- [inst:ui.element][element `'.$this->CallSign.'` cannot parse] -->';
              return '';
          }

          if ( $this->Type == 'theme' )
              return '';

          $all_vars = $this->Vars('static');
          $source = $this->Source();

          $source = $this->ParseAutoUIObjectsWithVars($source);
          $source = $this->ParseAutoUIObjectsWithoutVars($source);

          // process static vars
          if ( is_array($StaticData) ) {
              foreach($StaticData as $var_name=>$var_data) {
                  if ( isset($all_vars['[['.$var_name.']]']) )
                      unset($all_vars['[['.$var_name.']]']);
                  $source = str_replace('[['.$var_name.']]', $var_data, $source);
              }
          }

          // Loop through unused vars and set them to blank
          foreach($all_vars as $unused_var)
              $source = str_replace($unused_var, '', $source);

          return $source;

      }


      /******************************************************************************************************************
       * ParseAutoUI() - Returns string of HTML after processing all Auto UI elements with vars
       * @param $src - String of HTML with potential Auto UI elements to be parsed
       * @return string
       *
       * Example: [~AUTO_UI_ELEMENT_NAME~] {
       *     Var: Value
       * }
       *****************************************************************************************************************/
      private function ParseAutoUIObjectsWithVars($src) {
          preg_match_all('/\[~([^\]]+)~\]\s*(?={)([^}{]+|{((?2)*)})/', $src, $matches);
          $templateNames = $matches[1];
          $templateElements = $matches[3];
          for($i = sizeof($templateElements)-1; $i >= 0; $i--) {
              $match = $templateElements[$i];
              $k = $i;
              if ( preg_match('/\[~([^\]]+)~\]\s*(?={)([^}{]+|{((?2)*)})/', $match) ) {
                  $src = $this->ParseAutoUIObjectsWithVars($match);
              } else {
                  $vars = explode(',', $match);
                  $uivars = array();
                  foreach($vars as $var) {
                      $varparts = explode(':', $var);
                      $uivars[trim($varparts[0])] = trim($varparts[1]);
                  }
                  //echo $templateNames[$k] .'-'. print_r($uivars,1);
                  //print_r($matches[0][$k]); echo "\n\n\n";
                  $src = str_replace($matches[0][$k], autoUI($templateNames[$k], $uivars), $src);
              }
          }
          if ( preg_match('/\[~([^\]]+)~\]\s*(?={)([^}{]+|{((?2)*)})/', $src) ) {
              //echo 'found another dimension of variables';
          }
          return $src;
      }
      /******************************************************************************************************************
       * ParseAutoUIObjectsWithoutVars() - Returns string of HTML after processing all Auto UI elements without vars
       * @param $src - String of HTML with potential Auto UI elements to be parsed
       * @return string
       *
       * Example: [~AUTO_UI_ELEMENT_NAME~]
       *****************************************************************************************************************/
      private function ParseAutoUIObjectsWithoutVars($src) {
          preg_match_all('#\[~([a-zA-Z0-9\-_]+)~\]\s*[^{]#', $src, $allAutoElements);
          if ( @sizeof($allAutoElements[1]) > 0 ) {
              foreach($allAutoElements[1] as $ae)
                  $src = str_replace('[~'.$ae.'~]', autoUI($ae), $src);
          }
          return $src;
      }

      /******************************************************************************************************************
       * MockPath() - True if element file is found, false otherwise
       * @param string $Type - Returns true if this is a valid type false if not
       * @return boolean
       *****************************************************************************************************************/
      public function MockPath($Type) {
          // Use object name to create mock file location paths
          $theme_dir = $this->FilePathHandler->DefaultTheme() . '/';
          $path_rules = [
              'theme'=>[
                  'dir'=>$this->FilePathHandler->Themes(),
                  'ext'=>''
              ],
              'version'=>[
                  'dir'=>$theme_dir,
                  'ext'=>'html'
              ],
              'page'=>[
                  'dir'=>$theme_dir . '/pages/',
                  'ext'=>'html'
              ],
              'object'=>[
                  'dir'=>$theme_dir . '/objects/',
                  'ext'=>'html'
              ],
              'auto'=>[
                  'dir'=>$theme_dir . '/../../autohandlers/',
                  'ext'=>'php'
              ]
          ];

          if ( !array_key_exists($Type, $path_rules) )
              return '[inst:ui.element][mockpath][invalid type]';

          $mock_path = $this->FilePathHandler->Slash(
              $path_rules[$Type]['dir'] . '/' . $this->Name .
              ( ($path_rules[$Type]['ext'] == '') ? '' : '.' . $path_rules[$Type]['ext'] )
          );

          return $mock_path;
      }


      /******************************************************************************************************************
       * IsValidType() - Decides if a given type is valid
       * @param string $Type - Returns true if this is a valid type false if not
       * @param boolean $ReturnTrueType - If true, returns the true type of the element instead of true/false
       * @return mixed
       *****************************************************************************************************************/
      public function IsValidType($Type, $ReturnTrueType=false) {
          $valid_types = [
              'theme'=>[
                  'theme',
                  'template',
                  'tem',
                  't'
              ],
              'version'=>[
                  'version',
                  'vers',
                  'ver',
                  'vs',
                  'v'
              ],
              'page'=>[
                  'page',
                  'pg',
                  'p'
              ],
              'object'=>[
                  'object',
                  'obj',
                  'o'
              ],
              'auto'=>[
                  'auto',
                  'a'
              ]
          ];

          $verified_type = '';
          foreach($valid_types as $true_type=>$type_callsigns) {
              foreach($type_callsigns as $callsign)
                  if ( $Type == $callsign )
                      $verified_type = $true_type;
          }

          if ( $ReturnTrueType )
              return $verified_type;

          return ($verified_type == '') ? false : true;
      }




      function __destruct() {}
  }
}
?>
