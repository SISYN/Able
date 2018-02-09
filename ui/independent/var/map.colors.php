<?php
  $ext = new \UI\Extension(__FILE__);
  $data = $ext->Data();

  $url = trim($_SERVER['REQUEST_URI'], '/');

  $map = new SiteMap(true);
  $uri = $map->URI($url);
  $_color = $map->Attr('color', $uri);







  $_color = preg_replace('#^\#+#', '', $_color);


  $darkness_factor = 0.75;
  $lightness_factor = 1.50;


  $_rgb    =  hex_to_rgb($_color);
  $_text   =  'ffffff';//hex_contrast($_color);
  $_hsl    =  rgb_to_hsl($_rgb['red'] , $_rgb['green'] , $_rgb['blue']);

  $hsl_light = 'hsl( '.$_hsl['hue'].' , '.$_hsl['saturation'].'% , '.round($_hsl['lightness'] * $lightness_factor).'% )';
  $hsl_dark  = 'hsl( '.$_hsl['hue'].' , '.$_hsl['saturation'].'% , '.round($_hsl['lightness'] * $darkness_factor).'% )';

  echo '
    <meta name="theme-color" content="#'.$_color.'">

    <style type="text/css">

      ._bg ,
      ._highlight--bg { background: #'.$_color.'; }

      ._text , ._text:hover ,
      ._highlight--text { color: #'.$_text.'; }

      ._text--bg ,
      ._highlight--text--bg { color: #'.$_color.'; }

      ._gradient ,
      ._highlight--gradient { background: linear-gradient( 315deg , '.$hsl_light.' 0% , '.$hsl_dark.' ); }

      ._border ,
      ._highlight--border { border-color: #'.$_color.' !important; }

      ._border--hover:hover ,
      ._highlight--border--hover:hover { border-color: #'.$_color.' !important; }

    </style>

  ';
?>
