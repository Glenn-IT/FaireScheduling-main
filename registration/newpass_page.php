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
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="login-card">
        <h3 class="pt-3 pb-3">Create Password</h3>
        <form id="newpassForm">
            <div class="form-group">
                <label for="passkey">Passkey</label>
                <input type="number" id="passkey" name="passkey" class="form-control mt-2 mb-3" placeholder="Enter passkey" required>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_GET['email']); ?>" hidden>
            </div>

 
            <div class="form-group position-relative mt-3">
                <label for="newPassword">Password</label>
                <input type="password" class="form-control mt-2" id="newPassword" required>
                <span toggle="#newPassword" class="fa fa-fw fa-eye field-icon toggle-password pt-1"
                        style="position:absolute; top:38px; right:15px; cursor:pointer;"></span>
            </div>

            <div class="form-group position-relative mt-3 mb-3">
                <label for="confirmpassword">Confirm Password</label>
                <input type="password" class="form-control mt-2" id="confirmpassword" required>
                <span toggle="#confirmpassword" class="fa fa-fw fa-eye field-icon toggle-password pt-1"
                    style="position:absolute; top:38px; right:15px; cursor:pointer;"></span>
            </div>

            <div class="js-error-container mb-3" style="display: none;">
                <p id="error-message">sdsd</p>
            </div>

            <button type="button" id="submitBtn" class="btn btn-primary btn-login w-100">Confirm</button>
        </form>

        <div class="register-link mt-4">
            Go back to login! <a href="../index.php">Click Here!</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
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

        function showError(message) {
            $('#error-message').text(message);
            $('.js-error-container').show();
        }


        $(document).ready(function () {
            $("#submitBtn").click(function (event) {
                event.preventDefault();

                let email = $("#email").val().trim();
                let passkey = $("#passkey").val().trim();
                let newPass = $("#newPassword").val().trim();
                let confirmPass = $("#confirmpassword").val().trim();

                $('.error-container').hide(); // Clear previous error

                const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

                if (!email) {
                    showError("Email missing. Please submit the reset request again.");
                    return;
                }

                if (!passkey || !newPass || !confirmPass) {
                    showError("All fields are required.");
                    return;
                }

                if (!passwordRegex.test(newPass)) {
                    showError("Password must be at least 8 characters, include 1 uppercase, 1 number, and 1 special character.");
                    return;
                }

                if (newPass !== confirmPass) {
                    showError("Passwords do not match.");
                    return;
                }

                $.ajax({
                    url: "newpass.php",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({ email: email, password: newPass, passkey: passkey }),
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Password Updated",
                                text: "Please proceed to log in.",
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                window.location.href = "../index.php";
                            });
                        } else {
                            showError(response.error || "Something went wrong.");
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", xhr.responseText);
                        showError("Server error. Please try again.");
                    }
                });
            });
        });
    </script>
</body>
</html>
