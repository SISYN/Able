<?php
class Membership {
    private $User, $UserData;
    function __construct() { }

    /******************************************************************************************************************
     * Exists() - Returns true/false if user exists/ doesn't exist
     * @return boolean - True if found, false if not
     *****************************************************************************************************************/
    public function Exists() {
      if ( sizeof($this->Fetch('id')) )
        return true;

      return false;
    }

    public function Create($user_data) {
      return (new \User\Create)->Attr($user_data)->Save();
    }

    public function Attr() {
      return $this->UserData;
    }

    public function Authenticate($key) {
      if ( strlen($key) != strlen(adom_hash('str')) )
        $key = adom_hash($key);
      // Get account row where auth.key == $key
      $query = '
        SELECT * FROM '.MYSQL_TABLE_USER_ACCTS.'
        INNER JOIN '.MYSQL_TABLE_USER_ATTRS.' ON '.MYSQL_TABLE_USER_ATTRS.'.user_id='.$this->UserID.' AND '.MYSQL_TABLE_USER_ATTRS.'.status=1
        WHERE '.MYSQL_TABLE_USER_ACCTS.'.id = '.$this->UserID.' AND '.MYSQL_TABLE_USER_ATTRS.'.attr_name = \'auth.key\' AND '.MYSQL_TABLE_USER_ATTRS.'.attr_value = \''.$key.'\'
      ';

      $rows = (new \MySQL\Prepared)->Query($query)->FetchAll();
      return (sizeof($rows)) ? true : false;
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

      $this->UserData = (new \DB\MySQL)->Fetch($query);

      return $this;
    }


    function __destruct() {
    } // end __destruct()

} // end class `User` [extends `Base`]
?>
