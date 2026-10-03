document.addEventListener('DOMContentLoaded', function () {
    $('#dentist').on('change', refreshDentistRate);
    $('#rateForm').on('submit', saveDentistRate);
    refreshDentistRate();

    // Automatically load report if the dentist list already has a selected value.
    if (document.getElementById('dentist').value !== '') {
        loadPayrollReport();
    }
});

function refreshDentistRate() {
    const dentist = document.getElementById('dentist').value;
    const button = document.getElementById('updateRateButton');
    const rateLabel = document.getElementById('currentRatePerMinute');
    if (!button || !rateLabel) {
        return;
    }

    button.disabled = true;
    rateLabel.textContent = '0.000';
    if (!dentist) {
        return;
    }

    $.ajax({
        url: 'services/dentistPayrollRateService.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'get', dentist: dentist },
        success: function (response) {
            if (document.getElementById('dentist').value === dentist) {
                rateLabel.textContent = Number(response.rate).toFixed(3);
                button.disabled = false;
            }
        },
        error: function (xhr) {
            $('#taxBulkStatus').text(xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Unable to load the dentist rate.');
        }
    });
}

function openRateModal() {
    const dentist = document.getElementById('dentist').value;
    if (!dentist) {
        return;
    }

    $('#rateDentist').val(dentist);
    $('#ratePerMinuteInput').val('');
    $('#rateModalMessage').removeClass('text-danger text-success').text('');
    $('#rateModal').modal('show');

    $.ajax({
        url: 'services/dentistPayrollRateService.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'get', dentist: dentist },
        success: function (response) {
            if (document.getElementById('dentist').value === dentist) {
                $('#ratePerMinuteInput').val(Number(response.rate).toFixed(3));
                $('#currentRatePerMinute').text(Number(response.rate).toFixed(3));
            }
        },
        error: function (xhr) {
            $('#rateModalMessage').addClass('text-danger').text(xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Unable to load the dentist rate.');
        }
    });
}

function saveDentistRate(event) {
    event.preventDefault();
    const dentist = document.getElementById('rateDentist').value;
    const rate = document.getElementById('ratePerMinuteInput').value;
    const saveButton = $('#saveRateButton');

    saveButton.prop('disabled', true);
    $('#rateModalMessage').removeClass('text-danger text-success').text('');
    $.ajax({
        url: 'services/dentistPayrollRateService.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'save', dentist: dentist, rate: rate },
        success: function (response) {
            $('#currentRatePerMinute').text(Number(response.rate).toFixed(3));
            $('#rateModalMessage').addClass('text-success').text('Rate saved.');
            $('#rateModal').modal('hide');
            loadPayrollReport();
        },
        error: function (xhr) {
            $('#rateModalMessage').addClass('text-danger').text(xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Unable to save the dentist rate.');
        },
        complete: function () {
            saveButton.prop('disabled', false);
        }
    });
}

function loadPayrollReport() {

    var dentist = document.getElementById('dentist').value;
    var from = document.getElementById('from').value;
    var to = document.getElementById('to').value;

    if (!dentist) {
        document.getElementById('responseBody').innerHTML = '<div class="alert alert-warning">Please select a dentist before loading the report.</div>';
        document.getElementById('payrollTotals').style.display = 'none';
        return;
    }

    var subtitle = 'Date Range: ' + (from || '-') + ' to ' + (to || '-') + ' | Dentist: ' + dentist;
    var subtitleEl = document.getElementById('reportSubtitle');
    if (subtitleEl) {
        subtitleEl.innerText = subtitle;
    }
    document.getElementById('payslipDentist').innerText = dentist;
    document.getElementById('payslipPeriod').innerText = (from || '-') + ' to ' + (to || '-');


    var fd = new FormData();
    fd.append('dentist', dentist);
    fd.append('from', from);
    fd.append('to', to);

    document.getElementById('loading').style.display = 'block';
    document.getElementById('responseBody').innerHTML = '';


    $.ajax({
        url: 'services/dentistPayrollReportService.php',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function (result) {
            document.getElementById('responseBody').innerHTML = result;
            loadPayrollAdjustmentReport(); // Load the payroll adjustment report after the main report is loaded

        },
        complete: function () {
            document.getElementById('loading').style.display = 'none';
            recalculateTotal();

        }
    });
}

function loadPayrollAdjustmentReport() {

    var dentist = document.getElementById('dentist').value;
    var from = document.getElementById('from').value;
    var to = document.getElementById('to').value;

    if (!dentist) {
        document.getElementById('responseBody-dentistPayrollAdjustments').innerHTML = '';
        return;
    }
    var fd = new FormData();
    fd.append('dentist', dentist);
    fd.append('from', from);
    fd.append('to', to);

    document.getElementById('loading').style.display = 'block';
    document.getElementById('responseBody-dentistPayrollAdjustments').innerHTML = '';


    $.ajax({
        url: 'services/dentistPayrollReportSubService.php',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function (result) {
            document.getElementById('responseBody-dentistPayrollAdjustments').innerHTML = result;
            recalculateNetPay(); // Recalculate net pay after loading the payroll adjustment report

        },
        complete: function () {
            document.getElementById('loading').style.display = 'none';
        }
    });
}

function printPayroll() {
    window.print();
}

function openPayslipModal() {
    $('#payslipModal').modal('show');
}

function updateComissionAmount(thisObject) {
    const row = thisObject.closest('tr');
    const priceCell = row.querySelector('.price');
    const rawMaterialCell = row.querySelector('.raw_material');
    const taxCell = row.querySelector('.tax-amount');
    const subchargeCell = row.querySelector('.subcharge-amount');
    const commissionCell = row.querySelector('.commission');
    const paymentTypeCell = row.querySelector('.payment-type');
    const select = row.querySelector('select[name="commision_rate"]');
    if (!select) {
        return;
    }
    const price = parseFloat(priceCell.textContent.replace(/,/g, '')) || 0;
    const rawMaterial = parseFloat(rawMaterialCell.querySelector('input').value) || 0;
    const isTax = row.querySelector('.tax-checkbox').checked;
    const tax = isTax ? Math.round(price * 0.10 * 100) / 100 : 0;
    const isCreditCard = paymentTypeCell.textContent.trim().toLowerCase() === 'credit card';
    const subcharge = isCreditCard ? Math.round(price * 0.04 * 100) / 100 : 0;
    taxCell.textContent = tax.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    subchargeCell.textContent = subcharge.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    let commission;
    if (select.value === 'Other') {
        if (thisObject.matches('select[name="commision_rate"]')) {
            const amount = prompt('Enter commission amount:');
            if (amount === null) {
                select.value = '0';
                commission = 0;
            } else {
                commission = parseFloat(amount.replace(/,/g, ''));
            }

            if (isNaN(commission) || commission < 0) {
                alert('Please enter a valid commission amount.');
                select.value = '0';
                commission = 0;
            }
        } else {
            commission = parseFloat(commissionCell.textContent.replace(/,/g, '')) || 0;
        }
    } else {
        const rate = parseFloat(select.value);
        commission = Math.round((price - tax - subcharge - rawMaterial) * (rate / 100) * 100) / 100;
    }

    commissionCell.textContent = commission.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    clearTimeout(row.commissionSaveTimer);
    row.commissionSaveTimer = setTimeout(function () {
        $.ajax({
            url: 'services/updateDentistPayrollCommissionService.php',
            type: 'POST',
            dataType: 'json',
            data: {
                tsubpayid: row.dataset.tsubpayid,
                isTax: isTax ? 'true' : 'false',
                lab_fee: rawMaterial,
                commision_rate: select.value,
                commision: commission
            },
            error: function (xhr) {
                console.error('Unable to save payroll values:', xhr.responseText);
            }
        });
    }, 250);

    recalculateTotal();
}

$(document).on('input', '#commissionTable .raw-material-input', function () {
    updateComissionAmount(this);
});

$(document).on('change', '#commissionTable .tax-checkbox, #commissionTable select[name="commision_rate"]', function () {
    updateComissionAmount(this);
});

$(document).on('click', '#checkAllTax', function () {
    updateAllTaxCheckboxes(true);
});

$(document).on('click', '#uncheckAllTax', function () {
    updateAllTaxCheckboxes(false);
});

async function updateAllTaxCheckboxes(shouldCheck) {
    const checkboxes = Array.from(document.querySelectorAll('#responseBody #commissionTable .tax-checkbox'));
    if (checkboxes.length === 0) {
        $('#taxBulkStatus').text('Load a payroll report first.');
        return;
    }

    const buttons = $('#checkAllTax, #uncheckAllTax');
    buttons.prop('disabled', true);
    const changedCheckboxes = checkboxes.filter(function (checkbox) {
        return checkbox.checked !== shouldCheck;
    });
    $('#taxBulkStatus').text(changedCheckboxes.length ? 'Updating tax records...' : 'All tax selections already match.');

    for (let index = 0; index < changedCheckboxes.length; index++) {
        const checkbox = changedCheckboxes[index];
        checkbox.checked = shouldCheck;
        checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        $('#taxBulkStatus').text('Updating tax record ' + (index + 1) + ' of ' + changedCheckboxes.length + '...');
        await new Promise(function (resolve) {
            setTimeout(resolve, 500);
        });
    }

    buttons.prop('disabled', false);
    if (changedCheckboxes.length) {
        $('#taxBulkStatus').text('Tax records updated.');
    }
}

function recalculateTotal() {
    let total = 0;
    document.querySelectorAll('#commissionTable .commission').forEach(function (cell) {
        let amount = parseFloat(cell.textContent.replace(/,/g, ''));

        if (!isNaN(amount)) {
            total += amount;
        }
    });

    document.getElementById('totalCommission').textContent =
        total.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    recalculateNetPay(); // Recalculate net pay after updating total commission 
}


function recalculateNetPay() {
    const overtimeTotal = document.getElementById('totalOvertime');
    document.getElementById('totalGrossPay').textContent =
        ((parseFloat(document.getElementById('totalCommission').textContent.replace(/,/g, '') || 0)) + parseFloat(document.getElementById('totalBasicSalary').textContent.replace(/,/g, '') || 0) + (overtimeTotal ? parseFloat(overtimeTotal.textContent.replace(/,/g, '')) || 0 : 0) + parseFloat(document.getElementById('totalAdditional').textContent.replace(/,/g, '') || 0)).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });


    const totalGrossPay = parseFloat(
        document.getElementById('totalGrossPay').textContent.replace(/,/g, '')
    ) || 0;



    const totalDeductions = parseFloat(
        document.getElementById('totalDeductions').textContent.replace(/,/g, '')
    ) || 0;

    const netPay =
        totalGrossPay -

        totalDeductions;

    document.getElementById('netpay').textContent =
        netPay.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

    populatePayslip();
}


function populatePayslip() {

    // =========================
    // Employee / Filter Details
    // =========================

    const dentist = document.getElementById('dentist').value;
    const from = document.getElementById('from').value;
    const to = document.getElementById('to').value;

    document.getElementById('payslipDentist').textContent =
        dentist || '-';

    // Format dates
    if (from && to) {
        const fromDate = new Date(from + 'T00:00:00');
        const toDate = new Date(to + 'T00:00:00');

        const dateOptions = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };

        document.getElementById('payslipPeriods').textContent =
            fromDate.toLocaleDateString('en-US', dateOptions) +
            ' - ' +
            toDate.toLocaleDateString('en-US', dateOptions);
    } else {
        document.getElementById('payslipPeriods').textContent = '-';
    }

    // Pay date = "To" date



    // =========================
    // Payroll Values
    // =========================
    const commission =
        parseFloat(document.getElementById('totalCommission').textContent.replace(/,/g, '')) || 0;


    const basicSalary = parseFloat(
        document.getElementById('totalBasicSalary').textContent.replace(/,/g, '')
    ) || 0;

    const overtimeTotal = document.getElementById('totalOvertime');
    const overtime = overtimeTotal
        ? parseFloat(overtimeTotal.textContent.replace(/,/g, '')) || 0
        : 0;

    const additional =
        parseFloat(document.getElementById('totalAdditional').textContent.replace(/,/g, '')) || 0;

    const gross = basicSalary + overtime + additional + commission;

    const deductions =
        document.getElementById('totalDeductions').textContent;



    const netPay =
        document.getElementById('netpay').textContent;


    // =========================
    // Populate Payslip
    // =========================
    document.getElementById('payslipCommission').textContent = (commission).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });;

    document.getElementById('payslipBasicSalary').textContent =
        basicSalary.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });;

    document.getElementById('payslipOvertime').textContent =
        overtime.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

    document.getElementById('payslipAdditional').textContent =
        additional.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });;

    document.getElementById('payslipGrossPay').textContent =
        gross.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });;

    document.getElementById('payslipDeductions').textContent =
        deductions.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });;



    document.getElementById('payslipOtherDeductions').textContent =
        deductions.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });;

    document.getElementById('payslipNetPay').textContent =
        netPay.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

    const basicSalaryDetails = document.getElementById('basicSalaryDetails');
    const minutesRendered = basicSalaryDetails ? Number(basicSalaryDetails.dataset.minutesRendered) || 0 : 0;
    const ratePerMinute = basicSalaryDetails ? Number(basicSalaryDetails.dataset.ratePerMinute) || 0 : 0;
    document.getElementById('payslipDaysRendered').textContent =
        'Minutes Rendered: ' + minutesRendered.toLocaleString('en-US');
    document.getElementById('payslipRatePerDay').textContent =
        'Rate Per Minute: ' + ratePerMinute.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

    const overtimeDetails = document.getElementById('overtimeDetails');
    const overtimeMinutes = overtimeDetails ? Number(overtimeDetails.dataset.overtimeMinutes) || 0 : 0;
    const overtimeRatePerMinute = overtimeDetails ? Number(overtimeDetails.dataset.ratePerMinute) || 0 : 0;
    document.getElementById('payslipOvertimeMinutes').textContent =
        'Overtime Minutes: ' + overtimeMinutes.toLocaleString('en-US');
    document.getElementById('payslipOvertimeRate').textContent =
        'Rate Per Minute: ' + overtimeRatePerMinute.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

    document.getElementById('payslipDentists').textContent = document.getElementById('dentist').value || '-';
}