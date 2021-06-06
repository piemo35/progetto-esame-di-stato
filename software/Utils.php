<?php
class Utils
{
    public function dbConnect(): PDO
    {
        /* credenziali del mio DB */
        $username = "infoservice";
        $password = "1234";
        $dsn = "mysql:host=localhost;dbname=InfoService";
        $conn = new PDO($dsn, $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }

    /*public function sendQuery($req): array
    {
        try {
            $conn = $this->dbConnect();
            $query = $conn->prepare($req);
            $query->execute();
            $conn = null;

            return $query->fetchall();
        } catch (PDOException $e) {
            echo "Connection Failed: " . $e->getMessage();
        }
    }*/

}
