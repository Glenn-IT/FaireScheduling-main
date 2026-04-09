<?php 
  session_start();
  if (!isset($_SESSION['userid'])) {
      header("Location: ../../../logout.php");
      exit();
  }

  $userid = $_SESSION['userid'];
  $lastname = $_SESSION['lastname'];
  $firstname = $_SESSION['firstname'];
  $middlename = $_SESSION['middlename'];
  
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- Bootstrap & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css?<?= filemtime('../css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Responsive DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar">
      <div class="sidebar-header">
        <h4>Faire Scheduling</h4>
      </div>
        <ul class="px-3">
            <li class="p-1 navbar-custom mb-2"><a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i><span> Dashboard</span></a></li>
            <li class="p-1 navbar-custom-active mb-2"><a href="../account/accountpage.php"><i class="fas fa-users"></i><span> Accounts</span></a></li>
            <li class="p-1 navbar-custom mb-2"><a href="../services/servicepage.php"><i class="fas fa-wrench"></i><span> Services</span></a></li>
            <li class="p-1 navbar-custom my-2">
              <a
                class="dropdown-toggle d-flex align-items-center"
                data-bs-toggle="collapse"
                href="#scheduleSubmenu"
                role="button"
                aria-expanded="false"
                aria-controls="scheduleSubmenu"
              >
                <i class="fas fa-calendar me-2"></i><span class="me-3">Schedules</span>
              </a>
              <ul class="collapse list-unstyled ps-3 mt-1" id="scheduleSubmenu">
                <li class="my-1 custom-hover-dropdown">
                  <a href="../schedule/pending_page.php" class="custom-hover-dropdown-text"><i class="fas fa-hourglass-half me-2"></i>Pending</a>
                </li>
                <li class="my-1 custom-hover-dropdown">
                  <a href="../schedule/approved_page.php" class="custom-hover-dropdown-text"><i class="fas fa-check-circle me-2"></i>Approved</a>
                </li>
                <li class="my-1 custom-hover-dropdown">
                  <a href="../schedule/completed_page.php" class="custom-hover-dropdown-text"><i class="fas fa-clipboard-check me-2"></i>Completed</a>
                </li>
              </ul>
            </li>
            <hr>
            <li class="p-1 navbar-custom my-2"><a href="../../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div id="content">

        <div class="container-fluid mt-4">
            <div class="welcome-banner">
                <h2>Account Management</h2>
                <p>Manage Account</p>
            </div>

            <!-- DataTable -->
            <div class="card p-3">
                <div class="table-responsive">
                    <table id="accountsTable" class="table table-dark">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Mobile Number</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Active Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                <div class="mb-3">
                    <p>USER ID: <strong id="fileIdDisplay"></strong></p>
                    <input type="hidden" id="fileIdHidden">
                </div>

                <!-- Firstname -->
                <label class="form-label">First Name</label>
                <input type="text" class="form-control" id="firstnameEdit" name="firstnameEdit" required>

                <!-- Lastname -->
                <label class="form-label mt-3">Last Name</label>
                <input type="text" class="form-control" id="lastnameEdit" name="lastnameEdit" required>

                <!-- Middlename -->
                <label class="form-label mt-3">Middle Name</label>
                <input type="text" class="form-control" id="middlenameEdit" name="middlenameEdit">

                <!-- Mobile Number -->
                <label class="form-label mt-3">Mobile Number</label>
                <input type="text" class="form-control" id="mobileEdit" name="mobileEdit">

                <!-- Email -->
                <label class="form-label mt-3">Email</label>
                <input type="email" class="form-control" id="emailEdit" name="emailEdit" required>

                <!-- Role -->
                <label class="form-label mt-3">Role</label>
                <select id="editUserRole" class="form-select" required>
                    <option value="" selected hidden disabled>--Select Role--</option>
                    <option value="Admin">Admin</option>
                    <option value="User">User</option>
                </select>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
                </form>
            </div>
            </div>
        </div>
        </div>



    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    $(document).ready(function () {
    $('#accountsTable').DataTable({
        ajax: {
        url: 'fetch_accounts.php',          // <-- make sure this path is correct relative to accountpage.php
        dataSrc: 'data',                    // <-- DataTables will read rows from response.data
        error: function (xhr) {             // <-- helps you see what came back
            alert('Server returned non-JSON:\n' + xhr.responseText.slice(0, 500));
            console.error('AJAX error:', xhr.responseText);
        }
        },
        processing: true,
        responsive: true,
        columns: [
        { data: 'id', title: 'ID' },
        { data: 'fullname', title: 'Full Name' },
        { data: 'mobilenumber', title: 'Mobile' },
        { data: 'email', title: 'Email' },
        { data: 'user_role', title: 'Role' },
        { data: 'user_active', title: 'Status',
            render: (val) => Number(val) === 1 ? 'Active' : 'Not Active'
        },
        { data: null, orderable: false, searchable: false, title: 'Actions',
            render: (row) => {
            const isActive = Number(row.user_active) === 1;
            if (!isActive) {
                return `
                <button class="btn btn-sm btn-success activate-btn" data-id="${row.id}">
                    <i class="fas fa-check" style="font-size: 16px;"></i> Activate
                </button>
                <button class="btn btn-sm btn-warning reset-btn" data-id="${row.id}">
                    <i class="fas fa-undo" style="font-size: 16px;"></i> Reset
                </button>
                <button class="btn btn-sm btn-primary edit-btn" data-id="${row.id}">
                    <i class="fas fa-edit" style="font-size: 16px;"></i> Edit
                </button>`;
            }
            return `
                <button class="btn btn-sm btn-danger deactivate-btn" data-id="${row.id}">
                <i class="fas fa-ban" style="font-size: 16px;"></i> Deactivate
                </button>
                <button class="btn btn-sm btn-warning reset-btn" data-id="${row.id}">
                <i class="fas fa-undo" style="font-size: 16px;"></i> Reset
                </button>
                <button class="btn btn-sm btn-primary edit-btn" data-id="${row.id}">
                <i class="fas fa-edit" style="font-size: 16px;"></i> Edit
                </button>`;
            }
        }
        ],
        order: [[0, 'asc']]
    });
    });



    // Open edit modal
    $(document).on("click", ".edit-btn", async function () {
    const userid = $(this).data("id");
    try {
        const res = await fetch("fetch_edit.php?userid=" + encodeURIComponent(userid));
        const data = await res.json();
        if (data.status !== 'success') {
        Swal.fire({ icon:'error', title:'Error', text: data.error || 'Failed to load user.' });
        return;
        }
        const u = data.user;

        // show ID
        $("#fileIdDisplay").text(userid);
        $("#fileIdHidden").val(userid);
        $("#editForm").attr("data-id", userid);

        // fill fields (match your modal inputs)
        $("#firstnameEdit").val(u.firstname || '');
        $("#lastnameEdit").val(u.lastname || '');
        $("#middlenameEdit").val(u.middlename || '');
        $("#mobileEdit").val(u.mobilenumber || '');
        $("#emailEdit").val(u.email || '');
        $("#editUserRole").val(u.user_role || '');

        // show modal
        const modal = new bootstrap.Modal(document.getElementById("editModal"));
        modal.show();
    } catch (e) {
        console.error(e);
        Swal.fire({ icon:'error', title:'Network or server error' });
    }
    });

    // Submit update
    document.getElementById("editForm").addEventListener("submit", async function (event) {
    event.preventDefault();

    const userid       = this.getAttribute("data-id") || $("#fileIdHidden").val();
    const firstname    = $("#firstnameEdit").val().trim();
    const lastname     = $("#lastnameEdit").val().trim();
    const middlename   = $("#middlenameEdit").val().trim();   // optional
    const mobilenumber = $("#mobileEdit").val().trim();       // optional
    const email        = $("#emailEdit").val().trim();
    const userrole     = $("#editUserRole").val();

    if (!userid || !firstname || !lastname || !email || !userrole) {
        Swal.fire({ icon:"error", title:"Missing fields", text:"Firstname, Lastname, Email, and Role are required." });
        return;
    }

    const fd = new URLSearchParams();
    fd.append("userid", userid);
    fd.append("firstname", firstname);
    fd.append("lastname", lastname);
    fd.append("middlename", middlename);
    fd.append("mobilenumber", mobilenumber);
    fd.append("email", email);
    fd.append("user_role", userrole);

    try {
        const res  = await fetch("update_accounts.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: fd.toString()
        });
        const data = await res.json();

        if (data.success) {
        Swal.fire({ icon:"success", title:"Updated", timer:1300, showConfirmButton:false })
            .then(() => {
            $('#accountsTable').DataTable().ajax.reload(null, false);
            const modal = bootstrap.Modal.getInstance(document.getElementById("editModal"));
            if (modal) modal.hide();
            });
        } else {
        Swal.fire({ icon:"error", title:"Update failed", text: data.error || "Please try again." });
        }
    } catch (e) {
        console.error(e);
        Swal.fire({ icon:"error", title:"Network or server error" });
    }
    });

    // Reset modal when closed
    document.getElementById("editModal").addEventListener("hidden.bs.modal", () => {
        $("#fileIdHidden").val('');
        $("#editForm").removeAttr("data-id");
        $("#firstnameEdit, #lastnameEdit, #middlenameEdit, #mobileEdit, #emailEdit").val('');
        $("#editUserRole").val('');
    });



    function toggleSidebar() {
        let sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
    }


    // Deactivate account with SweetAlert2
    $('#accountsTable').on('click', '.deactivate-btn', function (e) {
        e.preventDefault();
        const userId = $(this).data('id');
        console.log('deactivate-click', userId);

        Swal.fire({
            title: "Are you sure?",
            text: "This will deactivate the user's account!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, deactivate!"
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post('deactivate_account.php', { id: userId }, function (response) {
            if (response.success) Swal.fire("Success", response.success, "success");
            else Swal.fire("Error", response.error || "Failed to deactivate user.", "error");
            $('#accountsTable').DataTable().ajax.reload(null, false);
            }, 'json')
            .fail(xhr => Swal.fire("Error", xhr.responseText || "Server error.", "error"));
        });
    });


    // Reset account with SweetAlert2
    $('#accountsTable').on('click', '.reset-btn', function (e) {
        e.preventDefault();
        const userId = $(this).data('id');
        console.log('reset-click', userId);

        Swal.fire({
            title: "Are you sure?",
            text: "This will reset the user's password!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, reset!"
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post('reset_account.php', { id: userId }, function (response) {
            if (response.success) Swal.fire("Success", response.success, "success");
            else Swal.fire("Error", response.error || "Failed to reset password.", "error");
            $('#accountsTable').DataTable().ajax.reload(null, false);
            }, 'json')
            .fail(xhr => Swal.fire("Error", xhr.responseText || "Server error.", "error"));
        });
    });


    // activate


    // Activate account with SweetAlert2
    $('#accountsTable').on('click', '.activate-btn', function (e) {
        e.preventDefault();
        const userId = $(this).data('id');
        console.log('activate-click', userId);

        Swal.fire({
            title: "Are you sure?",
            text: "This will activate the user's account.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, Activate!"
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post('activate_account.php', { id: userId }, function (response) {
            if (response.success) Swal.fire("Success", response.success, "success");
            else Swal.fire("Error", response.error || "Failed to activate user.", "error");
            $('#accountsTable').DataTable().ajax.reload(null, false);
            }, 'json')
            .fail(xhr => Swal.fire("Error", xhr.responseText || "Server error.", "error"));
        });
    });


</script>

</body>
</html>
