<?php
require_once('databaseService.php');
$service = new ServiceClass();
$fromdate = urldecode($_POST['from']);
$todate = urldecode($_POST['to']);
$dentist = urldecode($_POST['dentist']);
$service->loadDentistPayroll($fromdate, $todate, $dentist);

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

    private function formatWithLineBreaks($value)
    {
        $text = (string) ($value ?? '');
        for ($decodePass = 0; $decodePass < 5; $decodePass++) {
            $decodedText = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decodedText === $text) {
                break;
            }
            $text = $decodedText;
        }
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);

        return nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
    }

    public function loadDentistPayroll($fromdate, $todate, $dentist)
    {
        $parameters = [];
        $conditions = [];

        if (!empty($dentist)) {
            $conditions[] = 'tsoa.dentist = :dentist';
            $parameters[':dentist'] = $dentist;
        }

        if (!empty($fromdate) && !empty($todate)) {
            $conditions[] = 'tsp.paymentdate BETWEEN :fromdate AND :todate';
            $parameters[':fromdate'] = $fromdate;
            $parameters[':todate'] = $todate;
        } elseif (!empty($fromdate)) {
            $conditions[] = 'tsp.paymentdate >= :fromdate';
            $parameters[':fromdate'] = $fromdate;
        } elseif (!empty($todate)) {
            $conditions[] = 'tsp.paymentdate <= :todate';
            $parameters[':todate'] = $todate;
        }

        $whereClause = '';
        if (count($conditions) > 0) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }

        $query = "SELECT tsp.tsubpayid, tsp.tsubid, tsp.amount AS price, tsp.paymenttype, tsp.isTax, tsp.tax,
        tsp.subcharge, tsp.lab_fee AS raw_material,
        tsp.commision_rate, tsp.commision, tsp.paymentdate AS date, tsoa.soaid,
        CONCAT(cp.lname, ', ', cp.fname, ' ', COALESCE(cp.mdname, '')) AS fullname,
        ts.hmo AS hmo, ts.treatment, ts.details
        FROM treatmentsubpayment tsp
        INNER JOIN treatmentsub ts ON tsp.tsubid = ts.tsubid
        INNER JOIN treatmentsoa tsoa ON ts.soaid = tsoa.soaid
        INNER JOIN clientprofile cp ON tsoa.clientid = cp.clientid
        $whereClause
        ORDER BY tsp.paymentdate ASC";

        $stmt = $this->conn->prepare($query);
        foreach ($parameters as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            echo '<div class="alert alert-info">No payment records found for the selected dentist and date range.</div>';
            return;
        }

        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered text-dark" width="100%" cellspacing="0" id="commissionTable">';
        echo '<thead><tr>';
        echo '<th>SOAID</th>';
        echo '<th>Date</th>';
        echo '<th>Patient</th>';

        echo '<th>Treatment</th>';

        echo '<th>Payment Type</th>';
        echo '<th>Treatment Fee</th>';
        echo '<th>isTax</th>';
        echo '<th>Tax</th>';
        echo '<th>Subcharge</th>';
        echo '<th>Lab</th>';
        echo '<th>Commision %</th>';
        echo '<th>Commission Amount</th>';
        echo '</tr></thead><tbody>';
        $count = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $count++;
            $amount = number_format($row['price'], 2, '.', '');
            $rawMaterial = number_format((float) ($row['raw_material'] ?? 0), 2, '.', '');
            $commissionRate = (string) ($row['commision_rate'] ?? '0');
            $commission = number_format((float) ($row['commision'] ?? 0), 2, '.', '');
            $paymentType = htmlspecialchars((string) ($row['paymenttype'] ?? ''), ENT_QUOTES, 'UTF-8');
            $isTax = strtolower(trim((string) ($row['isTax'] ?? ''))) === 'true';
            $tax = number_format((float) ($row['tax'] ?? 0), 2, '.', '');
            $subcharge = number_format((float) ($row['subcharge'] ?? 0), 2, '.', '');
            echo '<tr data-amount="' . $amount . '" data-tsubid="' . (int) $row['tsubid'] . '" data-tsubpayid="' . (int) $row['tsubpayid'] . '">';
            echo '<td>' . htmlspecialchars($row['soaid']) . '</td>';
            echo '<td>' . htmlspecialchars(date('Y/m/d', strtotime($row['date']))) . '</td>';

            echo '<td>' . htmlspecialchars($row['fullname']) . '</td>';

            echo '<td>' . $this->formatWithLineBreaks($row['treatment']) . '</td>';

            echo '<td class="payment-type">' . $paymentType . '</td>';
            echo '<td class="text-right price">' . number_format($row['price'], 2) . '</td>';
            echo '<td class="text-center"><input type="checkbox" class="tax-checkbox"' . ($isTax ? ' checked' : '') . ' aria-label="Tax applicable"></td>';
            echo '<td class="text-right tax-amount">' . $tax . '</td>';
            echo '<td class="text-right subcharge-amount">' . $subcharge . '</td>';
            echo '<td class="text-right raw_material"><input type="number" step="0.01" class="form-control raw-material-input" style="width:100%; box-sizing:border-box;border:0px;font-size:inherit; padding:0px; background-color:transparent;" value="' . htmlspecialchars($rawMaterial, ENT_QUOTES, 'UTF-8') . '"></td>';
            // commission amount starts at 0.00; only checked rows will get a commission value

            echo '<td>';
            echo '<select name="commision_rate" class="form-select"  style="width:100%; box-sizing:border-box;border:0px;font-size:inherit; padding:0px; background-color:transparent;">';

            for ($rate = 0; $rate <= 60; $rate += 5) {
                $selected = $commissionRate === (string) $rate ? ' selected' : '';
                echo '<option value="' . $rate . '"' . $selected . '>' . $rate . '%</option>';
            }
            $otherSelected = $commissionRate === 'Other' ? ' selected' : '';
            echo '<option value="Other"' . $otherSelected . '>Other</option>';
            echo '</select>';
            echo '</td>';
            echo '<td class="text-right commission">' . $commission . '</td>';
            echo '</tr>';
        }

        echo '</tbody>
        
        
        ';


        echo '<tr>';
        echo '<th colspan="11" class="text-right">Total Commision:</th>';
        echo '<th id="totalCommission" class="text-right">0.00</th>';
        echo '</tr>';

        echo '
        </table></div>';

    }
}
