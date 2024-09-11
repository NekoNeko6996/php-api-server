<?php
class Database
{
  private static $instance = null;
  private $connection;

  private function __construct()
  {
    $dbHost = $_ENV['DB_HOST'];
    $dbName = $_ENV['DB_DATABASE'];
    $dbUser = $_ENV['DB_USERNAME'];
    $dbPass = $_ENV['DB_PASSWORD'];

    $this->connection = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($this->connection->connect_error) {
      die("Connection failed: " . $this->connection->connect_error);
    }
  }

  public static function getInstance()
  {
    if (self::$instance == null) {
      self::$instance = new Database();
    }
    return self::$instance;
  }

  public function getConnection()
  {
    return $this->connection;
  }
}