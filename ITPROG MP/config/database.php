<?php

class Database
{
    private $host = "localhost";
    private $dbname = "barangay_portal";
    private $username = "root";
    private $password = "";

    public $conn;

    public function connect()
    {
        $this->conn = null;

        try {

            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            die("Database Connection Failed: " . $e->getMessage());

        }

        return $this->conn;
    }
}
?>
