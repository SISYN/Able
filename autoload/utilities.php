<?php
/******************************************************************************************************************
 * Able / autoload / utilities.php
 * Defines basic utility functions
 *****************************************************************************************************************/


 /******************************************************************************************************************
  * arr_return() - Returns the specified index to allow shorthand array referencing
  * @param string $_str - The input string you wish to manipulate
  * @param bool $_remove_leading_slash (default = false) - Set to true if you want leading slashes removed
  * @return string
  *
  * Not used by default
  *****************************************************************************************************************/
  function single_slash($_str, $_remove_leading_slash = false) {
    $_str = preg_replace('#(\/|\\\)+#', '$1', $_str);
    if ( $_remove_leading_slash )
    return trim($_str, '/');

    return $_str;
  }

 /******************************************************************************************************************
  * arr_parent() - Returns the index of the parent array in a 2 dimension search
  * @param array $array - Array from which to fetch
  * @param int $index - Index to fetch
  * @return mixed
  *
  * $arr = [
  *   'parent_1' => [
  *     'id' => 1 ,
  *     'name' => 'Dan' ,
  *     'school' => 'UNCW'
  *   ],
  *   'parent_2' => [
  *     'id' => 2 ,
  *     'name' => 'Brian' ,
  *     'school' => 'Elon'
  *   ],
  *   'parent_3' => [
  *     'id' => 3 ,
  *     'name' => 'Frank' ,
  *     'school' => 'UVA'
  *   ]
  * ];
  *
  *
  * $alumni_index = array_parent()
  *
  *
  * Used in:
  * framework/ui/element.php
  *****************************************************************************************************************/
function arr_parent($needle, $parent) {
  foreach($parent as $index=>$sub) {
    foreach($sub as $i=>$child) {
      if ( $child == $needle )
        return $index;
    }
  }

  return false;
} function array_parent($needle, $parent) { return arr_parent($needle,$parent); }


/******************************************************************************************************************
 * arr_return() - Returns the specified index to allow shorthand array referencing
 * @param array $array - Array from which to fetch
 * @param int $index - Index to fetch
 * @return mixed
 *
 * $first_name = arr_return(explode(' ', 'Dan Lindsey'), 1);
 *
 *
 * Not used by default
 *****************************************************************************************************************/
function arr_return($array, $index=0) {
    if ( !isset($array[$index]) )
        return false;
    return $array[$index];
} function array_return($array,$index=0) { return arr_return($array,$index); } // Alias




 /******************************************************************************************************************
  * arr_search() - Returns the index of the needle in the haystack (multidimensional works too!)
  * @param mixed $needle - If string, used as value. If arrray, used as ['supplied_field' => 'expected_value']
  * @param array $haystack - The array to be searched
  * @return mixed
  *
  * $arr = [
  *   'parent_1' => [
  *     'id' => 1 ,
  *     'name' => 'Dan' ,
  *     'school' => 'UNCW'
  *   ],
  *   'parent_2' => [
  *     'id' => 2 ,
  *     'name' => 'Brian' ,
  *     'school' => 'Elon'
  *   ],
  *   'parent_3' => [
  *     'id' => 3 ,
  *     'name' => 'Frank' ,
  *     'school' => 'UVA'
  *   ]
  * ];
  *
  *
  * $alumni = arr_search(['name'=>'Dan'], $arr);
  * echo $alumni; // Outputs "Dan"
  *
  *
  * Not used by default
  *****************************************************************************************************************/
function arr_search($needle, $haystack, $index_dimension=0 , $index_chain=[]) {
  $index = false;
  foreach($haystack as $haystack_index => $haystack_item) {

    if ( $haystack_item == $needle )
      $index = $haystack_index;
    if ( is_array($needle) && isset($needle[$haystack_index]) && $needle[$haystack_index] == $haystack_item )
      $index = $haystack_index;
    if (  $index === false && is_array($haystack_item) )
      $index = arr_search($needle, $haystack_item, $index_dimension, arr_join($index_chain, [$haystack_index]));

  }

  if ( !sizeof($index_chain) )
    $index_chain[] = $index;

  return $index_chain[$index_dimension];
}


