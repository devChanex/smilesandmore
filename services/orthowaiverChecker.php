<?php
require_once('databaseService.php');
$service = new ServiceClass();
$clientId = urldecode($_POST['clientId']);

$result = $service->process($clientId);
echo $result;
class ServiceClass
{

    private $conn;
    public function __construct()
    {
        $database = new Database();
        $db = $database->dbConnection();
        $this->conn = $db;
    }

    public function runQuery($sql)
    {
        $stmt = $this->conn->prepare($sql);
        return $stmt;
    }
    //DO NOT INCLUDE THIS CODE
    public function process($clientId)
    {



        $query = "select * orthowaiver where clientId = :clientId";
        $stmt = $this->conn->prepare($query);
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);  // Ensure clientId is treated as a string
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

}







?>