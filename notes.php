<?php
include 'config.php';
include 'auth.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['logout'])) {
    logout();
}

$alterTable = "ALTER TABLE `notes` 
               ADD COLUMN IF NOT EXISTS `user_id` INT(11) NOT NULL AFTER `sno`,
               ADD KEY IF NOT EXISTS `user_id` (`user_id`)";

mysqli_query($conn, $alterTable);

$insert = false;
$update = false;
$delete = false;

if(isset($_GET['delete'])){
    $sno = $_GET['delete'];
    $user_id = $_SESSION['user_id'];
    $delete = true;
    $sql = "DELETE FROM `notes` WHERE `sno` = $sno AND `user_id` = $user_id";
    $result = mysqli_query($conn, $sql);
}

if($_SERVER['REQUEST_METHOD'] == "POST"){
    if(isset($_POST['snoEdit'])){
        $sno = $_POST['snoEdit'];
        $title = $_POST['edittitle'];
        $description = $_POST['editdesc'];
        $user_id = $_SESSION['user_id'];

        $sql = "UPDATE `notes` SET `title` = '$title', `discription` = '$description' 
                WHERE `sno` = $sno AND `user_id` = $user_id";

        $result = mysqli_query($conn, $sql);
        if(!$result){
            echo "Update was not successful";
        } else {
            $update = true;
        }
    } else {
        $title = $_POST['title'];
        $description = $_POST['desc'];
        $user_id = $_SESSION['user_id'];

        $sql = "INSERT INTO `notes` (`title`, `discription`, `user_id`, `tstamp`) 
                VALUES ('$title', '$description', '$user_id', current_timestamp())";

        $result = mysqli_query($conn, $sql);

        if($result){
            $insert = true;
        } else {
            echo "Record was not inserted successfully";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iNotes - My Notes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #f5f7ff;
            --accent-color: #ff6584;
            --dark-color: #2a2a72;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 60px;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--dark-color) 0%, var(--primary-color) 100%) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .brand-container {
            display: flex;
            align-items: center;
        }
        
        .brand-icon {
            font-size: 1.8rem;
            margin-right: 10px;
        }
        
        .container.form-container {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-top: -20px;
            position: relative;
            z-index: 1;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #554fd8;
            border-color: #554fd8;
        }
        
        .action-btn {
            margin: 0 3px;
            border-radius: 5px;
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .table th {
            background-color: var(--secondary-color);
            color: var(--dark-color);
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--dark-color) 0%, var(--primary-color) 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
            border-radius: 0 0 10px 10px;
        }
        
        .notes-counter {
            background-color: white;
            color: var(--primary-color);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.5rem;
            margin: 0 auto 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 5rem;
            margin-bottom: 20px;
            color: var(--secondary-color);
        }
        
        .note-card {
            transition: transform 0.2s;
        }
        
        .note-card:hover {
            transform: translateY(-5px);
        }
        
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 20px 0;
            margin-top: 40px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logout-btn {
            background: none;
            border: 1px solid #dc3545;
            color: #dc3545;
            padding: 5px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background-color: #dc3545;
            color: white;
        }
        
        @media (max-width: 768px) {
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .table-responsive {
                overflow-x: auto;
            }
            
            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }
            
            .action-btn {
                width: 100%;
                margin: 2px 0;
            }
        }
        
        .modal {
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
        }
        
        .delete-confirm {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.3);
            z-index: 1060;
            display: none;
        }
        
        .delete-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            display: none;
        }
    </style>
