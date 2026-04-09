<?php
// Example: registrationpage.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap + Font Awesome + SweetAlert2 -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="register-card">
    <h3>Register</h3>
    <form onsubmit="return false;">

      <div class="form-row">
        <div class="col-md-4">
          <label for="lastname">Last Name</label>
          <input type="text" class="form-control" id="lastname" required>
        </div>
        <div class="col-md-4">
          <label for="firstname">First Name</label>
          <input type="text" class="form-control" id="firstname" required>
        </div>
        <div class="col-md-4">
          <label for="middlename">Middle Name</label>
          <input type="text" class="form-control" id="middlename">
        </div>
      </div>

      <div class="form-row mt-3">
        <div class="col-md-6">
          <label for="birthday">Birthday</label>
          <input type="date" class="form-control" id="birthday" required>
        </div>
        <div class="col-md-6">
          <label for="age">Age</label>
          <input type="number" class="form-control" id="age" readonly>
        </div>
      </div>

      <div class="form-group">
        <label for="mobile">Mobile Number</label>
        <input type="tel" class="form-control" id="mobile" required pattern="[0-9]{11}" maxlength="11">
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" required>
      </div>

      <!-- Password Field -->
      <div class="form-group position-relative">
        <label for="password">Password</label>
        <input type="password" class="form-control" id="password" required>
        <span toggle="#password" class="fa fa-fw fa-eye field-icon toggle-password pt-1"
              style="position:absolute; top:38px; right:15px; cursor:pointer;"></span>
      </div>

      <!-- Confirm Password Field -->
      <div class="form-group position-relative">
        <label for="confirmpassword">Confirm Password</label>
        <input type="password" class="form-control" id="confirmpassword" required>
        <span toggle="#confirmpassword" class="fa fa-fw fa-eye field-icon toggle-password pt-1"
              style="position:absolute; top:38px; right:15px; cursor:pointer;"></span>
      </div>

      <button type="button" class="btn btn-danger btn-block" id="submitBtn">Register</button>

      <div class="login-link">
        Already have an account? <a href="../index.php">Login here</a>
      </div>
    </form>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script>
    // Auto-calculate age
    document.getElementById('birthday').addEventListener('change', function () {
      const birthDate = new Date(this.value);
      const today = new Date();
      let age = today.getFullYear() - birthDate.getFullYear();
      const m = today.getMonth() - birthDate.getMonth();
      if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }
      document.getElementById('age').value = age;
    });

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

    // Submit with SweetAlert2 and AJAX
    document.getElementById("submitBtn").addEventListener("click", function () {
      const password = $("#password").val();
      const confirmpassword = $("#confirmpassword").val();

      if (password !== confirmpassword) {
        Swal.fire({
          icon: 'warning',
          title: 'Password Mismatch',
          text: 'Passwords do not match.',
          confirmButtonColor: '#CE1126'
        });
        return;
      }

      const data = {
        council: $("#council").val(),
        lastname: $("#lastname").val(),
        firstname: $("#firstname").val(),
        middlename: $("#middlename").val(),
        birthday: $("#birthday").val(),
        age: $("#age").val(),
        company: $("#company").val(),
        companyposition: $("#companyposition").val(),
        mobile: $("#mobile").val(),
        email: $("#email").val(),
        representation: $("#representation").val(),
        councilposition: $("#councilposition").val(),
        assumption: $("#assumption").val(),
        password: password
      };

      $.ajax({
        url: "register.php",
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json",
        success: function (response) {
          if (response.status === "success") {
            Swal.fire({
              icon: 'success',
              title: 'Registration Successful!',
              text: 'You may now log in to your account.',
              confirmButtonColor: '#182242'
            }).then(() => {
              window.location.href = "../index.php";
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Registration Failed',
              text: response.message,
              confirmButtonColor: '#CE1126'
            });
          }
        },
        error: function () {
          Swal.fire({
            icon: 'error',
            title: 'Server Error',
            text: 'Could not connect to server. Please try again later.',
            confirmButtonColor: '#CE1126'
          });
        }
      });
    });
  </script>
</body>
</html>
