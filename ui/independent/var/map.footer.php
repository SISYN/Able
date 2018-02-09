<?php
  $ext = new \UI\Extension(__FILE__);
  $data = $ext->Data();

  $url = trim($_SERVER['REQUEST_URI'], '/');

  $map = new SiteMap(true);
  $uri = $map->URI($url);
  $_footer = $map->Attr('footer', $uri);

  echo UI($_footer);



?>