</head>
<body>
    <div class="delete-overlay" id="deleteOverlay"></div>
    <div class="delete-confirm" id="deleteConfirm">
        <h5>Confirm Delete</h5>
        <p>Are you sure you want to delete this note?</p>
        <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" id="cancelDelete">Cancel</button>
            <button class="btn btn-danger" id="confirmDelete">Delete</button>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit This Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="notes.php" method="post">
                        <input type="hidden" name="snoEdit" id="snoEdit">
                        <div class="mb-3">
                            <label for="edittitle" class="form-label">Note Title</label>
                            <input type="text" class="form-control" id="edittitle" name="edittitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="editdesc" class="form-label">Note Description</label>
                            <textarea class="form-control" id="editdesc" name="editdesc" rows="4" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update Note</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <div class="brand-container">
                <i class="fas fa-sticky-note brand-icon"></i>
                <a class="navbar-brand fw-bold" href="notes.php">iNotes</a>
            </div>
            
            <div class="user-info text-light">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="?logout=true" class="logout-btn ms-3">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-5 fw-bold">Your Personal Notes</h1>
            <p class="lead">Organize your thoughts and ideas in one place</p>
            <div class="notes-counter">
                <?php
                    $user_id = $_SESSION['user_id'];
                    $sql = "SELECT COUNT(*) as count FROM `notes` WHERE `user_id` = $user_id";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    echo $row['count'];
                ?>
            </div>
            <p>Notes and counting</p>
        </div>
    </div>

    <div class="container mt-4">
        <?php 
            if($insert){
                echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                <i class='fas fa-check-circle me-2'></i><strong>Success!</strong> Your note was added successfully.
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
                $insert = false;
            }
        ?>
        <?php 
            if($update){
                echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                <i class='fas fa-check-circle me-2'></i><strong>Success!</strong> Your note was updated successfully.
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
                $update = false;
            }
        ?>
        <?php 
            if($delete){
                echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                <i class='fas fa-check-circle me-2'></i><strong>Success!</strong> Your note was deleted successfully.
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
                $delete = false;
            }
        ?>
    </div>

    <div class="container form-container">
        <h3 class="mb-4"><i class="fas fa-plus-circle me-2 text-primary"></i>Add New Note</h3>
        <form action="notes.php" method="post">
            <div class="mb-3">
                <label for="title" class="form-label">Note Title</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="Enter note title" required>
            </div>
            <div class="mb-3">
                <label for="desc" class="form-label">Note Description</label>
                <textarea class="form-control" id="desc" name="desc" rows="4" placeholder="Enter your note content" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Note</button>
        </form>
    </div>

    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="fas fa-sticky-note me-2 text-primary"></i>Your Notes</h3>
            <span class="badge bg-primary">
                <?php
                    $user_id = $_SESSION['user_id'];
                    $sql = "SELECT COUNT(*) as count FROM `notes` WHERE `user_id` = $user_id";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    echo $row['count'] . ' notes';
                ?>
            </span>
        </div>
        
        <?php
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT * FROM `notes` WHERE `user_id` = $user_id";
        $result = mysqli_query($conn, $sql);
        
        if(mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover" id="notesTable">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Title</th>
                            <th scope="col">Description</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pos = 1;
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>
                            <th scope='row'>". $pos++ ."</th>
                            <td class='fw-bold'>". htmlspecialchars($row['title']) ."</td>
                            <td>". htmlspecialchars(strlen($row['discription']) > 50 ? substr($row['discription'], 0, 50) . '...' : $row['discription']) ."</td>
                            <td class='action-buttons'>
                                <button class='edit btn btn-sm btn-primary action-btn' data-id='". $row['sno'] ."' data-title='". htmlspecialchars($row['title']) ."' data-desc='". htmlspecialchars($row['discription']) ."' data-bs-toggle='tooltip' title='Edit'>
                                    <i class='fas fa-edit'></i>
                                </button> 
                                <button class='delete btn btn-sm btn-danger action-btn' data-id='". $row['sno'] ."' data-bs-toggle='tooltip' title='Delete'>
                                    <i class='fas fa-trash-alt'></i>
                                </button>
                            </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-sticky-note"></i>
                <h4>No notes yet</h4>
                <p>Add your first note using the form above</p>
            </div>
        <?php endif; ?>
    </div>

    <footer class="text-center">
        <div class="container">
            <p class="mb-0">© <?php echo date('Y'); ?> iNotes App. All rights reserved.</p>
            <p class="mb-0">Made with <i class="fas fa-heart text-danger"></i> for note takers</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#notesTable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search notes..."
                }
            });
            
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            $(document).on('click', '.edit', function() {
                var id = $(this).data('id');
                var title = $(this).data('title');
                var desc = $(this).data('desc');
                
                $('#snoEdit').val(id);
                $('#edittitle').val(title);
                $('#editdesc').val(desc);
                
                $('#editModal').modal('show');
            });
            
            var deleteId = null;
            
            $(document).on('click', '.delete', function() {
                deleteId = $(this).data('id');
                $('#deleteOverlay').fadeIn();
                $('#deleteConfirm').fadeIn();
            });
            
            $('#cancelDelete').click(function() {
                $('#deleteOverlay').fadeOut();
                $('#deleteConfirm').fadeOut();
                deleteId = null;
            });
            
            $('#confirmDelete').click(function() {
                if (deleteId) {
                    window.location = 'notes.php?delete=' + deleteId;
                }
            });
            
            $('#deleteOverlay').click(function() {
                $('#deleteOverlay').fadeOut();
                $('#deleteConfirm').fadeOut();
                deleteId = null;
            });
            
            $('.alert').delay(5000).fadeOut(400);
        });
    </script>
</body>
</html>
