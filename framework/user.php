<?php
class User {
    private $UserID = 0;
    private $UserData = [];
    private $pdb;
    function __construct($user_id=0) {
      global $pdb;
      $this->pdb = ($pdb) ? $pdb : new \DB\MySQL;
      if ( $user_id && is_int($user_id) )
        $this->UserID = $user_id;
    }



     public function Create($user_data, $return_new_id=true) {
       return (new \User\Create)->Attr($user_data)->Save($return_new_id);
     }


     /******************************************************************************************************************
      * Exists() - Returns true/false if user exists/ doesn't exist
      * @return boolean - True if found, false if not
      *****************************************************************************************************************/
      public function Exists() {
        return $this->Validate([
          'id' => $this->UserID
        ]);
      }

      /******************************************************************************************************************
       * Authenticate() - Checks if this user's password is the supplied password
       * @return boolean - True if yes, false if not
       *****************************************************************************************************************/
     public function Authenticate($key) {
       if ( strlen($key) != strlen(adom_hash('str')) )
         $key = adom_hash($key);
       // Get account row where auth.key == $key
       return $this->Validate([
         'id' => $this->UserID,
         'auth.key' => $key
       ]);
     }


    public function Validate($keypairs) {
      $attrs = [];
      foreach($keypairs as $key=>$val)
        $attrs[] =
          (in_array($key, ['id', 'status'])) ? MYSQL_TABLE_USER_ACCTS.'.'.$key.' = '.$this->UserID : '          attr_name = \'' . $key . '\' AND attr_value = \''. $val .'\'';
      $query = '
        SELECT
          *
        FROM
          '.MYSQL_TABLE_USER_ACCTS.'
          INNER JOIN
            '.MYSQL_TABLE_USER_ATTRS.'
          ON
            '.MYSQL_TABLE_USER_ATTRS.'.status=1
              AND
            '.MYSQL_TABLE_USER_ATTRS.'.user_id = '.MYSQL_TABLE_USER_ACCTS.'.id
        WHERE
          ' . join("\n              AND              \n", $attrs) . '
      ';

      $get = $this->pdb->Query($query)->Fetch();
      return ($get) ? true : false;
    }


    /******************************************************************************************************************
     * Fetch() - Gets data about the user
     * @param $attr - Attribute you wish to fetch (default = all attr)
     * @return array - blank if not found
     *****************************************************************************************************************/
    public function Fetch($attr='') {
      if ( !$this->UserData )
          $this->Refresh();
      if ( $attr && array_key_exists($attr, $this->UserData) )
        return $this->UserData[$attr];

      return $this->UserData;
    }

    public function Refresh() {
      // Get account row and subsequent attr data if it exists
      $query = '
        SELECT * FROM '.MYSQL_TABLE_USER_ACCTS.'
        INNER JOIN '.MYSQL_TABLE_USER_ATTRS.' ON '.MYSQL_TABLE_USER_ATTRS.'.user_id='.$this->UserID.' AND '.MYSQL_TABLE_USER_ATTRS.'.status=1
        WHERE '.MYSQL_TABLE_USER_ACCTS.'.id = '.$this->UserID.'
      ';

      $this->UserData = (new \MySQL\Prepared)->Query($query)->Fetch();

      return $this;
    }


    function __destruct() {
    } // end __destruct()

} // end class `User` [extends `Base`]
?>