/******************************************************************************************************************
 * arr_join() - Combines two potential arrays with type conflict fallbacks
 * @param array $arr1 - First item to combine
 * @param array $arr2 - Second item to combine
 * @param bool  $unique - Set to true if you want to remove duplicate items from combined array
 * @param array $fallback - The array to use as a default if one of the two $arr1/$arr2 items wasn't an array
 * @return array
 *
 * $customer = arr_join(
 *  ['name'=>'John', 'phone'=>'555 555 5555'],
 *  ['address'=>'123 NYC Ave', 'phone'=>'555 867 5309']
 * );
 *
 * $customer = ['name'=>'John', 'phone'=>'555 867 5309', 'address'=>'123 NYC Ave'];
 *
 *
 * Used in:
 * config/* (base and other constant loader files)
 *****************************************************************************************************************/
function arr_join($arr1, $arr2, $unique=false, $fallback=[]) {
  if ( !is_array($fallback) )
    $fallback = [];

  if ( !is_array($arr1) )
    $arr1 = $fallback;

  if ( !is_array($arr2) )
    $arr2 = $fallback;

  $merged = array_merge($arr1, $arr2);
  if ( !$unique )
    return $merged;

  return array_unique($merged);
}

/******************************************************************************************************************
 * arr_join_unique() - Shorthand for the arr_join with unique flag preset to true
 * @param array $arr1 - First item to combine
 * @param array $arr2 - Second item to combine
 * @param array $fallback - The array to use as a default if one of the two $arr1/$arr2 items wasn't an array
 * @return array
 *
 *
 * Used in:
 * config/* (base and other constant loader files)
 *****************************************************************************************************************/
function arr_join_unique($arr1, $arr2, $fallback=[]) {
  return arr_join($arr1, $arr2, true, $fallback);
} function array_join_unique($arr1, $arr2, $fallback=[]) { return arr_join_unique($arr1, $arr2, $fallback); } // Alias








/******************************************************************************************************************
 * arr_backtrace() - Returns an array of sequential keys required to get to the desired needle
 * @param mixed $needle - The target (can be string or array keyvalue pair)
 * @param array $haystack - The array to search in
 * @param array &$index - The backtrace array that keeps track of recursive indexes
 * @return array
 *
 *
 * Used in:
 * URI Mapping
 *
 *
 * Example :
 *
  $arr = [
    'parent_0' => [
      'labels' => ['parent 0', 'Parent 0'],
      'child_0_0' => ['labels' => ['child 0 0', 'Child 0 0']],
      'child_0_1' => ['labels' => ['child 0 1', 'Child 0 1']],
      'child_0_2' => ['labels' => ['child 0 2', 'Child 0 2']]
    ],
    'parent_1' => [
      'labels' => ['parent 1', 'Parent 1'],
      'child_1_0' => ['labels' => ['child 1 0', 'Child 1 0']],
      'child_1_1' => ['labels' => ['child 1 1', 'Child 1 1']],
      'child_1_2' => ['labels' => ['child 1 2', 'Child 1 2']]
    ],
    'parent_2' => [
      'labels' => ['parent 2', 'Parent 2'],
      'child_2_0' => ['labels' => ['child 2 0', 'Child 2 0']],
      'child_2_1' => ['labels' => ['child 2 1', 'Child 2 1']],
      'child_2_2' => [
          'labels' => ['child 2 2', 'Child 2 2'],
          'grandchild_2_0' => [ 'labels' => ['Grandchild', 'grandchild'] ]
      ]
    ]
  ];

  arr_backtrace(['labels'=>'Grandchild']) == [

      [0] => parent_2
      [1] => child_2_2
      [2] => grandchild_2_0
      [3] => labels
      [4] => 0

  ]
 *
 *****************************************************************************************************************/
