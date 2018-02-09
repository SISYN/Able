<?php
namespace UI {
  class Builder {
      private $Controller;
      private $Theme, $Version;
      function __construct() {}

      public function Theme($theme, $version='') {
        $this->Theme = $theme;
        if ( $version || !$this->Version )
          $this->Version = $version;

        return $this->UpdateController();
      }
      public function Version($version) {
        $this->Version = $version;
        return $this->UpdateController();
      }

      public function Title($title) {
        return $this->Set('page.title', $title);
      }

      /******************************************************************************************************************
       * Headers() - Sets the page headers if using CompiledOutput()
       * @param mixed $headers, [...] - Headers to set
       * @return Object
       *****************************************************************************************************************/
      public function Headers() {
        // Check which format is being used:
        // Headers('file.css') or Headers('file.css', 'file.js') or Headers(['file.css']) or Headers('<style>raw code</style>')
        $this->Headers = [];
        foreach(func_get_args() as $param) {
          if ( !is_array($param) ) {
            $this->ParseHeader($param);
            continue;
          }
          foreach($param as $header)
            $this->ParseHeader($header);
        }


        $header_markup = "<!-- Additional headers -->\n    " . join("\n    ", $this->Headers);
        $this->Set('page.headers', $header_markup);

        return $this;
      }

    /******************************************************************************************************************
     * ParseHeader() - Processes a single header item, whether a file or raw source
     * @param string $header_item
     * @return Object
     *****************************************************************************************************************/
      private function ParseHeader($header_item) {
        // Check if its a known header file extension (maybe add favicon support later?)
        if ( preg_match('#^.+\.js|css$#', $header_item) )
          $this->Headers[] = $this->ParseHeaderFile($header_item);
        else
          $this->Headers[] = $header_item; // Treat it as raw code if its not a file

        return $this;
      }


    /******************************************************************************************************************
     * ParseHeaderFile() - Gets the source of a single header file
     * @param string $file
     * @return Object
     *****************************************************************************************************************/
      private function ParseHeaderFile($file) {
        // Check if its an absolute path, if so, don't modify it; otherwise get its theme path and dir
        $path = $file;
        if ( !strstr($file, '/') )
          $path = (new \UI\Extension('path.theme'))->Invoke() . '/assets/'.  end((explode('.', $file))) . '/'.$file;

        if ( end((explode('.', $file))) == 'js' )
          return '<script src="'.$path.'"></script>';
        if ( end((explode('.', $file))) == 'css' )
          return '<link href="'.$path.'" rel="stylesheet" />';

        return '<!-- Unknown header file extension for `'.$path.'`. -->';
      }



      public function Body($body) {
        return $this->Set('page.body', $body);
      }

      public function Crumbs($crumbs) {
        $crumb_html = [];
        foreach($crumbs as $crumb_title=>$crumb_link)
          $crumb_html[] = (is_string($crumb_title)) ? '<a href="'.$crumb_link.'">'.$crumb_title.'</a>' : $crumb_link;
        return $this->Set('page.crumbs', join(' / ', $crumb_html));
      }

      public function Set($arg1,$arg2='') {
        if ( is_array($arg1) ) {
          foreach($arg1 as $key=>$val)
            $this->Controller->Set($key, $val);

          return $this;
        }


        $this->Controller->Set($arg1, $arg2);
        return $this;
      }

      public function Output() {
        return $this->Controller->Output();
      }

      private function UpdateController() {
        $this->Controller = new \UI\Controller($this->Theme, $this->Version);
        return $this;
      }

      function __destruct() {}
  }
}
?>
