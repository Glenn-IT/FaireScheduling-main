<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="registration/style.css?<?= time(); ?>">
</head>
<body>
  <div class="login-card">
    <h3>Login</h3>
    <form>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" placeholder="Enter email" required>
      </div>

      <div class="form-group position-relative">
        <label for="password">Password</label>
        <input type="password" class="form-control" id="password" required>
        <span toggle="#password" class="fa fa-fw fa-eye field-icon toggle-password pt-1"
              style="position:absolute; top:38px; right:15px; cursor:pointer;"></span>
      </div>


      <div class="js-error-container" style="display: none;">
        <p id="error-message"></p>
      </div>
      <button type="submit" id="submitBtn" class="btn btn-primary btn-block">Sign In</button>
    </form>
    
      <div class="my-3">
        <a href="registration/forgotpass_page.php" style="font-weight: bold; color: #135b96;">Forgot Password?</a>
      </div>
    <div class="register-link">
      Don't have an account? <a href="registration/registrationpage.php">Register here</a>
    </div>
  </div>


  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
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


    function lockLoginForm() {
      const lockTime = Date.now() + 30000; // 30 seconds
      localStorage.setItem("login_lock_until", lockTime);
      updateLockoutUI();
    }

    function updateLockoutUI() {
      const lockUntil = localStorage.getItem("login_lock_until");
      if (!lockUntil) return;

      const now = Date.now();
      const remaining = Math.max(0, Math.floor((lockUntil - now) / 1000));

      if (remaining > 0) {
        $("#email, #password, #submitBtn").prop("disabled", true);
        $(".js-error-container").show();
        $("#submitBtn").text("Locked");
        $("#error-message").html(`Too many failed attempts. Please wait <strong>${remaining}s</strong> before trying again.`);
        setTimeout(updateLockoutUI, 1000);
      } else {
        localStorage.removeItem("login_lock_until");
        localStorage.removeItem("login_attempts");
        $("#email, #password, #submitBtn").prop("disabled", false);
        $("#submitBtn").text("Sign In");
        $(".js-error-container").hide();
        $("#error-message").text("");
      }
    }


    document.addEventListener('DOMContentLoaded', function () {
      updateLockoutUI();

      $("#submitBtn").click(function (event) {
        event.preventDefault();

        let email = $("#email").val().trim();
        let password = $("#password").val().trim();

        if (email === "" || password === "") {
          $(".js-error-container").show();
          $("#error-message").text("Please fill in all fields.");
          return;
        }

        $.ajax({
          url: "registration/signin.php",
          type: "POST",
          contentType: "application/json",
          data: JSON.stringify({ email: email, password: password }),
          dataType: "json",
          success: function (response) {
            if (response.status === "success") {
              localStorage.removeItem("login_attempts");
              localStorage.removeItem("login_lock_until");
              window.location.href = response.redirect;
            } else {
              let attempts = parseInt(localStorage.getItem("login_attempts")) || 0;
              attempts++;
              localStorage.setItem("login_attempts", attempts);

              $(".js-error-container").show();
              $("#error-message").text(response.message);

              if (attempts >= 3) {
                lockLoginForm();
              }
            }
          },
          error: function (xhr, status, error) {
            $(".js-error-container").show();
            $("#error-message").text("Server error. Please try again.");
          }
        });
      });
    });
  </script>


</body>
</html>