function arr_backtrace($needle, $haystack, $lineage=[], &$index=[]) { // Index is an array with a sequential list of all parental keys
$self = __FUNCTION__;

  foreach($haystack as $key => $value) {

    // Copy the current index before modifying it for this round
    $index_copy = $index;
    // Add this key as an index in case the result is found here
    $index[] = $key;

    // Check for $arr[key] == $needle
    if ( $value == $needle ) {
      // result found
      $index[] = $key;
      return $index;
    }

    $continue_recursion = true;
    // Check for $needle[key] == $haystack[key]
    if ( isset($needle[$key]) && $needle[$key] == $value ) {
      // result found
      // Do not do $index[] = $key as adding the key here creates a duplicate of the final key
      // return $index;
      $continue_recursion = false;
    }

    // Check for $needle[$key] in $haystack[$key][]
    if ( isset($needle[$key]) ) {
      if ( is_array($haystack[$key]) && ($found = array_search($needle[$key], $haystack[$key])) !== false ) {
        // result found
        $index[] = $found;
        $continue_recursion = false;
        //return $index;
      }
    }

    if ( !$continue_recursion ) {
      // Check for parental lineage requirements
      if ( !is_array($lineage) )
        $lineage = [$lineage];

      if ( sizeof($lineage) ) {
        $lineage = array_values($lineage);
        for($lineage_i = 0; $lineage_i < sizeof($lineage); $lineage_i++) {
          if ( !in_array($lineage[$lineage_i], $index, true) )
            return [];
        }
      }

      // Since recursion cannot go farther, return the current index stack
      return $index;
    }


    // Its not the result, but check if it has children with possible matches
    if ( is_array($value) && ($results = $self($needle, $value, $lineage, $index)) != [] ) {
      return $results;
    }

    // If we got down here we know it wasn't found, so lets remove this last index key
    $index = $index_copy;

  }

  return [];

}












/******************************************************************************************************************
 * is_json() - Returns true if the $input type is JSON string
 * @param string $input - The string you wish to validate as JSON
 * @return bool
 *
 *
 * Not used by default
 *****************************************************************************************************************/
function is_json($input) {
    $decoded = json_decode($input, 1);
    if ( is_array($decoded) && json_last_error() == JSON_ERROR_NONE )
        return true;

    return false;
}




/******************************************************************************************************************
 * is_json() - Similar to the SQL LIKE operator
 * @param string $needle - The substr you wish to find
 * @param string $haystack - The parent string containing the substr
 * @return bool
 *
 *
 * if ( like_string('Hey Arn', 'Hey Arnold!') )
 *  echo 'Nick was great in the 90s';
 *
 *
 * Not used by default
 *****************************************************************************************************************/
function like_string($needle, $haystack) {
    $needle = strtolower($needle);
    $haystack = strtolower($haystack);
    $needle = str_replace('%', '.*', preg_quote($needle, '/'));
    return (bool) preg_match("/^{$needle}$/i", $haystack);
}


/******************************************************************************************************************
 * ucpreg() - Similar to ucwords but allows you to use a pcre regex to denote separator(s)
 * @param string $input - The input string you wish to start with
 * @param string $regex - The regular expression you wish to split the word list with
 * @return bool
 *
 *
 * if ( like_string('Hey Arn', 'Hey Arnold!') )
 *  echo 'Nick was great in the 90s';
 *
 *
 * Not used by default
 *****************************************************************************************************************/
function ucpreg($input, $regex) {
    return preg_replace_callback('#\-([a-z])#', function($matches) {
        return strtoupper($matches[0]);
    }, $input_string);
}

// Used to make textual URLs look better
function pretty_string($str) {
    // Remove harmful or ugly characters
    $str = str_replace('&amp;', 'and', $str);
    $str = preg_replace(
        '/[^a-z0-9\-\_\s\|]/i',
        '', $str
    );
    $str = force_single_spaces($str);
    $str = str_replace(' ', '-', $str);
    $str = preg_replace('#\-+#', '-', $str);
    // Consolidate spaces and exchange for hyphens
    return $str;
}

function currency_format($val, $includeCurrency=true, $currency=-1) {
    // copy over currency code from Merkd localhost on Blue Monster
    $retVal = ( $includeCurrency ) ? '$' : '';
    return $retVal.number_format((float)$val, 2, '.', '');
}


function force_array($var) {
    return (is_array($var))?$var:[];
}

function file_extension($file) {
  return preg_replace('#^.*\.([^\.]+)#i', '$1', $file);
}

// Add ability to make space limit variable and trim variable
function limit_space($str) {
    // Removes all extraneous whitespace from input - anymore than one space becomes single space, more than 2 \n become \n\n
    return trim(preg_replace('/(\r\n|\n|\r){3,}/', "$1$1", preg_replace('/( )+/', ' ', $str)));
}

/******************************************************************************************************************
 * ajax() - Returns true if request is sent using ajax
 * @return bool
 *****************************************************************************************************************/
