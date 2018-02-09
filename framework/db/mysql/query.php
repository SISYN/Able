<?php
namespace DB\MySQL {
  class Query {
    protected $BaseQuery, $CompiledQuery;
    private $QuerySelects     =    [];
    private $QueryJoins       =    [];
    private $QueryWheres      =    [];
    private $QueryGroupBys    =    [];
    private $QueryOrderBys    =    [];
    private $QueryLimit       =    '';

    private $MySQL_Reference;

    /*****************************************************************************************************************
    * __construct() - Initializes the class
    * @return $this
    *****************************************************************************************************************/
    function __construct($MySQL_Reference = false) {
      if ( $MySQL_Reference )
        $this->MySQL_Reference = $MySQL_Reference;
    } // End __construct() method


    public function Base($BaseQuery) {
        $this->BaseQuery = $this->EscapeEqualComparisons($BaseQuery);

        return $this;
    }

    public function Select() {
        $clauses = func_get_args();
        foreach($clauses as $clause)
            $this->QuerySelects[] = $clause;

        return $this;
    }
    public function Join() {
        $clauses = func_get_args();
        foreach($clauses as $clause)
            $this->QueryJoins[] = $clause;

        return $this;
    }
    public function Where() {
        $clauses = func_get_args();
        foreach($clauses as $clause)
            $this->QueryWheres[] = $this->EscapeEqualComparisons($clause);

        return $this;
    }
    public function GroupBy() {
        $clauses = func_get_args();
        foreach($clauses as $clause)
            $this->QueryGroupBys[] = $clause;

        return $this;
    }
    public function OrderBy() {
        $clauses = func_get_args();
        foreach($clauses as $clause)
            $this->QueryOrderBys[] = $clause;

        return $this;
    }
    public function Limit($Limit) {
        $this->QueryLimit = $Limit;

        return $this;
    }

    public function Compile() {
        $this->CompiledQuery = $this->BaseQuery;

        $additional_selects = $this->QuerySelects;
        $joins = $this->QueryJoins;
        $where_clauses = $this->QueryWheres;
        $group_clauses = $this->QueryGroupBys;
        $order_clauses = $this->QueryOrderBys;
        $limit_clause  = $this->QueryLimit;

        // Replace additional selects
        if ( sizeof($additional_selects) )
            $this->CompiledQuery = str_replace('SELECT', 'SELECT '.join(" , \n", $additional_selects).' , ', $this->CompiledQuery);

        // Now assemble the query parts
        if ( sizeof($joins) )
            $this->CompiledQuery .= join("\n", $joins);

        if ( sizeof($where_clauses) )
            $this->CompiledQuery .= '
              WHERE
            '.join('
                AND
            ', $where_clauses);


        if ( sizeof($group_clauses) )
            $this->CompiledQuery .= '
              GROUP BY '.join(' , ', $group_clauses).'
            ';
        if ( sizeof($order_clauses) )
            $this->CompiledQuery .= '
              ORDER BY '.join(' , ', $order_clauses).'
            ';

        if ( strlen($limit_clause) )
            $this->CompiledQuery .= '
              LIMIT '.$limit_clause.'
            ';


        return $this;
    }

    public function Output() {
        $this->Compile();
        return $this->CompiledQuery;
    }





    private function EscapeEqualComparisons($input) {
      if ( preg_match('#^[^=]+=[^=]+$#', $input) ) {
        list($field , $value) = explode('=', $input);
        $input = str_replace( trim($value) , $this->EscapeString(trim($value)) , $input );
      }

      return $input;
    }

    private function EscapeString($input) {
      $db = ($this->MySQL_Reference) ? $this->MySQL_Reference : new \DB\MySQL;
      return $db->EscapeQueryString($input);
    }









  } // End of class Query
} // End of namespace DB\MySQL
?>
