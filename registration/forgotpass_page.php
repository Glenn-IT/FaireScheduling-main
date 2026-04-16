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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="login-card">
        <h3 class="pt-3 pb-3">Forgot Password</h3>

        <!-- Step 1: Email -->
        <div id="step-email">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control mt-2 mb-3" id="email" placeholder="Enter your email" required>
            </div>
            <div class="js-error-container" style="display:none;">
                <p id="error-email" class="text-danger small"></p>
            </div>
            <button type="button" id="sendCodeBtn" class="btn btn-primary mt-1 w-100">
                <span class="btn-text">Send Verification Code</span>
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
        </div>

        <!-- Step 2: Passkey verification only -->
        <div id="step-reset" style="display:none;">
            <p class="text-success small mb-3">
                <i class="fa fa-check-circle"></i> A verification code was sent to <strong id="sentTo"></strong>
            </p>

            <div class="form-group mb-3">
                <label for="passkey">Verification Code</label>
                <input type="number" class="form-control mt-2" id="passkey" placeholder="Enter 6-digit code" required>
            </div>

            <div id="error-reset-wrap" style="display:none;">
                <p id="error-reset" class="text-danger small"></p>
            </div>

            <button type="button" id="verifyCodeBtn" class="btn btn-primary w-100">
                <span class="btn-text">Verify Code</span>
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
            <button type="button" id="backBtn" class="btn btn-link w-100 mt-2 small">← Use a different email</button>
        </div>

        <!-- Step 3: New password fields (only shown after code verified) -->
        <div id="step-newpass" style="display:none;">
            <p class="text-success small mb-3">
                <i class="fa fa-check-circle"></i> Code verified! Please set your new password.
            </p>

            <div class="form-group position-relative mb-3">
                <label for="newPassword">New Password</label>
                <div class="position-relative">
                    <input type="password" class="form-control mt-2" id="newPassword" required
                           placeholder="Enter new password"
                           style="padding-right: 2.5rem;">
                    <span toggle="#newPassword" class="fa fa-fw fa-eye toggle-password"
                          style="position:absolute; top:50%; right:12px; transform:translateY(-50%); cursor:pointer; color:#6c757d;"></span>
                </div>
                <small class="text-muted">Min 8 chars, 1 uppercase, 1 number, 1 special character</small>
            </div>

            <div class="form-group position-relative mb-3">
                <label for="confirmpassword">Confirm Password</label>
                <div class="position-relative">
                    <input type="password" class="form-control mt-2" id="confirmpassword" required
                           style="padding-right: 2.5rem;">
                    <span toggle="#confirmpassword" class="fa fa-fw fa-eye toggle-password"
                          style="position:absolute; top:50%; right:12px; transform:translateY(-50%); cursor:pointer; color:#6c757d;"></span>
                </div>
            </div>

            <div id="error-newpass-wrap" style="display:none;">
                <p id="error-newpass" class="text-danger small"></p>
            </div>

            <button type="button" id="resetBtn" class="btn btn-primary w-100">
                <span class="btn-text">Reset Password</span>
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
        </div>

        <div class="register-link mt-4">
            Go back to login! <a href="../index.php">Click Here!</a>
        </div>
    </div>

    <script>
    // Toggle password visibility
    $(document).on('click', '.toggle-password', function () {
        let input = $($(this).attr("toggle"));
        let icon = $(this);
        if (input.attr("type") === "password") {
            input.attr("type", "text");
            icon.removeClass("fa-eye").addClass("fa-eye-slash");
        } else {
            input.attr("type", "password");
            icon.removeClass("fa-eye-slash").addClass("fa-eye");
        }
    });

    // ── Step 1: Send verification code ──
    $('#sendCodeBtn').click(function () {
        const email = $('#email').val().trim();
        const $btn = $(this);
        const $spinner = $btn.find('.spinner-border');
        const $btnText = $btn.find('.btn-text');

        $('#error-email').text('');
        $('#error-email').closest('div').hide();

        if (!email) { showError('email', 'Please provide an email address.'); return; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showError('email', 'Please enter a valid email address.'); return; }

        $btn.prop('disabled', true);
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
            if (res.success) {
                $('#sentTo').text(email);
                $('#step-reset').data('email', email);
                $('#step-email').hide();
                $('#step-reset').show();
                $('#passkey').val('').focus();
            } else {
                showError('email', res.message || 'Something went wrong.');
            }
        })
        .fail(function () { showError('email', 'Network error. Please try again.'); })
        .always(function () {
            $btn.prop('disabled', false);
            $btnText.text('Send Verification Code');
            $spinner.addClass('d-none');
        });
    });

    // ── Step 2: Verify code only ──
    $('#verifyCodeBtn').click(function () {
        const email   = $('#step-reset').data('email');
        const passkey = $('#passkey').val().trim();
        const $btn    = $(this);
        const $spinner = $btn.find('.spinner-border');
        const $btnText = $btn.find('.btn-text');

        $('#error-reset-wrap').hide();

        if (!passkey) { showError('reset', 'Please enter the verification code.'); return; }

        $btn.prop('disabled', true);
        $btnText.text('Verifying...');
        $spinner.removeClass('d-none');

        $.ajax({
            url: 'verify_passkey.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ email: email, passkey: passkey }),
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                // Code correct — show password fields
                $('#step-reset').hide();
                $('#step-newpass').data('email', email).data('passkey', passkey).show();
                $('#newPassword').val('').focus();
                $('#confirmpassword').val('');
            } else {
                showError('reset', res.message || 'Incorrect code. Please try again.');
                $('#passkey').val('').focus();
            }
        })
        .fail(function () { showError('reset', 'Network error. Please try again.'); })
        .always(function () {
            $btn.prop('disabled', false);
            $btnText.text('Verify Code');
            $spinner.addClass('d-none');
        });
    });

    // ── Step 3: Set new password ──
    $('#resetBtn').click(function () {
        const email   = $('#step-newpass').data('email');
        const passkey = $('#step-newpass').data('passkey');
        const newPass  = $('#newPassword').val();
        const confPass = $('#confirmpassword').val();
        const $btn     = $(this);
        const $spinner = $btn.find('.spinner-border');
        const $btnText = $btn.find('.btn-text');

        $('#error-newpass-wrap').hide();

        if (!newPass) { showError('newpass', 'Please enter a new password.'); return; }

        const passRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passRegex.test(newPass)) {
            showError('newpass', 'Password must be ≥8 chars, include 1 uppercase, 1 number, and 1 special character (@$!%*?&).');
            return;
        }
        if (newPass !== confPass) { showError('newpass', 'Passwords do not match.'); return; }

        $btn.prop('disabled', true);
        $btnText.text('Updating...');
        $spinner.removeClass('d-none');

        $.ajax({
            url: 'newpass.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ email: email, password: newPass, passkey: passkey }),
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Password Updated!',
                    text: 'Your password has been reset. Please log in.',
                    confirmButtonColor: '#135b96'
                }).then(() => { window.location.href = '../index.php'; });
            } else {
                showError('newpass', res.message || res.error || 'Something went wrong. Please restart.');
            }
        })
        .fail(function () { showError('newpass', 'Network error. Please try again.'); })
        .always(function () {
            $btn.prop('disabled', false);
            $btnText.text('Reset Password');
            $spinner.addClass('d-none');
        });
    });

    // ── Back: return to email step ──
    $('#backBtn').click(function () {
        $('#step-reset').hide();
        $('#step-email').show();
        $('#passkey').val('');
        $('#error-reset-wrap').hide();
    });

    function showError(step, msg) {
        if (step === 'email') {
            $('#error-email').text(msg);
            $('#error-email').closest('.js-error-container').show();
        } else if (step === 'reset') {
            $('#error-reset').text(msg);
            $('#error-reset-wrap').show();
        } else {
            $('#error-newpass').text(msg);
            $('#error-newpass-wrap').show();
        }
    }
    </script>
</body>
</html>
