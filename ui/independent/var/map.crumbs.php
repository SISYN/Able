<?php
  $ext = new \UI\Extension(__FILE__);
  $data = $ext->Data();


  $map = new SiteMap;
  $demo_uri = $map->URI('app/demo');
  $crumbs = $map->Attr('title', $_SERVER['REQUEST_URI'], true, true);

  $links = [];
  $split_uri = array_filter(explode('/',$_SERVER['REQUEST_URI']));
  array_unshift($split_uri, '');
  $split_uri = array_reverse($split_uri);
  foreach($split_uri as $k=>$uri) {
    $uri = $_SERVER['REQUEST_URI'];
    for($i = 0; $i < $k; $i++)
        $uri = dirname($uri);
    $extra = ($k == 0) ? ' style="text-decoration: none !important; cursor: text !important;" ' : '';
    $links[] = '<a href="/' . trim(trim($uri, '/'), '\\') . '"'.$extra.'>'.$crumbs[$k].'</a>';
  }

  $links = array_filter(array_reverse($links));

  if ( sizeof($links) > 1 )
    echo '
        <div class="container" id="crumbs--top">
          <p><small>'.join(' / ', $links).'</small></p>
        </div>
      ';


?>
