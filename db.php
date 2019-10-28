<?php


class OracleDB {

  private $_connection;
  private static $_instance;
  public static function getInstance($user,$pass,$url,$autocommit=true,$persistent=true,$debug=true) {
    if(!self::$_instance) {
      self::$_instance = new self($user,$pass,$url,$autocommit,$persistent,$debug);
    }
    return self::$_instance;
  }

  public function __construct($user,$pass,$url,$autocommit,$persistent,$debug) {
    $this->debug = $debug;
    $this->_connection = new PDO("oci:dbname=$url",$user,$pass,array(
      PDO::ATTR_AUTOCOMMIT => $autocommit,
      PDO::ATTR_PERSISTENT => $persistent,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ));
  }

  public function __destruct() {
    $this->close();
  }

  public function close() {
    $this->_connection = null;
  }

  private function _stmt($query,$params,$outVar=false,$outType=false,$outLength=128) {
    $stmt = $this->_connection->prepare($query);
    foreach($params as $key => $val) {
      $stmt->bindParam($key,$val);
    }
    if($outVar && $outType) { $stmt->bindParam($outVar,$outVar,$outType,$outLength); }
    $stmt->execute();
    return ($outVar) ? $outVar : $stmt;
  }

  public function fetchAll($query,$params=array(),$single=false) {
    global $log;

    $start = microtime(true);
    $stmt = $this->_stmt($query,$params);
    $data = $single ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
    $timeTaken = microtime(true) - $start;
    if($this->debug) { $log->info('It took ' . round($timeTaken,6) . " sec to execute $query, params - " . print_r($params,true)); }
    return $data;
  }

  public function fetch($query,$params=array()) {
    $data = $this->fetchAll($query,$params,true);
    return $data;
  }

  public function execute($query,$params=array()) {
    $stmt = $this->_stmt($query,$params);
  }

  public function executeOut($query,$outVar,$outType,$params=array(),$outLength=128) {
    return $this->_stmt($query,$params,$outVar,$outType,$outLength);
  }

}
?>    
