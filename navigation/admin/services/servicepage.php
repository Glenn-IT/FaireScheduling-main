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
            <li class="p-1 navbar-custom my-2"><a href="../account/accountpage.php"><i class="fas fa-users"></i><span> Accounts</span></a></li>
            <li class="p-1 navbar-custom-active mb-2"><a href="../services/servicepage.php"><i class="fas fa-wrench"></i><span> Services</span></a></li>
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
    <!-- Place near top of body or inside #content -->
    <button class="toggle-button d-md-none ms-2" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

        <div class="container-fluid mt-4">
            <div class="welcome-banner">
                <h2>Service Management</h2>
                <p>Manage Services</p>
            </div>

            <!-- DataTable -->
            <div class="card p-3">
                <div class="d-flex">
                        <button type="button" class="adddocument-btn mb-2">
                            <i class='fas fa-add'></i> Add Service
                        </button>

                        <!-- Add Document Category Modal -->
                        <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addCategoryLabel">Add Service</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="addCategoryForm">
                                            <div class="mb-3">
                                                <label for="serviceName" class="form-label">Service Name</label>
                                                <input type="text" class="form-control" id="serviceName" name="serviceName" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control" id="description" name="description" required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Add Service</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                </div>
                <div class="table-responsive">
                    <table id="accountsTable" class="table table-dark">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Service name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel">Edit Council</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editForm">
                                    <input type="hidden" id="editDocumentId">
                                    
                                    <div class="mb-3">
                                        <label for="editserviceName" class="form-label">Service Name</label>
                                        <input type="text" class="form-control" id="editserviceName" name="editserviceName" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="editdescription" class="form-label">Description</label>
                                        <textarea class="form-control" id="editdescription" name="editdescription" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    

    <script>
        let table;

       $(document).ready(function () {
            table = $('#accountsTable').DataTable({
                responsive: true,
                ajax: 'fetch_service.php', // keep or adjust path
                columns: [
                    { data: 'ID' },
                    { data: 'service_name' },
                    { data: 'description' },
                    {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: (row) => {
                        const id   = row.ID;
                        const name = encodeURIComponent(row.service_name || '');
                        const desc = encodeURIComponent(row.description  || '');
                        return `
                        <button class="btn btn-sm btn-warning edit-btn"
                                data-id="${id}"
                                data-name="${name}"
                                data-description="${desc}">
                            <i class="fas fa-edit" style="font-size:14px;"></i> Edit
                        </button>`;
                    }
                    }
                ]
            });
            // Edit Button Click
            $(document).on('click', '.edit-btn', function () {
                const id   = $(this).data('id');
                const name = decodeURIComponent($(this).data('name') || '');
                const desc = decodeURIComponent($(this).data('description') || '');

                $('#editDocumentId').val(id);
                $('#editserviceName').val(name);
                $('#editdescription').val(desc);

                $('#editModal').modal('show');
            });


            // Submit Edit Form
            $('#editForm').on('submit', function (e) {
            e.preventDefault();

            const id    = $('#editDocumentId').val();
            const name  = $('#editserviceName').val().trim();
            const desc  = $('#editdescription').val().trim();

            if (!id || !name || !desc) {
                Swal.fire("Missing fields", "Service Name and Description are required.", "error");
                return;
            }

            $.ajax({
                url: 'edit_service.php',        // <-- fixed filename
                type: 'POST',
                dataType: 'json',
                data: { id, category: name, description: desc }, // keep keys to match PHP below
                success: function (res) {
                if (res.success) {
                    Swal.fire("Success", res.success, "success").then(() => {
                    $('#editModal').modal('hide');
                    // assumes you have a DataTable variable named `table`
                    if (window.table) table.ajax.reload(null, false);
                    else $('#accountsTable,#servicesTable').DataTable().ajax.reload(null, false);
                    });
                } else {
                    Swal.fire("Error", res.error || "Failed to update.", "error");
                }
                },
                error: function (xhr) {
                Swal.fire("Error", xhr.responseText || "Server error.", "error");
                }
            });
            });



        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('show');
        }




        $(document).ready(function() {
            // Show Modal When Button is Clicked
            $(document).on('click', '.adddocument-btn', function() {
                $('#addCategoryModal').modal('show');
            });

            // Handle Form Submission
            $('#addCategoryForm').on('submit', function (e) {
                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: 'add_service.php',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.success,
                                timer: 2000,
                                showConfirmButton: false
                            });

                            $('#addCategoryModal').modal('hide');
                            $('#addCategoryForm')[0].reset();

                            // ✅ Refresh DataTable
                            table.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.error
                            });
                        }
                    }
                });
            });
        });


       document.addEventListener('click', function (e) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.querySelector('.toggle-button');

            if (window.innerWidth <= 768 && sidebar.classList.contains('show')) {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });



    </script>


</body>
</html>
