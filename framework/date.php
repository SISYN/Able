<?php

class Date {
    private $TimeInput;
    private $TimeStamp , $Date , $Payload;
    private $Date_Format , $Payload_Format;
    /******************************************************************************************************************
     * __construct()
     *****************************************************************************************************************/
    function __construct($TimeInput=false) {
      if ( $TimeInput === false )
          $TimeInput = time();

      $this->TimeInput = $TimeInput;

      $this->DateFormat('m d, Y g:ia');
      $this->PayloadFormat(0);
      $this->AssignSegments($TimeInput);
    }

    private function AssignSegments() {
      $this->TimeStamp = $this->Translate();
      $this->Payload   = $this->Payload();
      $this->Date      = date($this->Date_Format , $this->TimeStamp);
    }

    public function DateFormat($_format) {
      $this->Date_Format = $_format;
      return $this;
    }

    public function PayloadFormat($_format) {
      $this->Payload_Format = $_format;
      return $this;
    }


    /******************************************************************************************************************
     * Translate() - Translate any time stamp/str into a timestamp
     * @param mixed $TimeInput - The piece of time to translate
     * @return int
     *****************************************************************************************************************/
    public function Translate() {
        // Check if it's a timestamp
        if ( is_int($this->TimeInput) )
            return $this->TimeInput;

        // Check if its a recognizable date
        $str_to_time = strtotime($this->TimeInput);
        if ( $str_to_time !== false )
          return $str_to_time;


        preg_match_all('#^[^0-9]*([0-9]+)\s?([a-z\-0-9_]+).*$#i', $this->TimeInput, $payload_segments);
        if ( sizeof($payload_segments < 3) )
          return false;

        $time_input_units = $payload_segments[1][0];
        $time_input_unit_type = $payload_segments[2][0];

        $offset = 0;
        if ( preg_match('#^s#i', $time_input_unit_type) )
            $offset = $time_input_units;
        if ( preg_match('#^m#i', $time_input_unit_type) && !preg_match('#^mo#i', $time_input_unit_type) )
            $offset = $time_input_units*60;
        if ( preg_match('#^h#i', $time_input_unit_type) )
            $offset = $time_input_units*60*60;
        if ( preg_match('#^d#i', $time_input_unit_type) )
            $offset = $time_input_units*60*60*24;
        if ( preg_match('#^w#i', $time_input_unit_type) )
            $offset = $time_input_units*60*60*24*7;
        if ( preg_match('#^mo#i', $time_input_unit_type) )
            $offset = $time_input_units*60*60*24*30;
        if ( preg_match('#^y#i', $time_input_unit_type) )
            $offset = $time_input_units*60*60*24*365;

        return time()-$offset;
    }


    /******************************************************************************************************************
     * Payload() - Returns a layman's termed date/time
     * @return string
     *****************************************************************************************************************/
    public function Payload() {
      $time_difference = time() - $this->TimeStamp;
      return $this->Abbr($time_difference, $this->Payload_Format);
    }

    public function Abbr($number_of_seconds, $abbreviation_level=2, $force_unit_type=false, $include_units=true) {
        $type_of_unit = 'year';
        $units_of_time = $number_of_seconds / (60 * 60 * 24 * 31 * 12); // Divide by a year
        // See if it's been less than a year ago
        if ( $force_unit_type == 'months' || $number_of_seconds < 60 * 60 * 24 * 31 * 12 ) {
            $units_of_time = $number_of_seconds / 60 / 60 / 24 / 30;
            $type_of_unit = 'month';
        }
        // See if it's been less than a month ago
        if ( $force_unit_type == 'weeks' ||  $number_of_seconds < 60 * 60 * 24 * 31 ) {
            $units_of_time = $number_of_seconds / 60 / 60 / 24 / 7;
            $type_of_unit = 'week';
        }
        // See if it's been less than a week ago
        if ( $force_unit_type == 'days' ||  $number_of_seconds < 60 * 60 * 24 * 7 ) {
            $units_of_time = $number_of_seconds / 60 /60 / 24;
            $type_of_unit = 'day';
        }
        // See if it's been less than a day ago
        if ( $force_unit_type == 'hours' ||  $number_of_seconds < 60 * 60 * 24 ) {
            $units_of_time = $number_of_seconds / 60 / 60;
            $type_of_unit = 'hour';
        }
        // See if it's been less than an hour ago
        if ( $force_unit_type == 'minutes' ||  $number_of_seconds < 60 * 60 ) {
            $units_of_time = $number_of_seconds / 60;
            $type_of_unit = 'minute';
        }
        if ( $force_unit_type == 'seconds' ||  $number_of_seconds < 60 ) {
            $units_of_time = $number_of_seconds;
            $type_of_unit = 'second';
        }

        $units_of_time = floor($units_of_time);
        $abbreviations = [
            'second'=>[
                'second',
                'sec',
                's'
            ],
            'minute'=>[
                'minute',
                'min',
                'm'
            ],
            'hour'=>[
                'hour',
                'hr',
                'h'
            ],
            'day'=>[
                'day',
                'day',
                'd'
            ],
            'week'=>[
                'week',
                'wk',
                'w'
            ],
            'month'=>[
                'month',
                'mon',
                'mo'
            ],
            'year'=>[
                'year',
                'yr',
                'y'
            ]
        ];

        $string = $abbreviations[$type_of_unit][$abbreviation_level];

        if ( $units_of_time != 1 && $abbreviation_level < 2 )
            $string .= 's';


        if ( $include_units )
            $string =
                $units_of_time .
                (
                    ($abbreviation_level==2)?'':' '
                ) .
                $string;


        return $string;
    }


    function __destruct() {}
}
?>
