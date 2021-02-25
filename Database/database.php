<?php
class Database
{
    private $dbHost = "localhost";
    private $dbUser = "root";
    private $dbPassword = "Tardis01&";
    private $dbName = "microcontroller_animations";
    private $mysqli;

    public function __construct()
    {
        $this->mysqli = new mysqli($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName);
    }

    public function select($fields, $table, $condition = NULL)
    {
        $result = NULL;
        if (!is_null($condition)) $result = $this->mysqli->query("SELECT $fields FROM $table WHERE $condition;");
        else $result = $this->mysqli->query("SELECT $fields FROM $table;");
        if ($result) return $result->fetch_all();
        return NULL;
    }

    public function insert($table, $columns, $values)
    {
        $this->mysqli->query("INSERT INTO $table ($columns) \n VALUES ($values);");
    }

    public function getLoginCredentials()
    {
        $result = $this->select("Username, PasswordHash", "User");
        if (is_null($result)) return [];
        $credentials = array();
        while ($row = array_shift($result)) $credentials[$row[0]] = $row[1];
        return $credentials;
    }
}
