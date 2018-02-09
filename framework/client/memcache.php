<?php
namespace Client {
  class Memcache {
    private $Memcache;
    function __construct() {
        $this->Memcache = new Memcache;

        if ( $this->Connect() )
            $this->Set('Session-'.session_id(), json_encode($_SESSION));
    }


    /******************************************************************************************************************
     * Connect() - Sets data on the Memcache server
     * @param $port_number - integer for port number to be used in connection
     * @param $host_name - string of the domain/host name to connect to
     * @return mixed - false if fail, $this otherwise
     *****************************************************************************************************************/
    private function Connect($port_number=11211, $host_name='localhost') {
      if ( !$this->Memcache->connect($host_name, $port_number) )
        throw new Exception('Cannot connect to memecache server. [adom\client\memcache : '.__LINE__.']');

      return $this;
    }


    /******************************************************************************************************************
     * Set() - Sets data on the Memcache server
     * @param $item_name - string of the item name to set
     * @param $item_data - string of data to set for the item
     * @return mixed - false if fail, $this otherwise
     *****************************************************************************************************************/
    private function Set($item_name, $item_data) {
        if (  !$this->Memcache->set($item_name, $item_data) )
            return false;

        return $this;
    }

    function __destruct() {
    } // end __destruct()
  }
}
?>
