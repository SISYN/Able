<?php
  $_ = new UI\Extension(__FILE__);
  $_data = $_->Data();
  $_element = $_data['_element'];

  $Extension_Object = new \UI\Extension('extensions/'.$_element);
  $Extension_Data = $Extension_Object->Data();


  echo trim(UI('objects/'.$_element, $Extension_Data));

?>
