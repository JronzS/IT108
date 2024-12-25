<?php
require_once('config.php');

class Database {
    private $con;

    public function __construct() {
        $this->db_connect();
    }

    /*--------------------------------------------------------------*/
    /* Function for Open database connection
    /*--------------------------------------------------------------*/
    public function db_connect()
    {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            $this->con = new PDO($dsn, DB_USER, DB_PASS);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    /*--------------------------------------------------------------*/
    /* Function for Close database connection
    /*--------------------------------------------------------------*/
    public function db_disconnect()
    {
        $this->con = null;
    }

    /*--------------------------------------------------------------*/
    /* Function for execute query
    /*--------------------------------------------------------------*/
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->con->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Error on this Query :<pre> " . $sql . "</pre>Message: " . $e->getMessage());
        }
    }

    /*--------------------------------------------------------------*/
    /* Function for fetching data
    /*--------------------------------------------------------------*/
    public function fetch_array($stmt)
    {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetch_object($stmt)
    {
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function fetch_assoc($stmt)
    {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function num_rows($stmt)
    {
        return $stmt->rowCount();
    }

    public function insert_id()
    {
        return $this->con->lastInsertId();
    }

    public function affected_rows($stmt)
    {
        return $stmt->rowCount();
    }

    /*--------------------------------------------------------------*/
    /* Function for escaping input
    /*--------------------------------------------------------------*/
    public function escape($str)
    {
        return $this->con->quote($str);
    }
}

$db = new Database();
?>
