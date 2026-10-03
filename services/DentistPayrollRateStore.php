<?php
class DentistPayrollRateStore
{
    private $conn;
    private $defaultRates;
    private $tableEnsured = false;

    public function __construct($conn, $rateEntries)
    {
        $this->conn = $conn;
        $this->defaultRates = [];
        foreach ($rateEntries as $entry) {
            $parts = explode('|', $entry, 2);
            if (count($parts) === 2) {
                $this->defaultRates[$parts[0]] = (float) $parts[1];
            }
        }
    }

    public function hasDentist($dentist)
    {
        return array_key_exists($dentist, $this->defaultRates);
    }

    public function getRate($dentist)
    {
        $this->ensureTable();
        $stmt = $this->conn->prepare(
            'SELECT rate_per_minute FROM dentist_payroll_rates WHERE dentist = :dentist'
        );
        $stmt->execute([':dentist' => $dentist]);
        $rate = $stmt->fetchColumn();

        return $rate === false ? $this->defaultRates[$dentist] : (float) $rate;
    }

    public function saveRate($dentist, $rate)
    {
        $this->ensureTable();
        $stmt = $this->conn->prepare(
            'INSERT INTO dentist_payroll_rates (dentist, rate_per_minute) VALUES (:dentist, :rate) '
            . 'ON DUPLICATE KEY UPDATE rate_per_minute = VALUES(rate_per_minute)'
        );
        $stmt->execute([
            ':dentist' => $dentist,
            ':rate' => $rate,
        ]);
    }

    private function ensureTable()
    {
        if ($this->tableEnsured) {
            return;
        }

        $this->conn->exec(
            'CREATE TABLE IF NOT EXISTS dentist_payroll_rates ('
            . 'dentist VARCHAR(150) NOT NULL, '
            . 'rate_per_minute DECIMAL(10, 3) NOT NULL, '
            . 'updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, '
            . 'PRIMARY KEY (dentist)'
            . ')'
        );
        $this->tableEnsured = true;
    }
}
?>
