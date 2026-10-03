$(function () {
    loadTimekeeping();

    $('#newTimekeepingRecord').on('click', function () {
        $('#timeInForm')[0].reset();
        $('#timeInModal').modal('show');
    });

    $('#tableSearch').on('input', loadTimekeeping);
    $('#filterFrom, #filterTo').on('change', loadTimekeeping);
    $('#recordName, #timeIn, #timeOut').on('input change', updateRenderedPreview);

    $('#timekeepingModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        if (!button.length || !button.data('id')) {
            return;
        }

        $('#timekeepid').val(button.data('id'));
        $('#recordDate').val(button.data('date'));
        $('#recordName').val(button.data('name'));
        $('#timeIn').val(button.data('timein'));
        $('#timeOut').val(button.data('timeout'));
        $('#dayType').val(button.data('daytype'));
        updateRenderedPreview();
    });

    $('#timeOutModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $('#timeOutTimekeepId').val(button.data('id'));
        $('#timeOutPerson').text(button.data('name'));
        $('#timeOutDate').text(button.data('date'));
        $('#timeOutForm')[0].reset();
        $('#timeOutTimekeepId').val(button.data('id'));
    });

    $('#timeInForm, #timekeepingForm').on('submit', function (event) {
        event.preventDefault();
        saveTimekeepingForm(this);
    });

    $('#timeOutForm').on('submit', function (event) {
        event.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'timeout');
        sendTimekeepingForm(formData, '#timeOutModal');
    });
});

function saveTimekeepingForm(form) {
    var formData = new FormData(form);
    var modalId = form.id === 'timeInForm' ? '#timeInModal' : '#timekeepingModal';
    sendTimekeepingForm(formData, modalId);
}

function sendTimekeepingForm(formData, modalId) {
    $.ajax({
        url: 'services/upsertTimekeepingService.php',
        data: formData,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function (result) {
            if (result.trim() === 'success') {
                toastSuccess('Timekeeping record saved.');
                $(modalId).modal('hide');
                loadTimekeeping();
            } else {
                toastError(result);
            }
        },
        error: function (xhr) {
            toastError(xhr.responseText || 'Unable to save the timekeeping record.');
        }
    });
}

function loadTimekeeping() {
    var fromDate = $('#filterFrom').val();
    var toDate = $('#filterTo').val();
    if (fromDate && toDate && fromDate > toDate) {
        $('#timekeepingRows').empty();
        $('#totalRenderedMinutes').text('0 min');
        $('#totalOvertimeMinutes').text('0 min');
        $('#timekeepingMessage').text('Date From must be on or before Date To.');
        return;
    }

    $.ajax({
        url: 'services/timekeepingListService.php',
        type: 'POST',
        data: {
            from: fromDate,
            to: toDate,
            search: $('#tableSearch').val() || ''
        },
        success: function (result) {
            $('#timekeepingRows').html(result);
            var totalMinutes = 0;
            var totalOvertimeMinutes = 0;
            $('#timekeepingRows tr').each(function () {
                totalMinutes += Number(this.dataset.renderedMinutes) || 0;
                totalOvertimeMinutes += Number(this.dataset.overtimeMinutes) || 0;
            });
            $('#totalRenderedMinutes').text(totalMinutes.toLocaleString() + ' min');
            $('#totalOvertimeMinutes').text(totalOvertimeMinutes.toLocaleString() + ' min');
            $('#timekeepingMessage').text($('#timekeepingRows').children().length ? '' : 'No timekeeping records found.');
        },
        error: function () {
            $('#timekeepingMessage').text('Unable to load timekeeping records.');
        }
    });
}

function updateRenderedPreview() {
    var person = $('#recordName').val();
    var timeIn = $('#timeIn').val();
    var timeOut = $('#timeOut').val();
    var shift = window.timekeepingSchedules[person];

    if (!shift || !timeIn || !timeOut) {
        $('#renderedMinutesInput').val('');
        $('#renderedMinutes').text('Enter a time out to calculate schedule-eligible minutes.');
        $('#overtimeMinutes').text('Overtime: 0 minutes.');
        return;
    }

    var shiftParts = shift.split('-');
    var scheduledStart = toMinutes(shiftParts[0]);
    var scheduledEnd = toMinutes(shiftParts[1]);
    var actualStart = toMinutes(timeIn);
    var actualEnd = toMinutes(timeOut);

    if (scheduledEnd <= scheduledStart) scheduledEnd += 1440;
    if (actualEnd <= actualStart) actualEnd += 1440;

    var start = Math.max(scheduledStart, actualStart);
    var end = Math.min(scheduledEnd, actualEnd);
    var eligibleMinutes = Math.max(0, end - start);
    var overtimeMinutes = Math.max(0, actualEnd - scheduledEnd);
    $('#renderedMinutesInput').val(eligibleMinutes);
    $('#renderedMinutes').text(eligibleMinutes + ' schedule-eligible minutes');
    $('#overtimeMinutes').text('Overtime: ' + overtimeMinutes + ' minutes.');
}

function toMinutes(time) {
    var parts = time.split(':');
    return (Number(parts[0]) * 60) + Number(parts[1]);
}