<?php

class DatabaseConnection
{
    public $dbc;             //The database object we'll be using
    public $result;          //The results from any
    public $num_rows;        //The number of rows returned in a query
    public $last_id;         //The ID of the last row updated/entered
    public $PATH_TO_UPLOADS; //The location where image uploads will be stored

    /**
     * Create a new connection to the database
     *
     * @return MySQLi
     */
    function __construct()
    {
        $this->dbc = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB_NAME);

        if ($this->dbc->connect_errno)
            echo "Something went wrong with the database connection.";
    }

    function __destruct()
    {
        $this->closeConnection();
    }

    /**
     * Clean a string from SQL injections
     *
     * @param (String) The string to be cleansed
     * @return (String) The cleaned string
     */
    function clean_string($string)
    {
        return $this->dbc->real_escape_string($string);
    }

    /**
     * Close the current connection to the MySQL database
     *
     * @param (void)
     * @return (void)
     */
    function closeConnection()
    {
        mysqli_close($this->dbc);
    }

    /**
     * Tests whether or not the connection to the database
     * is still active
     *
     * @param (void)
     * @return (void)
     */
    function isConnected()
    {
        return $this->dbc->ping();
    }

    /**
     * Prepares and executes a MySQL prepared statement
     *
     * @param String $query The prepared SQL statement that will be executed
     * @param Array $parameters The array of values that will be binded to the prepared statement
     * @param String $param_types The types of values that will be passed through the prepared statement
     *
     * @return Array The elements that were returned from the SQL query or null
     */
    function prepared_query($query, $typeDef = FALSE, $params = FALSE)
    {
        if ($stmt = $this->dbc->prepare($query))
        {
            if(count($params) == count($params, 1))
            {
                $params = array($params);
                $multiQuery = false;
            }
            else
                $multiQuery = true;

            if($typeDef)
            {
                $bindParams = array();
                $bindParamsReferences = array();
                $bindParams = array_pad($bindParams, (count($params, 1) - count($params))/count($params), "");

                foreach($bindParams as $key => $value)
                    $bindParamsReferences[$key] = &$bindParams[$key];

                array_unshift($bindParamsReferences, $typeDef);
                $bindParamsMethod = new ReflectionMethod('mysqli_stmt', 'bind_param');
                $bindParamsMethod->invokeArgs($stmt, $bindParamsReferences);
            }

            $result = array();
            foreach($params as $queryKey => $query)
            {
                foreach($bindParams as $paramKey => $value)
                    $bindParams[$paramKey] = $query[$paramKey];

                $queryResult = array();
                if($stmt->execute())
                {
                    $resultMetaData = $stmt->result_metadata();

                    if($resultMetaData)
                    {
                        $stmtRow = array();
                        $rowReferences = array();

                        while ($field = $resultMetaData->fetch_field())
                        {
                            $rowReferences[] = &$stmtRow[$field->name];
                        }

                        mysqli_free_result($resultMetaData);
                        $bindResultMethod = new ReflectionMethod('mysqli_stmt', 'bind_result');
                        $bindResultMethod->invokeArgs($stmt, $rowReferences);

                        while(mysqli_stmt_fetch($stmt))
                        {
                            $row = array();
                            foreach($stmtRow as $key => $value)
                            {
                                $row[$key] = $value;
                            }

                            $queryResult[] = $row;
                        }

                        mysqli_stmt_free_result($stmt);
                    }
                    else
                        $queryResult[] = mysqli_stmt_affected_rows($stmt);
                }
                else
                    $queryResult[] = false;

                $result[$queryKey] = $queryResult;
            }

            mysqli_stmt_close($stmt);
        }
        else
            $result = false;

        if($multiQuery)
            return $result;
        else
            return $result[0];
    }

    /**
     * Output an error that has occured
     *
     * @param (String) The error message to be displayed
     * @param (int) The line number where the error occured
     *
     * @return (void)
     */
    function throw_error($message, $query)
    {
        die("The following error has occured: " . $message . "<br>Executing the following query: " . $query);
    }
}