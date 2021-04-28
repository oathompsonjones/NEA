<?php

/**
 * A class to interface with the database.
 */
class Database
{
    /**
     * @var string
     */
    private $dbHost = "localhost";
    /**
     * @var string
     */
    private $dbUser = "root";
    /**
     * @var string
     */
    private $dbPassword = "Tardis01&";
    /**
     * @var string
     */
    private $dbName = "microcontroller_animations";
    /**
     * @var mysqli
     */
    private $mysqli;

    public function __construct()
    {
        $this->mysqli = new mysqli($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName);
    }

    /**
     * Runs an SQL SELECT statement. The fields and table are required, the condition, orderBy and innerJoin settings are optional.
     * @param string $fields
     * @param string $table
     * @param string|null $condition
     * @param string|null $orderBy
     * @param string|null $innerJoin
     * @param string|null $innerJoinOn
     * @return Array|null
     */
    public function select($fields, $table, $condition = NULL, $orderBy = NULL, $innerJoin = NULL, $innerJoinOn = NULL)
    {
        // Sets up the basic SQL statement.
        $query = "SELECT $fields FROM $table";
        // Checks if an INNER JOIN ... ON exists and applies it if needed.
        if (!is_null($innerJoin) && !is_null($innerJoinOn)) $query .= " INNER JOIN $innerJoin ON $innerJoinOn";
        // Checks if a WHERE exists and applies it if needed.
        if (!is_null($condition)) $query .= " WHERE $condition";
        // Checks if an ODER BY exists ad applies it if needed.
        if (!is_null($orderBy)) $query .= " ORDER BY $orderBy";
        // Adds a semi-colon to the end of the query.
        $query .= ";";
        // Runs the query.
        $result = $this->mysqli->query($query);
        // Checks if there is a result and returns it if it exists.
        if ($result) return $result->fetch_all();
        // Returns null if there is no result.
        return NULL;
    }

    /**
     * Runs an SQL INSERT statement.
     * @param string $table
     * @param string $columns
     * @param string $values
     * @return void
     */
    public function insert($table, $columns, $values)
    {
        $this->mysqli->query("INSERT INTO $table ($columns) VALUES ($values);");
    }

    /**
     * Runs an SQL UPDATE statement.
     * @param string $table
     * @param string[] $columns
     * @param string[] $values
     * @param string $condition
     * @return void
     */
    public function update($table, $columns, $values, $condition)
    {
        // Checks that the number of columns is the same as the number of values.
        if (count($columns) !== count($values)) return NULL;
        // Sets up the basic SQL statement.
        $query = "UPDATE $table SET";
        // Gets the number of columns so that it doesn't need to be recounted on every loop iteration.
        $columnCount = count($columns);
        // Loops through each column and adds the column and value to the statement.
        for ($i = 0; $i < $columnCount; ++$i) $query .= " $columns[$i] = '$values[$i]'" . ($i !== $columnCount - 1 ? "," : "");
        // Adds the where condition.
        $query .= " WHERE $condition;";
    }

    /**
     * Runs an SQL DELETE FROM statement.
     * @param string $table
     * @param string $condition
     * @return void
     */
    public function delete($table, $condition)
    {
        $this->mysqli->query("DELETE FROM $table WHERE $condition;");
    }
}
