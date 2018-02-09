<?php
namespace UI {
  class Parser {
    private $Source, $Vars;
    function __construct($source='') {
      $this->Source = $source;
    }



    public function Source($source) {
      $this->Source = $source;
      return $this;
    }


    public function Data($data) {
      $this->Data = $data;
      return $this;
    }

    public function Output() {
      $this->Parse();
      return $this->Source;
    }


    private function Parse() {
      $this->ParseExtensionsVars();
      $this->ParseStaticVars();
      return $this;
    }

    private function Vars($AllStaticOrAuto='all') {
        $regex = '#(\[[\[\~][^~\]]+[~\]]\])#';
        if ( $AllStaticOrAuto == 'auto' )
            $regex = '#(\[~[^~\]]+~\])#';
        if ( $AllStaticOrAuto == 'static' )
            $regex = '#(\[\[[^~\]]+\]\])#';

        preg_match_all($regex, $this->Source, $vars);
        return array_unique($vars[0]);
    }


    /******************************************************************************************************************
     * ParseStaticVars() - Parses all static vars into their appropriate value or an empty string if unspecified
     * @return object
     *
     * Example: [[var_name]]
     *****************************************************************************************************************/
    private function ParseStaticVars() {
      foreach($this->Vars('static') as $var) {
        $var_name = str_replace(['[[',']]'], '', $var);
        $replacement = ( isset($this->Data[$var_name]) ) ? $this->Data[$var_name] : '';
        $this->Source( str_replace($var, $replacement, $this->Source) );
      }
      return $this;
    }


    /******************************************************************************************************************
     * ParseExtensionsVars() - Parses all auto vars into their appropriate value or an empty string if unspecified
     * @return object
     *
     * Example: [~AUTO~] or [~AUTO~] { var: something }
     *****************************************************************************************************************/
    private function ParseExtensionsVars() {
      $this->Source = $this->ParseExtensionsWithVars($this->Source);
      $this->Source = $this->ParseExtensionsWithoutVars($this->Source);
      return $this;
    }

    /******************************************************************************************************************
     * ParseExtensionsWithVars() - Parses all Auto UI elements with variables
     * @return object
     *
     * Example: [~AUTO_UI_ELEMENT_NAME~] {
     *     Var: Value
     * }
     *****************************************************************************************************************/
    private function ParseExtensionsWithVars($source) {
        preg_match_all('/\[~([^\]]+)~\]\s*(?={)([^}{]+|{((?2)*)})/', $source, $AutoElements);
        $AutoElementNames = $AutoElements[1];
        $AutoElementAttributes = $AutoElements[3];
        for($i = sizeof($AutoElementNames)-1; $i >= 0; $i--) {
            $attr = $AutoElementAttributes[$i];
            $k = $i;
            // Check if one of its attributes contains another Auto UI element
            if ( preg_match('/\[~([^\]]+)~\]\s*(?={)([^}{]+|{((?2)*)})/', $attr) ) {
                $source = $this->ParseExtensionsWithVars($attr);
                continue;
            }

            $vars = explode(',', $attr);
            $uivars = [];
            foreach($vars as $var) {
                $varparts = explode(':', $var);
                $uivars[trim($varparts[0])] = trim($varparts[1]);
            }
            $ExtensionCallsign = 'ext.'.$AutoElements[1][$k];
            $ExtensionOutput = (new \UI\Extension($ExtensionCallsign))->Invoke($uivars);
            $source = str_replace($AutoElements[0][$k], $ExtensionOutput, $source);
        }
        if ( preg_match('/\[~([^\]]+)~\]\s*(?={)([^}{]+|{((?2)*)})/', $source) ) {
            echo "Experimental multi-dimensional variables in use. <br />\n";
            $source = $this->ParseExtensionsVars($source);
        }

        return $source;
    }
    /******************************************************************************************************************
     * ParseExtensionsWithoutVars() - Parses all Auto UI elements without vars
     * @return object
     *
     * Example: [~AUTO_UI_ELEMENT_NAME~]
     *****************************************************************************************************************/
    private function ParseExtensionsWithoutVars($source) {
        preg_match_all('#\[~([^~]+)~\]\s*[^{]#', $source, $allAutoElements);
        if ( @sizeof($allAutoElements[1]) > 0 ) {
            foreach($allAutoElements[1] as $ExtensionName) {
              $ExtensionCallsign = 'extensions/'.$ExtensionName;
              $ExtensionOutput = (new \UI\Extension($ExtensionCallsign))->Invoke();
              $source = str_replace('[~'.$ExtensionName.'~]', $ExtensionOutput, $source);
            }
        }

        return $source;
    }

    function __destruct() {}
  }
}
?>
