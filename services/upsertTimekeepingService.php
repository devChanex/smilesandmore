<?php
session_start();
require_once(__DIR__ . '/databaseService.php');
require_once(__DIR__ . '/../bars/properties.php');

try {
    $service = new TimekeepingService();
    $service->process($_POST, array_merge($dentistSchedule, $staffSchedule));
    echo 'success';
} catch (Throwable $error) {
    http_response_code(400);
    echo $error->getMessage();
}

class TimekeepingService
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->dbConnection();
    }

    public function process($data, $scheduleEntries)
    {
        if (($data['action'] ?? '') === 'delete') {
            $this->deleteRecord($data);
            return;
        }
        if (($data['action'] ?? '') === 'timeout') {
            $this->recordTimeOut($data, $scheduleEntries);
            return;
        }

        $id = trim($data['timekeepid'] ?? '');
        $date = $data['date'] ?? '';
        $name = trim($data['name'] ?? '');
        $timeIn = $data['timein'] ?? '';
        $timeOut = $data['timeout'] ?? '';
        $dayType = $data['dayType'] ?? '';
        $validDayTypes = ['Regular Working Day', 'Regular Holiday', 'Special Holiday'];

        if (!$this->isValidDate($date) || $name === '' || !$this->isValidTime($timeIn)) {
            throw new InvalidArgumentException('Enter a valid date, person, and time in.');
        }
        if ($id !== '' && !ctype_digit($id)) {
            throw new InvalidArgumentException('Invalid timekeeping record.');
        }
        if ($timeOut !== '' && !$this->isValidTime($timeOut)) {
            throw new InvalidArgumentException('Enter a valid time out.');
        }
        if (!in_array($dayType, $validDayTypes, true)) {
            throw new InvalidArgumentException('Select a valid day type.');
        }
        $this->assertUniqueDailyRecord($date, $name, $id);

        $scheduleMap = [];
        foreach ($scheduleEntries as $entry) {
            $parts = explode('|', $entry, 2);
            if (count($parts) === 2) {
                $scheduleMap[$parts[0]] = $parts[1];
            }
        }
        if (!isset($scheduleMap[$name])) {
            throw new InvalidArgumentException('The selected person has no configured schedule.');
        }

        $minutes = $timeOut === ''
            ? ['rendered' => 0, 'overtime' => 0]
            : $this->calculateTimekeepingMinutes($date, $timeIn, $timeOut, $scheduleMap[$name]);
        if ($id !== '') {
            $stmt = $this->conn->prepare('SELECT timeout FROM timekeeping WHERE timekeepid = :id');
            $stmt->execute([':id' => (int) $id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$existing) {
                throw new InvalidArgumentException('Timekeeping record not found.');
            }
            $hasSavedTimeOut = !empty($existing['timeout']) && $existing['timeout'] !== '00:00:00';
            $canEditFinalized = in_array((int) ($_SESSION['account_type'] ?? -1), [0, 100], true);
            if ($hasSavedTimeOut && !$canEditFinalized) {
                throw new RuntimeException('Only account types 0 and 100 can edit a completed record.');
            }

            $stmt = $this->conn->prepare('UPDATE timekeeping SET date = :date, name = :name, timein = :timein, timeout = :timeout, rendered = :rendered, overtime = :overtime, dayType = :dayType WHERE timekeepid = :id');
            $stmt->execute([
                ':date' => $date,
                ':name' => $name,
                ':timein' => $timeIn,
                ':timeout' => $timeOut === '' ? '00:00:00' : $timeOut,
                ':rendered' => $minutes['rendered'],
                ':overtime' => $minutes['overtime'],
                ':dayType' => $dayType,
                ':id' => (int) $id,
            ]);
        } else {
            $stmt = $this->conn->prepare('INSERT INTO timekeeping (date, name, timein, timeout, rendered, overtime, dayType) VALUES (:date, :name, :timein, :timeout, :rendered, :overtime, :dayType)');
            $stmt->execute([
                ':date' => $date,
                ':name' => $name,
                ':timein' => $timeIn,
                ':timeout' => $timeOut === '' ? '00:00:00' : $timeOut,
                ':rendered' => $minutes['rendered'],
                ':overtime' => $minutes['overtime'],
                ':dayType' => $dayType,
            ]);
        }
    }

    private function deleteRecord($data)
    {
        if (!in_array((int) ($_SESSION['account_type'] ?? -1), [0, 100], true)) {
            throw new RuntimeException('Only account types 0 and 100 can delete timekeeping records.');
        }

        $id = trim((string) ($data['timekeepid'] ?? ''));
        if (!ctype_digit($id) || (int) $id < 1) {
            throw new InvalidArgumentException('Invalid timekeeping record.');
        }

        $stmt = $this->conn->prepare('DELETE FROM timekeeping WHERE timekeepid = :id');
        $stmt->execute([':id' => (int) $id]);
        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Timekeeping record not found.');
        }
    }

    private function assertUniqueDailyRecord($date, $name, $id)
    {
        $query = 'SELECT timekeepid FROM timekeeping WHERE date = :date AND name = :name';
        if ($id !== '') {
            $query .= ' AND timekeepid <> :id';
        }
        $query .= ' LIMIT 1';

        $stmt = $this->conn->prepare($query);
        $params = [':date' => $date, ':name' => $name];
        if ($id !== '') {
            $params[':id'] = (int) $id;
        }
        $stmt->execute($params);

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new InvalidArgumentException('A timekeeping record already exists for this person on this date.');
        }
    }

    private function recordTimeOut($data, $scheduleEntries)
    {
        $id = trim($data['timekeepid'] ?? '');
        $timeOut = $data['timeout'] ?? '';
        if (!ctype_digit($id) || !$this->isValidTime($timeOut)) {
            throw new InvalidArgumentException('Enter a valid time out.');
        }

        $stmt = $this->conn->prepare('SELECT date, name, timein, timeout FROM timekeeping WHERE timekeepid = :id');
        $stmt->execute([':id' => (int) $id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$record) {
            throw new InvalidArgumentException('Timekeeping record not found.');
        }

        $hasSavedTimeOut = !empty($record['timeout']) && $record['timeout'] !== '00:00:00';
        $canEditFinalized = in_array((int) ($_SESSION['account_type'] ?? -1), [0, 100], true);
        if ($hasSavedTimeOut && !$canEditFinalized) {
            throw new RuntimeException('Only account types 0 and 100 can edit a completed record.');
        }

        $scheduleMap = [];
        foreach ($scheduleEntries as $entry) {
            $parts = explode('|', $entry, 2);
            if (count($parts) === 2) {
                $scheduleMap[$parts[0]] = $parts[1];
            }
        }
        if (!isset($scheduleMap[$record['name']])) {
            throw new RuntimeException('The selected person has no configured schedule.');
        }

        $minutes = $this->calculateTimekeepingMinutes($record['date'], substr($record['timein'], 0, 5), $timeOut, $scheduleMap[$record['name']]);
        $stmt = $this->conn->prepare('UPDATE timekeeping SET timeout = :timeout, rendered = :rendered, overtime = :overtime WHERE timekeepid = :id');
        $stmt->execute([
            ':timeout' => $timeOut,
            ':rendered' => $minutes['rendered'],
            ':overtime' => $minutes['overtime'],
            ':id' => (int) $id,
        ]);
    }

    private function calculateTimekeepingMinutes($date, $timeIn, $timeOut, $schedule)
    {
        $parts = explode('-', $schedule, 2);
        if (count($parts) !== 2 || !$this->isValidTime($parts[0]) || !$this->isValidTime($parts[1])) {
            throw new RuntimeException('The selected person has an invalid schedule configuration.');
        }

        $actualStart = new DateTimeImmutable($date . ' ' . $timeIn);
        $actualEnd = new DateTimeImmutable($date . ' ' . $timeOut);
        $scheduledStart = new DateTimeImmutable($date . ' ' . $parts[0]);
        $scheduledEnd = new DateTimeImmutable($date . ' ' . $parts[1]);
        if ($actualEnd <= $actualStart) {
            $actualEnd = $actualEnd->modify('+1 day');
        }
        if ($scheduledEnd <= $scheduledStart) {
            $scheduledEnd = $scheduledEnd->modify('+1 day');
        }

        $eligibleStart = max($actualStart->getTimestamp(), $scheduledStart->getTimestamp());
        $eligibleEnd = min($actualEnd->getTimestamp(), $scheduledEnd->getTimestamp());
        $renderedMinutes = max(0, (int) (($eligibleEnd - $eligibleStart) / 60));
        $overtimeMinutes = max(0, (int) (($actualEnd->getTimestamp() - $scheduledEnd->getTimestamp()) / 60));

        return [
            'rendered' => $renderedMinutes,
            'overtime' => $overtimeMinutes,
        ];
    }

    private function isValidDate($date)
    {
        $parsed = DateTime::createFromFormat('!Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date;
    }

    private function isValidTime($time)
    {
        return is_string($time) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time) === 1;
    }
}
?>