function ajax() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
}
/******************************************************************************************************************
 * array_quotes() - Outputs quotes around the input array items
 * @param array $arr - Array you wish to quote
 * @return array
 *****************************************************************************************************************/
function array_quotes($arr) {
    foreach($arr as $key=>$val) {
        if ( is_array($val) )
            $val = array_quotes($val);

        // It's not an array, check its type
        if ( is_string($val) ) {
            // It's a string, now check if it's got ending and beginning quotes
            if ( !preg_match('#^\'#', $val) )
                $val = '\''.$val;
            if ( !preg_match('#\'$#', $val) )
                $val .= '\'';
        }

        // Reassign the changes to the original array
        $arr[$key] = $val;
    } // End foreach($arr)

    return $arr;
}
/******************************************************************************************************************
 * force_instance() - Outputs an object file with appropriate data, or a UI Object Instance
 * @param mixed &$pointer - File or directory path you wish to sanitize
 * @param string $type - The type of instance the pointer must be
 * @return mixed
 *****************************************************************************************************************/
function force_instance(&$pointer, $type) {
    if ( !($pointer instanceof $type) )
        $pointer = new $type();

    return $pointer;
}
/******************************************************************************************************************
 * clean_path() - Ensures that the supplied file path is properly formatted (does not verify if it exists)
 * @param string $path - File or directory path you wish to sanitize
 * @param bool $append - If true, will end string with / otherwise will remove any / from end of path
 * @return string
 *****************************************************************************************************************/
function clean_path($path, $append=true) {
    $path = preg_replace('/\/+/i', '/', $path);
    $path = preg_replace('#(.*)/$#i', '$1', $path);
    if ( $append )
        $path .= '/';

    return $path;
}
function sanitize_path($path, $append=true) {
    return clean_path($path, $append);
}
function parse_path($path, $append=true) {
    return clean_path($path, $append);
}


/******************************************************************************************************************
 * adom_hash() - Hashes a string of data (safe for URLs)
 * @param mixed $data - String of data to hash
 * @return string
 *****************************************************************************************************************/
function adom_hash($data) {
    return sha1(md5(sha1(sha1(sha1(md5(md5(sha1($data))))))));
} function able_hash($data) { return adom_hash($data); }
/******************************************************************************************************************
 * encode_string() - Encodes a string of data (safe for URLs)
 * @param string $str - String of data to encode
 * @return string
 *****************************************************************************************************************/
function encode_string($str) {
    return str_replace(array('+', '/', '='), array(',', '_', '-'), base64_encode(gzcompress($str, 9)));
}
/******************************************************************************************************************
 * decode_string() - Decodes a string of data from encode_string()
 * @param string $str - String of data to decode
 * @return string
 *****************************************************************************************************************/
function decode_string($str) {
    $str = str_replace(array(',', '_', '-'), array('+', '/', '='), $str);
    return gzuncompress(base64_decode($str));
}
/******************************************************************************************************************
 * valid_email() - Returns true if supplied with a valid email address, false otherwise.
 * @param string $email - Email address you wish to validate
 * @return bool
 *****************************************************************************************************************/
function valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $email);
} function is_email($input) { return valid_email($input); }
/******************************************************************************************************************
 * elapsed_execution_time() - Measures the amount of time passed since the page was first requested
 * @return string
 *****************************************************************************************************************/
function elapsed_execution_time() {
    return microtime(true) - ABLE_INITIAL_MICROTIME;
}
/******************************************************************************************************************
 * compress_int() - Compresses the supplied integer into a string representation
 * @param int $int - The integer with which to begin
 * @return string
 *****************************************************************************************************************/
function compress_int($int) {
    return alphanumerical_compression($int, false, false, null);
}
/******************************************************************************************************************
 * decompress_int() - Decompresses the supplied string representation into its original integer value
 * @param string $str - The string representation with which to begin
 * @return int
 *****************************************************************************************************************/
function decompress_int($str) {
    return alphanumerical_compression($str, true, false, null);
}
/******************************************************************************************************************
 * alphanumerical_compression() - Turns an int into a shortened string representation or vise-versa.
 * @param string $in - Integer or string with which to begin
 * @param bool $to_num - If true, will convert a given string value back into its original integer value
 * @param bool $pad_up - The least number of characters allowed to be returned (if $to_num=false)
 * @param bool $pass_key - If supplied, will use this as a password to further encrypt the underlying integer
 * @return mixed - Returns string for $to_num=false and int for $to_num=true
 *
 * Note: Although this function's original intent was to simply make the ID short (not so much secure),
 * with this patch by Simon Franz (http://blog.snaky.org/) you can optionally supply a password to make
 * it harder to calculate the corresponding numerical value.
 *****************************************************************************************************************/
