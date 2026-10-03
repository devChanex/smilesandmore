<?php
require_once('databaseService.php');
require_once(__DIR__ . '/DentistPayrollRateStore.php');
require_once(__DIR__ . '/../bars/properties.php');
$fromdate = urldecode($_POST['from']);
$todate = urldecode($_POST['to']);
$dentist = urldecode($_POST['dentist']);
$service = new ServiceClass($dentistRate);
$service->loadDentistPayroll($fromdate, $todate, $dentist);

class ServiceClass
{
    private $conn;
    private $dentistRate;

    public function __construct($dentistRate)
    {
        $this->dentistRate = $dentistRate;
        $database = new Database();
        $db = $database->dbConnection();
        $this->conn = $db;
    }

    public function runQuery($sql)
    {
        $stmt = $this->conn->prepare($sql);
        return $stmt;
    }

    public function loadDentistPayroll($fromdate, $todate, $dentist)
    {
        $rateStore = new DentistPayrollRateStore($this->conn, $this->dentistRate);
        $ratePerMinute = $rateStore->hasDentist($dentist) ? $rateStore->getRate($dentist) : 0.0;

        $timekeepingConditions = ['name = :dentist'];
        $timekeepingParameters = [':dentist' => $dentist];
        if (!empty($fromdate)) {
            $timekeepingConditions[] = 'date >= :fromdate';
            $timekeepingParameters[':fromdate'] = $fromdate;
        }
        if (!empty($todate)) {
            $timekeepingConditions[] = 'date <= :todate';
            $timekeepingParameters[':todate'] = $todate;
        }
        $timekeepingQuery = 'SELECT COALESCE(SUM(rendered), 0) AS total_minutes, COALESCE(SUM(overtime), 0) AS total_overtime_minutes FROM timekeeping WHERE '
            . implode(' AND ', $timekeepingConditions);
        $timekeepingStmt = $this->conn->prepare($timekeepingQuery);
        $timekeepingStmt->execute($timekeepingParameters);
        $timekeepingTotals = $timekeepingStmt->fetch(PDO::FETCH_ASSOC);
        $minutesRendered = (int) round((float) $timekeepingTotals['total_minutes']);
        $overtimeMinutes = (int) round((float) $timekeepingTotals['total_overtime_minutes']);
        $basicSalary = round($minutesRendered * $ratePerMinute, 2);
        $overtimePay = round($overtimeMinutes * $ratePerMinute, 2);
        $basicSalaryDetails = 'Minutes Rendered : ' . number_format($minutesRendered)
            . ', Rate Per minute ' . number_format($ratePerMinute, 3, '.', ',');
        $overtimeDetails = 'Overtime Minutes : ' . number_format($overtimeMinutes)
            . ', Rate Per minute ' . number_format($ratePerMinute, 3, '.', ',');


        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered text-dark" width="100%" cellspacing="0" id="commissionTable">';
        echo '<thead><tr>';
        echo '<th>Type</th>';
        echo '<th>Particulars</th>';
        echo '<th>Amount</th>';
        echo '</tr></thead><tbody>';

        echo '<tr>';
        echo '<td>Basic Salary</td>';
        echo '<td id="basicSalaryDetails" data-minutes-rendered="' . $minutesRendered . '" data-rate-per-minute="' . htmlspecialchars((string) $ratePerMinute, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($basicSalaryDetails, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="text-right" id="basicSalaryAmount">' . number_format($basicSalary, 2) . '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td>Overtime</td>';
        echo '<td id="overtimeDetails" data-overtime-minutes="' . $overtimeMinutes . '" data-rate-per-minute="' . htmlspecialchars((string) $ratePerMinute, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($overtimeDetails, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="text-right" id="overtimeAmount">' . number_format($overtimePay, 2) . '</td>';
        echo '</tr>';

        $parameters = [];
        $conditions = [];

        if (!empty($dentist)) {
            $conditions[] = 'adj.dentist = :dentist';
            $parameters[':dentist'] = $dentist;
        }

        if (!empty($fromdate) && !empty($todate)) {
            $conditions[] = 'adj.date BETWEEN :fromdate AND :todate';
            $parameters[':fromdate'] = $fromdate;
            $parameters[':todate'] = $todate;
        } elseif (!empty($fromdate)) {
            $conditions[] = 'adj.date >= :fromdate';
            $parameters[':fromdate'] = $fromdate;
        } elseif (!empty($todate)) {
            $conditions[] = 'adj.date <= :todate';
            $parameters[':todate'] = $todate;
        }

        $whereClause = '';
        if (count($conditions) > 0) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }

        $query = "SELECT adj.type,adj.particular, adj.amount FROM payroll_adjustments adj $whereClause ORDER BY adj.type, adj.date ASC";

        $stmt = $this->conn->prepare($query);
        foreach ($parameters as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $deductions = 0.00;
        $additional = 0.00;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['type'] === 'Deduction') {
                $deductions += $row['amount'];
            } elseif ($row['type'] === 'Additional') {
                $additional += $row['amount'];
            }
            $amount = number_format($row['amount'], 2, '.', '');
            echo '<tr data-amount="' . $amount . '">';
            echo '<td>' . htmlspecialchars($row['type']) . '</td>';
            echo '<td>' . htmlspecialchars($row['particular']) . '</td>';
            echo '<td class="text-right price">' . number_format($row['amount'], 2) . '</td>';
            // commission amount starts at 0.00; only checked rows will get a commission value


            echo '</tr>';
        }

        echo '</tbody>
        
        
        ';


        echo '<tr>';
        echo '<th colspan="2" class="text-right">Basic Salary:</th>';
        echo '<th id="totalBasicSalary" class="text-right">' . number_format($basicSalary, 2) . '</th>';
        echo '</tr>';

        echo '<tr>';
        echo '<th colspan="2" class="text-right">Overtime:</th>';
        echo '<th id="totalOvertime" class="text-right">' . number_format($overtimePay, 2) . '</th>';
        echo '</tr>';

        echo '<tr>';
        echo '<th colspan="2" class="text-right">Total Additional:</th>';
        echo '<th id="totalAdditional" class="text-right">' . number_format($additional, 2) . '</th>';
        echo '</tr>';

        echo '<tr>';
        echo '<th colspan="2" class="text-right">GrossPay:</th>';
        echo '<th id="totalGrossPay" class="text-right">0.00</th>';
        echo '</tr>';


        echo '<tr>';
        echo '<th colspan="2" class="text-right">Total Deductions:</th>';
        echo '<th id="totalDeductions" class="text-right">' . number_format($deductions, 2) . '</th>';
        echo '</tr>';



        echo '<tr>';
        echo '<th colspan="2" class="text-right">NetPay:</th>';
        echo '<th id="netpay" class="text-right">0.00</th>';
        echo '</tr>';


        echo '
        </table></div>';

    }
}
