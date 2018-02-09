<?php
namespace System {
  class Log {
      protected $Log = [];
      function __construct($input='') {
        if ( $input )
          $this->Write($input);
      }
      public function Write($input) {
        //echo 'Log to '.$this->StoragePath();
        $LogFileResource = fopen($this->StoragePath(), 'wb');
        fwrite($LogFileResource, $this->Output($input));
        fclose($LogFileResource);

        return $this;
      }

      private function Output($input) {
        return
          "    [Request URI: ".$_SERVER['REQUEST_URI']."]    \r\n".
          "    [Date / Time: ".date('M j, Y g:ia')."]        \r\n".
          "    [Begin Log Input]                             \r\n".
          "      $input                                      \r\n".
          "    [ END Log Input ]                             \r\n";
      }
      private function StoragePath() {
        return (new \System\Path\Dir)->Log() . '/' . $this->StorageFile();
      }

      private function StorageFile($extension='log') {
        $LogFileName = strtoupper(date('M_j_Y_g.ia_')).
                       time().'_'.md5(  microtime() . rand(  0,time()  )  );
        return $LogFileName . '.' . $extension;
      }
  }
}
?>