function alphanumerical_compression($in, $to_num = false, $pad_up = false, $pass_key = null) {
    $out   =   '';
    $index = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
    $base  = strlen($index);

    if ($pass_key !== null) {
        // Although this function's purpose is to just make the
        // ID short - and not so much secure,
        // with this patch by Simon Franz (http://blog.snaky.org/)
        // you can optionally supply a password to make it harder
        // to calculate the corresponding numeric ID

        for ($n = 0; $n < strlen($index); $n++) {
            $i[] = substr($index, $n, 1);
        }

        $pass_hash = hash('sha256',$pass_key);
        $pass_hash = (strlen($pass_hash) < strlen($index) ? hash('sha512', $pass_key) : $pass_hash);

        for ($n = 0; $n < strlen($index); $n++) {
            $p[] =  substr($pass_hash, $n, 1);
        }

        array_multisort($p, SORT_DESC, $i);
        $index = implode($i);
    }

    if ($to_num) {
        // Digital number  <<--  alphabet letter code
        $len = strlen($in) - 1;

        for ($t = $len; $t >= 0; $t--) {
            $bcp = bcpow($base, $len - $t);
            $out = $out + strpos($index, substr($in, $t, 1)) * $bcp;
        }

        if (is_numeric($pad_up)) {
            $pad_up--;

            if ($pad_up > 0) {
                $out -= pow($base, $pad_up);
            }
        }
    } else {
        // Digital number  -->>  alphabet letter code
        if (is_numeric($pad_up)) {
            $pad_up--;

            if ($pad_up > 0) {
                $in += pow($base, $pad_up);
            }
        }

        for ($t = ($in != 0 ? floor(log($in, $base)) : 0); $t >= 0; $t--) {
            $bcp = bcpow($base, $t);
            $a   = floor($in / $bcp) % $base;
            $out = $out . substr($index, $a, 1);
            $in  = $in - ($a * $bcp);
        }
    }

    return $out;
}








// returns white/black for contrasting color depending on perceptive luminance of hex
function hex_contrast($_hex) {
  $rgb = hex_to_rgb(hex_trim($_hex));
  $lum = [ // Standardized perceptive luminance for humans
    'red' => 0.299 ,
    'green' => 0.587 ,
    'blue' => 0.114
  ];
  $d = 0;
  $a = 1 - ( $lum['red'] * $rgb['red'] + $lum['green'] * $rgb['green'] + $lum['blue'] * $rgb['blue'] ) / 255;
  $contrast = ( $a < 0.5 ) ? 0 : 255;

  return rgb_to_hex($contrast, $contrast, $contrast);
}

// convert rgb into hex
function rgb_to_hex($_red, $_green, $_blue) {
  return dechex($_red) . dechex($_green) . dechex($_blue);
}

// remove # from hex
function hex_trim($_hex) {
  return preg_replace('#[^A-Z0-9]#i', '', $_hex);
}





// Convert hex color into rgb array
function hex_to_rgb($_hex) {
  // Strip non-alphanumeric chars
  $hex = hex_trim($_hex);
  // Force it to be 6 chars
  if ( strlen( $hex ) == 3 )
    $hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];

  preg_match_all('#[A-Z0-9]{2}#i', $hex, $hex_pairs);

  $rgb_values = [];
  foreach($hex_pairs[0] as $hex_pair)
    $rgb_values[] = hexdec($hex_pair);

  list($red , $green , $blue) = $rgb_values;
  return ['red'=>$red , 'green'=>$green, 'blue'=>$blue];
}


/******************************************************************************************************************
 * hex_shade() - Lightens or darkens a hex value
 * @param string $_hex - The input string you wish to start with
 * @param float $_percent - Percent you wish to change the hex
 * @param bool $_prefix - True if you want it returned with a leading #
 * @return string
 *
 *
 * Not used by default
 *****************************************************************************************************************/


