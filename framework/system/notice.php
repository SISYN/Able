<?php
namespace System {
  $_SESSION['System.Notice.Index'] = 0;
  class Notice {
      protected $Log = [];
      function __construct($message, $log=false) {

        if ( $log )
          new Log($message);

        // Format `` to <pre inline>
        $message = preg_replace('#`([^`]+)`#', '<pre style="display: inline;">$1</pre>', $message);

        // Format and print message
        if ( $_SESSION['System.Notice.Index'] == 0 )
          echo '<div style="background: red; color: white; font-size: 14px; padding: 5px 8px;">System error(s) encountered</div>';


        $_SESSION['System.Notice.Index']++;

        echo "<div style=\"font-size: 12px; padding: 5px;\">".$_SESSION['System.Notice.Index'].". $message</div>\n";

      }
  }
}
?>
