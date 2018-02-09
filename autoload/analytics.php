<?php
/******************************************************************************************************************
 * Able / autoload / analytics.php
 * Stores a row in the DB logging this user's visit
 *****************************************************************************************************************/

// If Analytics is enabled, save this visit to the database
if ( SYS_ENABLE_ANALYTICS ) {
  $client_data = [
    'uri'       =>    $_SERVER['REQUEST_URI'],
    'ip'        =>    $_SERVER['REMOTE_ADDR'],
    'agent'     =>    (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '',
    'ref'       =>    (!isset($_SERVER['HTTP_REFERER']))?'':$_SERVER['HTTP_REFERER'],
    'created'   =>    time(),
    'execution_time' => elapsed_execution_time()
  ];

  $log_visit = $db->Create(MYSQL_TABLE_ANALYTICS, $client_data);


  if ( !$log_visit )
    new \System\Log('Failed to log client visit. JSON:'.json_encode($client_data));
}


?>
