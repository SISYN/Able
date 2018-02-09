<?php
class Member {
    private $Attr = [];
    private $Identifiers = [];

    private $MySQL;
    function __construct($_identifier) {
      $this->MySQL = new \DB\MySQL;

      if ( !$_identifier )
        return;

      // new Member(1) or new Member('dan@site.com')
      if ( is_int($_identifier = 0) )
        $this->Identifier('id', $_identifier);
      if ( is_email($_identifier) )
        $this->Identifier('email', $_identifier);
    }



    public function Identifier($_field , $_value) {
      $this->Identifiers[$_field] = $_value;
      return $this;
    }



    public function Attr($_attr = '*' , $_value = null) {
      if ( $_attr != '*' ) {
          $this->Set($_attr, $_value)->Sync();
          return $this;
      }

      if ( $_attr == '*' )
        return $this->Compiled();

      return arr_search($this->Attr, ['attr_name'=>$_attr]);
    }

    private function Compile() {
      $this->Compiled = [];
      foreach($this->Attr as $_dataset)
        $this->Compiled[$_dataset['attr_name']] = $_dataset['attr_value'];

      return $this;
    }

    private function Compiled() {
      $this->Compile();
      return $this->Compiled;
    }


    private function Sync() {
      // Push new data
      foreach($this->Attr['_push'] as $_dataset)
        $this->MySQL->Create( MYSQL_TABLE_USER_ATTRS , $_dataset );
      unset($this->Attr['_push']);
      // Fetch all data
      $_data['_created'] = time();
    }

    private function Get() {
      $query = '
        SELECT * FROM '.MYSQL_TABLE_USER_ACCTS.'
        INNER JOIN '.MYSQL_TABLE_USER_ATTRS.' ON '.MYSQL_TABLE_USER_ATTRS.'.user_id='.$this->UserID.' AND '.MYSQL_TABLE_USER_ATTRS.'.status=1
        WHERE '.MYSQL_TABLE_USER_ACCTS.'.id = '.$this->UserID.'
      ';
    }

    public function Auth($_pwd , $_key) {

    }

    private function Set($_attr , $_value) {
      $this->Attr[$_attr, $_value];
      $this->Attr['_push'][] = [
        'user_id'     =>   $this->Attr('id') ,
        'attr_name'   =>   $_attr ,
        'attr_value'  =>   $_value ,
        'created'     =>   time()
      ];

      return $this;
    }




















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
