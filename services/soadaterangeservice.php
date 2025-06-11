<?php
require_once('databaseService.php');
$service = new ServiceClass();
$fromdate = urldecode($_POST['from']);
$todate = urldecode($_POST['to']);

$result = $service->loadClientTreatment($fromdate, $todate);

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
    public function loadClientTreatment($fromdate, $todate)
    {


        $dateToday = date("Y-m-d");

        $query = "SELECT tsoa.soaid, cp.clientid, cp.lname, cp.fname, cp.mdname, tsoa.dentist, tsub.treatment, tsub.price, tsoa.date FROM clientprofile cp INNER JOIN treatmentsub tsub ON tsub.clientid = cp.clientid INNER JOIN treatmentsoa tsoa ON tsoa.soaid = tsub.soaid WHERE (tsoa.date BETWEEN :a AND :b) ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':a', $fromdate);
        $stmt->bindParam(':b', $todate);
        $stmt->execute();
        $total = 0;
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $total += $row["price"];
                $fullname = $row["lname"] . ', ' . $row["fname"] . ' ' . $row["mdname"];
                echo '
                <tr>
                <td>' . $row["soaid"] . '</td>

                <td>' . $fullname . '</td>
                <td>' . $row["dentist"] . '</td>
                <td>' . $row["treatment"] . '</td>
                <td style="text-align:right;">' . number_format($row["price"], 2) . '</td>
                <td>' . $row["date"] . '</td>
            </tr>';
            }
        }
        echo '
<tr>
              
                <td colspan="4">Total </td>
                
                <td style="text-align:right;"><strong>' . number_format($total, 2) . '</strong></td>
                <td>-</td>      
            </tr>
';
    }
}
