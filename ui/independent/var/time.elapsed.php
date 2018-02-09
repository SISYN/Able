<?php
  $Extension = new UI\Extension(__FILE__);
  //$ExtensionData = $Extension->Data();
  echo $Extension->Data('prefix');
  echo elapsed_execution_time();
  echo $Extension->Data('suffix');
?>
