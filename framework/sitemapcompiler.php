<?php
class SiteMapCompiler {
  private $LastSegmentURI;
  private $SubsetIdentifier = '>>';
  private $MapRaw, $MapCompiled;
  /******************************************************************************************************************
  * __construct()
  * @param $_map - New site map array
  *****************************************************************************************************************/
  function __construct($_map) {
    $this->MapRaw = $_map;
    $this->MapCompiled = ['uri'=>[] , 'data'=>[]];
  }


  public function Output( $_raw = false ) {
    if ( $_raw )
      return $this->MapRaw;
    return $this->MapCompiled;
  }

  public function Compile() {
    foreach($this->MapRaw as $segment_label => $segment_data)
      $this->CompileSegment([$segment_label => $segment_data]);

    return $this;
  }



  /******************************************************************************************************************
  * CompileSegment() - Recursively compiles a single segment/block of the site map array
  * @param $_segment - Site map segment to compile
  * @return object - returns $this
  *****************************************************************************************************************/
  /*
    Using a simple last_segment_uri doesn't work when multiple subsets are present.
    For instance, able[examples,docs] - docs looks for the last uri "examples" which it will never have
  */
  private function CompileSegment($_segment, $_paths = []) {
    // Add this segments main label to its alt labels
    list($segment_label) = array_keys($_segment);
    array_unshift( $_segment[$segment_label]['labels'] , $segment_label );

    // Set URI and labels as easily retrievable vars
    $segment_uri = $_segment[$segment_label]['uri'];
    $segment_labels = $_segment[$segment_label]['labels'];


    $last_path = end($_paths);
    //echo "Last path in heap is `$last_path` for segment \n";
    //print_r($_segment);

    // Reassign last segment label
    $last_segment_uri = trim($this->LastSegmentURI, '/');
    $this->LastSegmentURI = $last_path;


    $last_segment_uri = trim($this->LastSegmentURI, '/');
    $this->LastSegmentURI = $segment_uri;

    // Add its data to the compiled data heap after removing its subet(s)
    $segment_without_subets = $_segment;
    unset($segment_without_subets[$segment_label][$this->SubsetIdentifier]);

    // Add all its possible labels to the compiled URI heap
    if ( !$_paths ) { // If $_paths is empty, populate it with the labels in this segment
      foreach($segment_labels as $alt_label)
        $_paths[$alt_label] = $segment_uri;
      // Inserting here for now to try to make root (/) data insert... (IT WORKS - SOLVED!)
      $this->MapCompiled['data']['/'] = $segment_without_subets[$segment_label];
    } else { // If $_paths is not empty, combine current labels with existing paths to form new paths list
      $new_paths = $_paths;
      foreach($segment_labels as $alt_label) {
        foreach($_paths as $path => $uri) {
          // Make sure this path ends with the beginning of this segments URI
          $uri_dirs = array_values(array_filter( explode('/', $uri) ));
          //echo "Compare last segment label [$last_segment_uri] with ";
          //print_r($uri_dirs);

          // If we are at root level, add any uri and a blank one
          $true_segment_uri = '';
          if ( !sizeof($uri_dirs) && !$last_segment_uri ) {
            //echo "adding new paths from root level \n";
            $true_segment_uri = single_slash($uri . '/' . $segment_uri);
            $new_paths[ trim($path . '/' . $alt_label, '/') ] = $true_segment_uri;
          }

          // If we are in a subset, make sure the uri ends with the corrrect label
          if ( sizeof($uri_dirs) ) {
            //echo "URI dirs exist for segmnt $segment_label: ";
            //print_r($uri_dirs);
            $current_end_dir = end($uri_dirs);
            $last_path_dirs = array_values( array_filter( explode('/', end($_paths)) ) );
            $last_segment_uri = end($last_path_dirs);
            //echo "Expected dir: $last_segment_uri -- got dir $current_end_dir \n";
            if ( $current_end_dir == $last_segment_uri ) {
              //echo "Got the expected dir $last_segment_uri \n";
              $true_segment_uri = single_slash($uri . '/' . $segment_uri);
              $new_paths[ trim($path . '/' . $alt_label, '/') ] = $true_segment_uri;
            } else {
              //echo "Got unexpected dir $current_end_dir while expecting $last_segment_uri \n";
            }
          }

          if ( $true_segment_uri )
            $this->MapCompiled['data'][$true_segment_uri] = $segment_without_subets[$segment_label];
        }
      }

      $_paths = $new_paths;
    }



    //print_r($_paths);
    $this->MapCompiled['uri'] = array_merge($this->MapCompiled['uri'] , $_paths);

    // Compile any site map subsets if they exist
    if ( isset($_segment[$segment_label][$this->SubsetIdentifier]) ) {
      //echo "Found a subset identifier, proceeding with paths ";
      //print_r($_paths);
      foreach($_segment[$segment_label][$this->SubsetIdentifier] as $subset_label => $subset_data) {
        $this->CompileSegment( [$subset_label => $subset_data] , $_paths );
      }
    } else {
      //echo "No subset identifier found in ";
      //print_r($_segment);
    }

    return $this;
  }


  /******************************************************************************************************************
  * __destruct()
  *****************************************************************************************************************/
  function __destruct() {}
} // End of class URI
?>
