<?php
session_start();
require_once(__DIR__ . '/databaseService.php');
$service = new TimekeepingListService();
$service->process($_POST['from'] ?? date('Y-m-d'), $_POST['to'] ?? date('Y-m-d'), $_POST['search'] ?? '');

class TimekeepingListService
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->dbConnection();
    }

    public function process($fromDate, $toDate, $search)
    {
        $fromDate = $this->normalizeDate($fromDate);
        $toDate = $this->normalizeDate($toDate);
        $conditions = [];
        $parameters = [];

        if ($fromDate !== null) {
            $conditions[] = 'date >= :date_from';
            $parameters[':date_from'] = $fromDate;
        }
        if ($toDate !== null) {
            $conditions[] = 'date <= :date_to';
            $parameters[':date_to'] = $toDate;
        }
        if ($search !== '') {
            $conditions[] = '(name LIKE :name_search OR date LIKE :date_search OR dayType LIKE :type_search)';
            $searchParam = '%' . $search . '%';
            $parameters[':name_search'] = $searchParam;
            $parameters[':date_search'] = $searchParam;
            $parameters[':type_search'] = $searchParam;
        }

        $query = 'SELECT timekeepid, date, name, timein, timeout, rendered, overtime, dayType FROM timekeeping';
        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $query .= ' ORDER BY date DESC, timekeepid DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute($parameters);

        $canEditFinalized = in_array((int) ($_SESSION['account_type'] ?? -1), [0, 100], true);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = htmlspecialchars($row['timekeepid'], ENT_QUOTES, 'UTF-8');
            $date = htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8');
            $name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
            $timeIn = htmlspecialchars(substr((string) $row['timein'], 0, 5), ENT_QUOTES, 'UTF-8');
            $rawTimeOut = (string) $row['timeout'];
            $timeOut = htmlspecialchars($rawTimeOut === '00:00:00' ? '' : substr($rawTimeOut, 0, 5), ENT_QUOTES, 'UTF-8');
            $dayType = htmlspecialchars($row['dayType'], ENT_QUOTES, 'UTF-8');
            $renderedMinutes = (int) round((float) $row['rendered']);
            $overtimeMinutes = (int) round((float) ($row['overtime'] ?? 0));
            $rendered = number_format($renderedMinutes);
            $overtime = number_format($overtimeMinutes);
            $hasTimeOut = !empty($rawTimeOut) && $rawTimeOut !== '00:00:00';
            $canEdit = $canEditFinalized;

            echo '<tr data-rendered-minutes="' . $renderedMinutes . '" data-overtime-minutes="' . $overtimeMinutes . '">';
            echo '<td>' . $date . '</td><td>' . $name . '</td><td>' . $timeIn . '</td><td>' . $timeOut . '</td>';
            echo '<td class="text-right">' . $rendered . ' min</td><td class="text-right">' . $overtime . ' min</td><td>' . $dayType . '</td><td class="text-center">';
            if (!$hasTimeOut) {
                echo '<button type="button" class="btn btn-warning btn-circle mr-1" data-toggle="modal" data-target="#timeOutModal"'
                    . ' data-id="' . $id . '" data-date="' . $date . '" data-name="' . $name . '"'
                    . ' title="Record time out" aria-label="Record time out"><i class="fas fa-sign-out-alt"></i></button>';
            }
            if ($canEdit) {
                echo '<button type="button" class="btn btn-primary btn-circle" data-toggle="modal" data-target="#timekeepingModal"'
                    . ' data-id="' . $id . '" data-date="' . $date . '" data-name="' . $name . '"'
                    . ' data-timein="' . $timeIn . '" data-timeout="' . $timeOut . '" data-daytype="' . $dayType . '"'
                    . ' title="Edit record" aria-label="Edit record"><i class="fas fa-edit"></i></button>';
                echo '<button type="button" class="btn btn-danger btn-circle ml-1 delete-timekeeping"'
                    . ' data-id="' . $id . '" data-name="' . $name . '" data-date="' . $date . '"'
                    . ' title="Delete record" aria-label="Delete record"><i class="fas fa-trash"></i></button>';
            } elseif ($hasTimeOut) {
                echo '<span class="text-muted" title="Only account types 0 and 100 can edit a completed record">Locked</span>';
            }
            echo '</td></tr>';
        }
    }

    private function normalizeDate($date)
    {
        if ($date === null || $date === '') {
            return null;
        }
        if (!is_string($date)) {
            return date('Y-m-d');
        }

        $parsedDate = DateTime::createFromFormat('!Y-m-d', $date);
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
            return date('Y-m-d');
        }
        return $date;
    }
}
?>