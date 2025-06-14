<?php
require_once('databaseService.php');
$service = new ServiceClass();

$result = $service->runMethod();
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
    public function runMethod()
    {



        $query = "SELECT count(id) as 'consent' FROM consent inner join clientprofile on consent.clientId=clientprofile.clientid where consent.status='Active'";
        $stmt = $this->conn->prepare($query);

        $stmt->execute();
        $count = 0;
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $count = $row["consent"];
            }
        }
        return $count;
    }

}
?>