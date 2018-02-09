<?php
namespace UI {
  $_SESSION['UI\Path\Cache'] = [];
  class Path {
    function __construct() {}


  /******************************************************************************************************************
   * Get() - Returns either Theme or Element depending on type
   * @param $arg1 - element or theme
   * @param $arg2 - theme or version
   * @return string
   *****************************************************************************************************************/
    public function Get($arg1, $arg2='') {
      if ( (new \UI\Element($arg1))->Type() != 'theme' )
        return $this->Element($arg1, $arg2);

      return $this->Theme($arg1, $arg2);
    }

  /******************************************************************************************************************
   * Theme() - Returns the dir path to the theme if no version is specified, otherwise, the version file location
   * @param $theme - the UI theme to find
   * @param $version - version of the UI theme to find
   * @return string
   *****************************************************************************************************************/
    public function Theme($theme, $version='') {
      $cache_callsign = 'theme:'.$theme.':'.$version;
      if ( isset($_SESSION['UI\Path\Cache'][$cache_callsign]) )
        return $_SESSION['UI\Path\Cache'][$cache_callsign];

      $location = $this->RecursiveThemeSearch($theme);
      if ( $version )
        $location .= '/'.$version.'.html';

      if ( $location ) {
        $_SESSION['UI\Themes'][] = $theme;
        $_SESSION['UI\Path\Cache']['theme:'.$theme.':'.$version] = $location;
      }

      return $location;
    }


    /******************************************************************************************************************
     * Element() - Returns the file path to the specified UI element
     * @param $element - the UI element to find
     * @param $theme - theme in which the UI element is located
     * @return string
     *****************************************************************************************************************/
    public function Element($element, $theme='') {
      $cache_callsign = 'element:'.$element.':'.$theme;
      if ( isset($_SESSION['UI\Path\Cache'][$cache_callsign]) )
        return $_SESSION['UI\Path\Cache'][$cache_callsign];

      $search = $this->RecursiveElementSearch($element, $theme);
      return $search;
    }

    /******************************************************************************************************************
     * RecursiveThemeSearch() - Returns the dir path to the specified theme
     * @param $theme - the UI theme to find
     * @return string
     *****************************************************************************************************************/
    private function RecursiveThemeSearch($theme) {
      $possible_locations = [
        (new \System\Path\Dir)->UI('local'),
        (new \System\Path\Dir)->UI('central')
      ];

      $location = '';
      foreach($possible_locations as $possible_location) {
        $mock = $possible_location . '/dependent/themes/'.$theme;
        if ( file_exists($mock) ) {
          $location = $mock;
          break;
        }
      }

      return $location;
    }

    /******************************************************************************************************************
     * RecursiveElementSearch() - Returns the file path to the specified UI element
     * @param $element - the UI element to find
     * @param $theme - theme in which the UI element is located
     * @return string
     *****************************************************************************************************************/
    private function RecursiveElementSearch($element, $theme='') {
      // Get attr of element
      $ui_element = new \UI\Element($element, $theme);
      $element_dir = $ui_element->Type(true);
      $element_file = $ui_element->File();

      //echo "Element $element has file $element_file <br />\n";


      // List all possible base dirs
      $potential_ui_base_dirs = [
        'default' => (new \System\Path\Dir)->UI(),
        'local'   => (new \System\Path\Dir)->UI('local'),
        'central' => (new \System\Path\Dir)->UI('central')
      ];


      $potential_ui_sub_dirs = ( $ui_element->Type() == 'extension' ) ? [
        '/independent/var/'
      ] :
      [
        '/dependent/themes/'.$theme.'/'.$element_dir,
        '/independent/static/'.$element_dir,
        '/dependent/themes/'.$theme,
        '/independent/static/'
      ];

      foreach($potential_ui_base_dirs as $base_dir) {
        foreach($potential_ui_sub_dirs as $sub_dir) {
            if ( !file_exists($base_dir . $sub_dir) )
              continue;

            $rdi = new \RecursiveDirectoryIterator($base_dir . $sub_dir);
            foreach(new \RecursiveIteratorIterator($rdi) as $file) {
              if ( $this->DoesFileMatchElement($element_file, $file) )
                  return $file;
            }
        }
      }

      // If it's not been found and it's an extension, try again as a static object using the same name
      if ( $ui_element->Type() == 'extension' ) {
        $session_index = 'UI\Extensions\Data\\'.SYS_UI_AUTO_PARSER;
        if ( !isset($_SESSION[$session_index]) )
          $_SESSION[$session_index] = [];
        $_SESSION[$session_index]['_element'] = $ui_element->Name();
        return $this->RecursiveElementSearch('extensions/'.SYS_UI_AUTO_PARSER); //return '';//$this->RecursiveElementSearch('objects/' . $ui_element->Name() , @end($_SESSION['UI\Themes']));
      }



      return '';
    } // End RecursiveElementSearch

    private function DoesFileMatchElement($element_file_name, $current_file_path) {
      $element_file_name = str_replace('\\', '/', $element_file_name);
      $current_file_path = str_replace('\\', '/', $current_file_path);

      $is_path_identical = ( basename($current_file_path) == $element_file_name );
      $is_element_in_path = ( strrpos($current_file_path, $element_file_name) + strlen($element_file_name) == strlen($current_file_path) );

      //echo "Comparing $element_file_name to $current_file_path <br />\n";
      return ( $is_path_identical || $is_element_in_path );
    }


    function __destruct() {}
  }
}
?>
