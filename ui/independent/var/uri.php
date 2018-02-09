<?php
  $ext = new \UI\Extension(__FILE__);
  $data = $ext->Data();

  /*
  $map = new SiteMap;
  $uri = $map->URI($data['uri']);
  */

  $URI = new URI($data['uri']);
  $_uri = $URI->Get();

  echo $_uri;
?>
