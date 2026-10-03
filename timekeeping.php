<?php
session_start();
error_reporting(0);
date_default_timezone_set("Asia/Manila");
include_once(__DIR__ . '/bars/properties.php');

$scheduleOptions = [];
foreach (array_merge($dentistSchedule, $staffSchedule) as $scheduleEntry) {
    $scheduleParts = explode('|', $scheduleEntry, 2);
    if (count($scheduleParts) === 2) {
        $scheduleOptions[$scheduleParts[0]] = $scheduleParts[1];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Timekeeping | Smiles &amp; More</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include_once('bars/sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once('bars/topbar.php'); ?>
                <div class="container-fluid">
                    <div class="card shadow mb-4">
                        <div
                            class="card-header py-3 d-flex justify-content-between align-items-center <?php echo $cards; ?>">
                            <h6 class="m-0 font-weight-bold">Timekeeping</h6>
                            <button class="btn btn-success btn-circle" type="button" id="newTimekeepingRecord"
                                title="Record time in/out" aria-label="Record time in/out">
                                <i class="fas fa-clock"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-end align-items-center mb-3">
                                <label class="mr-2 mb-2" for="filterFrom"><strong>Date From:</strong></label>
                                <input type="date" id="filterFrom" class="form-control form-control-sm mr-3 mb-2"
                                    value="<?php echo date('Y-m-d'); ?>" style="max-width: 180px;">
                                <label class="mr-2 mb-2" for="filterTo"><strong>Date To:</strong></label>
                                <input type="date" id="filterTo" class="form-control form-control-sm mr-3 mb-2"
                                    value="<?php echo date('Y-m-d'); ?>" style="max-width: 180px;">
                                <label class="mr-2 mb-0" for="tableSearch"><strong>Search:</strong></label>
                                <input type="search" id="tableSearch" class="form-control form-control-sm"
                                    placeholder="Person, date, or day type" style="max-width: 260px;">
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered text-dark" id="timekeepingTable" width="100%"
                                    cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Time In</th>
                                            <th>Time Out</th>
                                            <th>Mins. Rendered</th>
                                            <th>Overtime</th>
                                            <th>Day Type</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="timekeepingRows"></tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <th colspan="4" class="text-right">Total Minutes Rendered</th>
                                            <th id="totalRenderedMinutes" class="text-right">0 min</th>
                                            <th id="totalOvertimeMinutes" class="text-right">0 min</th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div id="timekeepingMessage" class="text-muted"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include_once('bars/footer.php'); ?>
        </div>
    </div>

    <div class="modal fade" id="timekeepingModal" tabindex="-1" role="dialog" aria-labelledby="timekeepingModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header <?php echo $cards; ?>">
                    <h5 class="modal-title" id="timekeepingModalLabel">Edit Timekeeping Record</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form id="timekeepingForm">
                    <div class="modal-body">
                        <input type="hidden" name="timekeepid" id="timekeepid">
                        <div class="form-group">
                            <label for="recordDate">Date</label>
                            <input type="date" class="form-control" name="date" id="recordDate"
                                value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="recordName">Dentist / Staff</label>
                            <select class="form-control" name="name" id="recordName" required>
                                <option value="">Select a person</option>
                                <?php foreach ($scheduleOptions as $personName => $shift): ?>
                                    <option value="<?php echo htmlspecialchars($personName, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($personName, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="timeIn">Time In</label>
                                <input type="time" class="form-control" name="timein" id="timeIn" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="timeOut">Time Out</label>
                                <input type="time" class="form-control" name="timeout" id="timeOut">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="renderedMinutesInput">Rendered Minutes</label>
                            <input type="number" class="form-control" name="rendered" id="renderedMinutesInput" step="1"
                                readonly>
                            <small class="form-text text-muted" id="renderedMinutes">Enter a time out to calculate
                                schedule-eligible minutes.</small>
                            <small class="form-text text-muted" id="overtimeMinutes">Overtime: 0 minutes.</small>
                        </div>
                        <div class="form-group mb-0">
                            <label for="dayType">Day Type</label>
                            <select class="form-control" name="dayType" id="dayType" required>
                                <option value="Regular Working Day">Regular Working Day</option>
                                <option value="Regular Holiday">Regular Holiday</option>
                                <option value="Special Holiday">Special Holiday</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Record</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="timeInModal" tabindex="-1" role="dialog" aria-labelledby="timeInModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header <?php echo $cards; ?>">
                    <h5 class="modal-title" id="timeInModalLabel">Record Time In</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form id="timeInForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="newRecordDate">Date</label>
                            <input type="date" class="form-control" name="date" id="newRecordDate"
                                value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="newRecordName">Dentist / Staff</label>
                            <select class="form-control" name="name" id="newRecordName" required>
                                <option value="">Select a person</option>
                                <?php foreach ($scheduleOptions as $personName => $shift): ?>
                                    <option value="<?php echo htmlspecialchars($personName, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($personName, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="newTimeIn">Time In</label>
                            <input type="time" class="form-control" name="timein" id="newTimeIn" required>
                        </div>
                        <div class="form-group mb-0">
                            <label for="newDayType">Day Type</label>
                            <select class="form-control" name="dayType" id="newDayType" required>
                                <option value="Regular Working Day">Regular Working Day</option>
                                <option value="Regular Holiday">Regular Holiday</option>
                                <option value="Special Holiday">Special Holiday</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Time In</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="timeOutModal" tabindex="-1" role="dialog" aria-labelledby="timeOutModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header <?php echo $cards; ?>">
                    <h5 class="modal-title" id="timeOutModalLabel">Record Time Out</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form id="timeOutForm">
                    <div class="modal-body">
                        <input type="hidden" name="timekeepid" id="timeOutTimekeepId">
                        <p class="mb-3"><strong id="timeOutPerson"></strong> <span id="timeOutDate"
                                class="text-muted"></span></p>
                        <div class="form-group mb-0">
                            <label for="recordTimeOut">Time Out</label>
                            <input type="time" class="form-control" name="timeout" id="recordTimeOut" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Time Out</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="js/custom-v2.js"></script>
    <script src="controllers/logOutConroller.js"></script>
    <script src="controllers/sessionController.js"></script>
    <script>
        window.timekeepingSchedules = <?php echo json_encode($scheduleOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    </script>
    <script src="controllers/timekeepingController-v1.js"></script>
</body>

</html>