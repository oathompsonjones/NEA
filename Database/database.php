<?php
class Database {
    private $dbHost = "localhost";
    private $dbUser = "root";
    private $dbPassword = "Tardis01&";
    private $dbName = "microcontroller_animations";
    private $mysqli;

    public function __construct() {
        $this->mysqli = new mysqli($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName);
    }

    public function select($fields, $table, $condition) {
        if ($result = $this->mysqli->query("SELECT $fields FROM $table WHERE $condition;")) {
            $array = array();
            while ($row = $result->fetch_array()) array_push($array, $row[0]);
            return $array;
        }
        return array();
    }

    public function insert($table, $columns, $values) {
        $this->mysqli->query("INSERT INTO $table ($columns) \n VALUES ($values);");
    }
    
    public function getLoginCredentials() {
        if ($result = $this->mysqli->query("SELECT Username, PasswordHash FROM User;")) {
            $array = array();
            while ($row = $result->fetch_array()) $array[$row["Username"]] = $row["PasswordHash"];
            return $array;
        }
        return array();
    }
}
?>