function rgb_to_hsl( $_red , $_green , $_blue , $_return = '*' ) {
  // $_hue , $_saturation , $_lightness will be used in return value

  $_new = [
    'red' => $_red / 255,
    'green' => $_green / 255,
    'blue'  => $_blue / 255
  ];

  $max = max($_new['red'] , $_new['green'], $_new['blue']);
  $min = min($_new['red'] , $_new['green'], $_new['blue']);
  $difference = $max - $min;

  $_lightness = ( $max + $min ) / 2;

  if ( $difference == 0 ) { // Achromatic
    $_hue = $_saturation = 0;
  } else {
    $_saturation = $difference / ( 1 - abs( 2 * $_lightness - 1 ) );
    switch( $max ) {
      case $_new['red'] :
        $_hue = 60 * fmod( ( ( $_new['green'] - $_new['blue'] ) / $difference ), 6 ) + ($_new['blue'] > $_new['green'] ? 360 : 0);
        break;
      case $_new['green'] :
        $_hue = 60 * ( ( $_new['blue'] - $_new['red'] ) / $difference + 2 );
        break;
      case $_new['blue']  :
        $_hue = 60 * ( ( $_new['red'] - $_new['green'] ) / $difference + 4 );
        break;
    }
  } // End achromatic check


  $_hsl = [
    'hue'         =>   round($_hue) ,
    'saturation'  =>   round($_saturation, 2) * 100 ,
    'lightness'   =>   round($_lightness, 2) * 100
  ];
  if ( isset($_hsl[$_return]) )
    return $_hsl[$_return];

  return $_hsl;
}


function _rgb_to_hsl( $r, $g, $b , $_r = '*' ) {
	$oldR = $r;
	$oldG = $g;
	$oldB = $b;
	$r /= 255;
	$g /= 255;
	$b /= 255;
    $max = max( $r, $g, $b );
	$min = min( $r, $g, $b );
	$h;
	$s;
	$l = ( $max + $min ) / 2;
	$d = $max - $min;
    	if( $d == 0 ){
        	$h = $s = 0; // achromatic
    	} else {
        	$s = $d / ( 1 - abs( 2 * $l - 1 ) );
		switch( $max ){
	            case $r:
	            	$h = 60 * fmod( ( ( $g - $b ) / $d ), 6 );
                  if ($b > $g) {
	                    $h += 360;
	                }
	                break;
	            case $g:
	            	$h = 60 * ( ( $b - $r ) / $d + 2 );
	            	break;
	            case $b:
	            	$h = 60 * ( ( $r - $g ) / $d + 4 );
	            	break;
	        }
	}
  $hsl = array( round( $h, 2 ), round( $s, 2 ), round( $l, 2 ) );

  if ( is_int($_r) && isset($hsl[$_r]) )
    return $hsl[$_r];

  return 'hsl('.$hsl[0].','.$hsl[1].','.$hsl[2].')';
}



// does not work?
function hsl_to_rgb( $h, $s, $l , $_r = '*'){
    $r;
    $g;
    $b;
	$c = ( 1 - abs( 2 * $l - 1 ) ) * $s;
	$x = $c * ( 1 - abs( fmod( ( $h / 60 ), 2 ) - 1 ) );
	$m = $l - ( $c / 2 );
	if ( $h < 60 ) {
		$r = $c;
		$g = $x;
		$b = 0;
	} else if ( $h < 120 ) {
		$r = $x;
		$g = $c;
		$b = 0;
	} else if ( $h < 180 ) {
		$r = 0;
		$g = $c;
		$b = $x;
	} else if ( $h < 240 ) {
		$r = 0;
		$g = $x;
		$b = $c;
	} else if ( $h < 300 ) {
		$r = $x;
		$g = 0;
		$b = $c;
	} else {
		$r = $c;
		$g = 0;
		$b = $x;
	}
	$r = ( $r + $m ) * 255;
	$g = ( $g + $m ) * 255;
	$b = ( $b + $m  ) * 255;
  $rgb = array( floor( $r ), floor( $g ), floor( $b ) );

  if ( is_int($_r) && isset($hsl[$_r]) )
    return $hsl[$_r];

    $rgb['red'] = $rgb[0];
    $rgb['green'] = $rgb[1];
    $rgb['blue'] = $rgb[2];
    return $rgb;

  return 'hsl('.$rgb[0].','.$rgb[1].','.$rgb[2].')';
}



?>
