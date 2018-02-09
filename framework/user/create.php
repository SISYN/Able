<?php
namespace User {
  class Create {
      private $UserData = [];
      function __construct() {

      }

      public function Attr($attr, $attr_value='') {
        if ( is_array($attr) )
          $this->UserData = $attr;
        else
          $this->UserData[$attr] = $attr_value;

        return $this;
      }

      public function Save($return_new_id=true) {
        $new_user_id = (new \DB\MySQL)->Create(MYSQL_TABLE_USER_ACCTS, [
          'created' =>  time()
        ], 1);
        if ( !$new_user_id ) {
          $msg = 'User account creation failed';
          new \System\Log($msg);
          new \System\Notice($msg);
          return $this;
        }


        foreach($this->UserData as $attr=>$val) {
          $create_attr = (new \DB\MySQL)->Create(MYSQL_TABLE_USER_ATTRS, [
            'user_id'     =>   $new_user_id,
            'attr_name'   =>   $attr,
            'attr_value'  =>   $val,
            'created'     =>   time(),
            'status'      =>   1
          ]);

          if ( !$create_attr ) {
            $msg = 'User attribute creation failed ('.$attr.')';
            new \System\Log($msg);
            new \System\Notice($msg);
            return $this;
          }
        }

        if ( $return_new_id )
          return $new_user_id;

        return $this;
      }

      function __destruct() { }
  }
}
?>
