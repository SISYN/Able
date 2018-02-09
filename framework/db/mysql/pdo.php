<?php
namespace DB\MySQL {
  class PDO {

    private $pdo, $tablePrefix;
    function __construct($MySQL_Host=MYSQL_AUTH_HOST , $MySQL_User=MYSQL_AUTH_USER , $MySQL_Pass=MYSQL_AUTH_PASS , $MySQL_DB=MYSQL_AUTH_DB) {
      $charset = 'utf8';

      $this->tablePrefix = '';

      $dsn = 'mysql:host='.$MySQL_Host.';dbname='.$MySQL_DB.';charset='.$charset;
      $opt = [
          \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
          \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
          \PDO::ATTR_EMULATE_PREPARES   => false,
      ];

      try {
        $this->pdo = new \PDO($dsn, $MySQL_User, $MySQL_Pass, $opt);
      } catch (\PDOException $exception) {
        new \System\Notice('Unable to connect to data source.');
        new \System\Log('PDO Unable to connect on framework/db/mysql/pdo : '.__LINE__.'. Code: '.$exception);
        exit;
      }
    }

    public function InsertID() {
      return $this->pdo->lastInsertId();
    }

    public function Error() {
      return $this->pdo->errorInfo();
    }

    public function Query($query) {
        $query = $this->EscapeTableName($query);
        return $this->pdo->query($query);
    }

    public function Prepare($query) {
        $query = $this->EscapeTableName($query);
        return $this->pdo->prepare($query);
    }

    public function Execute($vars) {
        $this->pdo->execute($vars);
        return $this->pdo;
    }

    public function Fetch() {
        return $this->pdo->fetch();
    }
    public function FetchAll() {
        return $this->pdo->fetchAll();
    }
    public function FetchColumn() {
        return $this->pdo->fetchColumn();
    }

    public function Quote($input) {
      return $this->pdo->quote($input);
    }

    public function EscapeTableName($query) {
        if ( strlen($this->tablePrefix) < 1 )
            return $query;
        $escapeTableNames = preg_replace('#'.$this->tablePrefix.'(\.|\-)([a-zA-Z0-9\-_\.]+)#i', '`'.$this->tablePrefix.'_$2'.'`', $query);
        preg_match('#`'.$this->tablePrefix.'[^`]+`#i', $escapeTableNames, $matches);
        foreach($matches as $match)
            $escapeTableNames = str_replace($match, str_replace(array('.', '-'), '_', $match), $escapeTableNames);

        return $escapeTableNames;
    }


    function __destruct() {

    }
  } // End of class PDO
} // End of namespace \DB\MySQL
?>
