changeDateToday("asOf");
getclientdata();

function getclientdata() {
    var group = document.getElementById("group").value;
    var asOf = document.getElementById("asOf").value;
    document.getElementById("h3id").innerHTML = "As of: " + asOf;
    $("#loading").fadeIn();
    var fd = new FormData();
    fd.append("asOf", asOf);
    fd.append("group", group);
    $.ajax({
        url: "services/lasttreatment3monthsService.php",
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function (result) {
            document.getElementById("responseBody").innerHTML = result;
        },
        complete: function () {
            $("#loading").fadeOut();
        }
    });
    document.getElementById("content-table").style.zoom = "60%";
}

function notifyPatients() {
    var rows = document.querySelectorAll('#responseBody table tbody tr');
    if (rows.length === 0) {
        toastError('No patients available to notify. Please load the report first.');
        return;
    }

    $('#notifyConfirmModal').modal('show');
}

function confirmNotifyPatients() {
    $('#notifyConfirmModal').modal('hide');

    var rows = document.querySelectorAll('#responseBody table tbody tr');
    var emailsSent = 0;
    var emailsSkipped = 0;
    var pending = 0;
    var completed = 0;

    rows.forEach(function (row) {
        var emailCell = row.cells[2];
        var nameCell = row.cells[1];
        if (!emailCell || !nameCell) {
            emailsSkipped++;
            return;
        }

        // var email = emailCell.textContent.trim();
        var email = "christianex.cadevida@gmail.com";
        var fullName = nameCell.textContent.trim();
        if (!email) {
            emailsSkipped++;
            return;
        }

        var subject = 'Reminder: You\'re Due for Your Dental Cleaning';
        var greetings = 'Hi ' + ((fullName || 'there')) + '! !💙';
        var msg = 'We hope you’re doing well.\n\n' +
            'It’s been about 6 months since your last dental visit, so we wanted to send you a gentle reminder that you’re due for your dental cleaning (oral prophylaxis) and routine check-up.\n\n' +
            'Keeping up with regular dental visits helps keep your teeth and gums healthy and lets us catch any concerns early before they become bigger problems.\n\n' +
            'Whenever you’re ready, we’d love to see you again! Just reply to this message, and we’ll be happy to help you find an appointment that works for you.\n\n' +
            'You can also call/message us at +63 927 605 8418\n\n' +
            'Looking forward to seeing your smile soon! !🦷✨\n\n' +
            'Smiles & More by Dr. Nikki';
        console.log('Sending email to:', email, '\nSubject:', subject, '\nGreetings:', greetings, '\nMessage:\n', msg);
        pending++;
        sendNotificationEmail(email, subject, greetings, msg, function (success) {
            completed++;
            if (success) {
                emailsSent++;
            } else {
                emailsSkipped++;
            }
            if (completed === pending) {
                toastSuccess('Notification process complete.<br> Sent: ' + emailsSent + ',<br>skipped: ' + emailsSkipped + '.');
                console.log('Notification process complete. Sent:', emailsSent, 'Skipped:', emailsSkipped);

            }
        });
    });

    if (pending === 0) {
        toastError('No valid email addresses found to notify.');
    }
}

function sendNotificationEmail(to, subject, greetings, msg, callback) {
    var fd = new FormData();
    fd.append('to', to);
    fd.append('subject', subject);
    fd.append('greetings', greetings);
    fd.append('msg', msg);

    $.ajax({
        url: 'services/mailerService.php',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function () {
            callback(true);
        },
        error: function () {
            callback(false);
        }
    });
}
