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

    public function select($fields, $table, $condition = NULL, $orderBy = NULL, $innerJoin = NULL, $innerJoinOn = NULL)
    {
        $query = "SELECT $fields FROM $table";
        if (!is_null($innerJoin) && !is_null($innerJoinOn)) $query = $query . " INNER JOIN $innerJoin ON $innerJoinOn";
        if (!is_null($condition)) $query = $query . " WHERE $condition";
        if (!is_null($orderBy)) $query = $query . " ORDER BY $orderBy";
        $query = $query . ";";

        $result = $this->mysqli->query($query);
        if ($result) return $result->fetch_all();
        return NULL;
    }

    public function insert($table, $columns, $values)
    {
        $this->mysqli->query("INSERT INTO $table ($columns) VALUES ($values);");
    }

    public function update($table, $columns, $values, $condition)
    {
        if (count($columns) !== count($values)) return NULL;
        $query = "UPDATE $table SET";
        for ($i = 0; $i < count($columns); ++$i)
            $query = $query . " $columns[$i] = '$values[$i]'" . ($i !== count($columns) - 1 ? "," : "");
        $query = $query . " WHERE $condition;";
        return $this->mysqli->query($query);
    }

    public function delete($table, $condition)
    {
        return $this->mysqli->query("DELETE FROM $table WHERE $condition;");
    }
}
