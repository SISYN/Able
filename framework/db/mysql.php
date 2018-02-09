<?php
namespace DB {
  class MySQL {

    private $PDO, $Token;
    private $Config = [
      // Set the following to false if you want Create(), Update(), and Delete() to return $this
      'RETURN_MODIFICATION_STATUS' => true
    ];
    /*****************************************************************************************************************
    * __construct() - Initializes the class
    * @param $host - the MySQL host you wish to use if different than default config settings
    * @param $user - the MySQL username you wish to use if different than default config settings
    * @param $pass - the MySQL password you wish to use if different than default config settings
    * @param $db   - the MySQL database you wish to use if different than default config settings
    * @return $this
    *****************************************************************************************************************/
    function __construct(  $host = MYSQL_AUTH_HOST , $user = MYSQL_AUTH_USER , $pass = MYSQL_AUTH_PASS , $db = MYSQL_AUTH_DB  ) {
      $this->PDO = new \DB\MySQL\PDO( $host , $user , $pass , $db );
    } // End of __construct()


    /*****************************************************************************************************************
    * Token() - Return the MySQL connection reference token
    * @return object
    *****************************************************************************************************************/
    public function Token() {
      return $this->Token;
    } // End of Token()



    /*****************************************************************************************************************
    * Create() - Creates a new row, given supplied parameters
    * @return mixed - if RETURN_MODIFICATION_STATUS : 0 if failed, mysql_insert_id if successful | else : $this
    *****************************************************************************************************************/
    public function Create($table_name, $assoc_data) {

      // Start query prep
      $fields = [];
      $field_placeholders = [];
      $data_sets = [];
      foreach($assoc_data as $field=>$value) {
        $fields[] = $field;
        $field_placeholders[] = ':pdo_data__'.$field;
        $data_sets['pdo_data__'.$field] = $value;
      }

      $query_string = 'INSERT INTO '.$table_name.' (' . join(',', $fields) . ') VALUES (' . join(',', $field_placeholders) . ')';
      $prep = $this->PDO->Prepare($query_string);
      $prep->Execute($data_sets);

      if ( $this->Config['RETURN_MODIFICATION_STATUS'] )
        return $this->PDO->InsertID();

      return $this;
    } // End of Create()

    /*****************************************************************************************************************
    * Delete() - Deletes one or more rows, given supplied parameters
    * @return mixed - if RETURN_MODIFICATION_STATUS : false if failed, true if successful | else : $this
    *****************************************************************************************************************/
    public function Delete() {
      if ( $this->Config['RETURN_MODIFICATION_STATUS'] )
        return ( 0 || 1 );
      return $this;
    } // End of Delete()

    /*****************************************************************************************************************
    * Update() - Updates one or more rows, given supplied parameters
    * @return mixed - if RETURN_MODIFICATION_STATUS : false if failed, true if successful | else : $this
    *****************************************************************************************************************/
    public function Update($table_name, $assoc_data, $assoc_criteria) {
      // If using a string format such as id=1, split it for them
      if ( is_string($assoc_criteria) ) {
        list($index, $value) = explode('=', $assoc_criteria);
        $assoc_criteria = [];
        $assoc_criteria[$index] = $value;
      }

      // Start query prep
      $data_sets = [];
      $field_sets = [];
      $criteria_sets = [];
      foreach($assoc_data as $field=>$value) {
        $data_sets['pdo_data__'.$field] = $value;
        $field_sets[] = $field .' = :pdo_data__'. $field;
      }
      foreach($assoc_criteria as $field=>$value) {
        $data_sets['pdo_criteria__'.$field] = $value;
        $criteria_sets[] = $field .' = :pdo_criteria__'. $field;
      }


      $query_string = 'UPDATE '.$table_name.' SET ' . join(' , ', $field_sets) . ' WHERE '.join(' AND ', $criteria_sets);
      $prep = $this->PDO->Prepare($query_string);

      $exec = $prep->Execute($data_sets);

      if ( $this->Config['RETURN_MODIFICATION_STATUS'] )
        return ( $exec == true );

      return $this;
    } // End of Update()

    // Returns PDO->errorInfo
    public function Error() {
      return $this->PDO->Error();
    }


    /*
    public function Duplicates($Table, $GroupByField) {
        $ResultsArray = [];
        while($a = $this->Assoc($this->Query('SELECT `'.$GroupByField.'` FROM `'.$Table.'` GROUP BY `'.$GroupByField.'` HAVING COUNT(*) >= 2')))
            $ResultsArray[] = $a;
        return $ResultsArray;
    }
    */



    /*****************************************************************************************************************
    * GetQuery() - Returns a sample query, given supplied parameter(s)
    * @return string
    *****************************************************************************************************************/
    public function GetQuery() {
      $Query = $this->QueryHandler( func_get_args() );
      return $Query->Compile()->Output();
    } // End of GetQuery()

    /*****************************************************************************************************************
    * Query() - Runs a query -- alias for $this->Fetch(raw_query)
    * @return string
    *****************************************************************************************************************/
    public function Query() {
      $Query = $this->QueryHandler( func_get_args() );
      return $this->PDO->Query(  $Query->Output()  )->Fetch();
    } // End of Query()

    /*****************************************************************************************************************
    * Fetch() - Return a single result, given supplied parameter(s)
    * @return array
    *****************************************************************************************************************/
    public function Fetch() {
      $Query = $this->QueryHandler( func_get_args() );
      return $this->PDO->Query(  $Query->Output()  )->Fetch();
    } // End of Fetch()


    /*****************************************************************************************************************
    * FetchAll() - Returns all results, given supplied parameter(s)
    * @return array
    *****************************************************************************************************************/
    public function FetchAll() {
      $Query = $this->QueryHandler( func_get_args() );
      return $this->PDO->Query(  $Query->Output()  )->FetchAll();
    } // End of FetchAll()



    /*****************************************************************************************************************
    * EscapeQueryString() - Returns an escaped version of the input string
    * @return string
    *****************************************************************************************************************/
    public function EscapeQueryString($input) {
      return $this->PDO->Quote($input);
    } // End of FetchAll()


    /*****************************************************************************************************************
    * QueryHandler() - Takes all applicable parameters from both Fetch and FetchAll and parses them
    * @return array
    *****************************************************************************************************************/
    public function QueryHandler($args) {
      if ( preg_match('#(select|update|delete)\s[^\s]+\sfrom\s[^\s]+#i', $args[0]) )
        return (  new \DB\MySQL\Query($this)  ) -> Base($args[0]) -> Compile();

      if ( is_array($args[1]) )
        $args[1] = join(',', $args[1]);

      $Query = new \DB\MySQL\Query($this);
      $Query -> Base('SELECT '.$args[1].' FROM '.$args[0]);

      if ( is_array($args[2]) ) {
        foreach($args[2] as $possible_field_name => $clause_criteria) {
          if ( is_string($possible_field_name) )
            $Query -> Where($possible_field_name .' = '. $clause_criteria);
          else
            $Query -> Where($clause_criteria);
        }
      } else {
        // It's a string, such as "id = 1 ORDER BY id DESC"
        // So let's make sure it doesn't have an ORDER BY clause as well
        if ( preg_match('#order\s+by#i', $args[2]) ) {
          list($where, $order) = preg_split('#order\s+by#i', $args[2]);
          $Query -> Where( trim($where) ) -> OrderBy( trim($order) );
        } else {
          $Query -> Where($args[2]);
        }
      }

      return $Query;
    } // End of QueryHandler()


  } // End of class MySQL
} // End of namespace DB




?>
