<?php 
    include "../database/connection.php";
    session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .error-container {
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="pt-3 pb-3">Forgot Password</h3>
        <form id="forgotForm" novalidate>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control mt-2 mb-3" id="email" placeholder="Enter email" required>
            </div>
            
            <div class="js-error-container" style="display: none;">
                <p id="error-message"></p>
            </div>
            
            <button type="button" id="submitBtn" class="btn btn-primary mt-1 w-100">
                <span class="btn-text">Send</span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
        </form>
        
        <div class="register-link mt-4">
            Go back to login! <a href="../index.php">Click Here!</a>
        </div>
    </div>
    
    <script>
    function showError(msg) {
        $('#error-message').text(msg);
        $('.js-error-container').show();
    }

    $('#submitBtn').click(function () {
    const email = $('#email').val().trim();
    const $button = $('#submitBtn');
    const $btnText = $button.find('.btn-text');
    const $spinner = $button.find('.spinner-border');

    console.log("📩 Submitted email:", email);  // ✅ Log the typed email

    $('.js-error-container').hide();

    if (!email) {
        console.warn("⚠️ Email field is empty.");
        showError('Please provide an email address.');
        return;
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        console.warn("⚠️ Email format is invalid.");
        showError('Please enter a valid email address.');
        return;
    }

    console.log("✅ Passed validation. Sending AJAX...");

    $button.prop('disabled', true);
    $btnText.text('Sending...');
    $spinner.removeClass('d-none');

    $.ajax({
        url: 'forgotpass_email.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ email: email }),
        dataType: 'json'
    })
    .done(function (res) {
        console.log("✅ Server response:", res);
        if (res.success) {
            Swal.fire({
                icon: 'success',
                title: 'Email Sent',
                text: 'A verification code has been sent to your email.'
            });
            setTimeout(() => {
                window.location.href = `newpass_page.php?email=${encodeURIComponent(email)}`;
            }, 2500);
        } else {
            console.error("❌ Server responded with error:", res.message);
            showError(res.message || 'Something went wrong.');
        }
    })
    .fail(function (xhr, status, error) {
        console.error("🚨 AJAX error:", status, error, xhr.responseText);
        showError('Network error. Please try again.');
    })
    .always(function () {
        $button.prop('disabled', false);
        $btnText.text('Send');
        $spinner.addClass('d-none');
    });
});



    </script>
</body>
</html>
