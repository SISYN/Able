<?php
  $full_path = (new \System\Path\Dir)->UI('local') . '/dependent/themes/' . end($_SESSION['UI\Themes']);
  $full_path = str_replace('\\', '/', $full_path);
  $full_path = str_replace(able_local_path(), '', $full_path);
  echo $full_path;
?>
