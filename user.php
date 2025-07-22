<?php
include_once 'db.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10; // Display 1 record per page
$offset = ($page - 1) * $per_page;

// Get total record count
$count_sql = "SELECT COUNT(*) as total FROM users WHERE `role` != 'superAdmin' AND (username LIKE :search OR name LIKE :search)";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute(['search' => "%$search%"]);
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $per_page);

// Get current page user data
$sql = "SELECT * FROM users WHERE `role` != 'superAdmin' AND (username LIKE :search OR name LIKE :search) ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


if($_SERVER['REQUEST_METHOD'] == 'POST'){

    if(isset($_POST['add_user'])){
        $username = $_POST['username'];
        $password = $_POST['password'];
        $name = $_POST['name'];
        $role = $_POST['role'];
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $check_qry = "SELECT `username` FROM `users` WHERE `username` = :username";
        $check_stmt = $conn->prepare($check_qry);
        $check_stmt->execute(['username' => "$username"]);
        $check_result = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
        $count_result = count($check_result);

        if($count_result > 0){
            $_SESSION['error'] = "<b>'".$username."'</b> User Already Exists!";
        }else{
            $sql = "INSERT INTO users (username, password, name, role) VALUES ('$username', '$hash', '$name', '$role')";
            $conn->query($sql);
        }

        // Maintain current search and pagination state
        $redirect_url = "user.php";
        if ($search) {
            $redirect_url .= "?search=" . urlencode($search);
            if ($page > 1) {
                $redirect_url .= "&page=" . $page;
            }
        } elseif ($page > 1) {
            $redirect_url .= "?page=" . $page;
        }
        
        header('Location: ' . $redirect_url);
        exit();
    }

    if(isset($_POST['reset_password'])){
        $id = $_POST['id'];

        $name_sql = "SELECT name FROM users WHERE id = '$id'";
        $name_qry = $conn->query($name_sql);
        $name_result = $name_qry->fetch(PDO::FETCH_ASSOC);
        $name = $name_result['name'];
        
        $password = $_POST['reset_password'];
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = '$hash' WHERE id = '$id'";
        $conn->query($sql);

        $_SESSION['success'] = $name.'\'s password reset successfully';

        // Maintain current search and pagination state
        $redirect_url = "user.php";
        if ($search) {
            $redirect_url .= "?search=" . urlencode($search);
            if ($page > 1) {
                $redirect_url .= "&page=" . $page;
            }
        } elseif ($page > 1) {
            $redirect_url .= "?page=" . $page;
        }
        
        header('Location: ' . $redirect_url);
        exit();
    }

    if(isset($_POST['update_role'])){
        $id = $_POST['id'];
        $new_role = $_POST['new_role'];

        $name_sql = "SELECT name FROM users WHERE id = '$id'";
        $name_qry = $conn->query($name_sql);
        $name_result = $name_qry->fetch(PDO::FETCH_ASSOC);
        $name = $name_result['name'];
        
        $sql = "UPDATE users SET role = '$new_role' WHERE id = '$id'";
        $conn->query($sql);

        $_SESSION['success'] = $name.'\'s role updated successfully';

        // Maintain current search and pagination state
        $redirect_url = "user.php";
        if ($search) {
            $redirect_url .= "?search=" . urlencode($search);
            if ($page > 1) {
                $redirect_url .= "&page=" . $page;
            }
        } elseif ($page > 1) {
            $redirect_url .= "?page=" . $page;
        }
        
        header('Location: ' . $redirect_url);
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Report System</title>
</head>
<?php 
    include_once 'header.php'; 
    include_once 'loading.php';
?>
<body>
    <div class="container">
        <div class="row mb-5">
            <div class="col-md-12">
                <h1 class="mb-4 mt-2"><i class="fa-solid fa-users me-3"></i>User Management</h1>
                <?php if(isset($_SESSION['success'])){ ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success']); 
                }elseif(isset($_SESSION['error'])){ ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php 
                    unset($_SESSION['error']);
                } ?>
                <div class="card p-3 shadow mb-5">
                    <form action="" method="post" class="row">
                        <div class="col-md-12">
                            <h3><i class="fa-solid fa-user-plus me-2"></i>Add User</h3>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="username">Username:</label>
                                <input class="form-control" type="text" id="username" name="username" placeholder="Username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="pwd">Password:</label>
                                <input class="form-control" type="password" id="pwd" name="password" placeholder="Password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="name">Name:</label>
                                <input class="form-control" type="text" id="name" name="name" placeholder="Name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="role">Role:</label>
                                <select class="form-control" name="role">
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <input class="col-12 btn btn-primary mt-2" type="submit" value="Add User" name="add_user">
                        </div>
                    </form>
                </div>
                <form method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search by username or name..." value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="page" value="1">
                        <button class="btn btn-primary" type="submit">Search</button>
                        <?php if($search): ?>
                            <a href="user.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
                <div class="card p-2 shadow">
                    <table class="table">
                        <tr>
                            <th style="width: 20%;"><i class="fa-solid fa-user"></i> Username</th>
                            <th style="width: 20%;"><i class="fa-solid fa-address-card"></i> Name</th>
                            <th style="width: 20%;"><i class="fa-solid fa-user-tag"></i> Role</th>
                            <th style="width: 20%;"><i class="fa-solid fa-key"></i> Password</th>
                            <th style="width: 20%;" class="text-center"><i class="fa-solid fa-gears"></i> Action</th>
                        </tr>
                        <?php foreach ($users as $user) : ?>
                            <tr>
                                <th><?php echo $user['username']; ?></th>
                                <td><?php echo $user['name']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php 
                                        $roleClass = $user['role'] === 'admin' ? 'badge bg-danger' : 'badge bg-success';
                                        $roleIcon = $user['role'] === 'admin' ? 'fa-user-shield' : 'fa-user';
                                        ?>
                                        <span class="<?php echo $roleClass; ?> me-2" style="font-size: 13px;">
                                            <i class="fa-solid <?php echo $roleIcon; ?> me-1"></i>
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editRole(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>', '<?php echo $user['name']; ?>')">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="p-0">
                                    <form action="" method="post">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <div class="input-group pt-1">
                                            <input class="form-control py-0" type="password" name="reset_password" placeholder="New Password" required>
                                            <button type="submit" class="btn btn-primary" type="submit"><i class="fa-solid fa-key"></i></button>
                                        </div>
                                    </form>
                                </td>
                                <td class="text-center fs-5">
                                    <a href="#" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo $user['name']; ?>')" class="link-danger link-offset-2 link-underline link-underline-opacity-0">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>   
                </div>
                
                
                <!-- Pagination Information -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing <?php echo $offset + 1; ?> - <?php echo min($offset + $per_page, $total_records); ?> of <?php echo $total_records; ?> records
                    </div>
                    
                    <!-- Pagination Navigation -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="User list pagination">
                            <ul class="pagination mb-0">
                                <!-- Previous Page -->
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                            <
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link"><</span>
                                    </li>
                                <?php endif; ?>
                                
                                <!-- Page Numbers -->
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if ($start_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">1</a>
                                    </li>
                                    <?php if ($start_page > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $total_pages; ?></a>
                                    </li>
                                <?php endif; ?>
                                
                                <!-- Next Page -->
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                            >
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">></span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    function deleteUser(id, name) {
        if(confirm('Are you sure you want to delete ' + name + '?')) {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    try {
                        var response = JSON.parse(this.responseText);
                        if (response.success) {
                            // 删除成功，刷新页面
                            window.location.reload();
                        } else {
                            alert('删除失败: ' + response.message);
                        }
                    } catch (e) {
                        // 如果不是 JSON 响应，直接刷新页面
                        window.location.reload();
                    }
                }
            };
            xhttp.open("GET", "delete_report.php?user=" + id, true);
            xhttp.send();
        }
    }

    function editRole(id, currentRole, name) {
        var newRole = prompt('Change role for ' + name + ' (current: ' + currentRole + ')\nEnter "admin" or "user":', currentRole);
        if (newRole && (newRole === 'admin' || newRole === 'user')) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            var idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            
            var roleInput = document.createElement('input');
            roleInput.type = 'hidden';
            roleInput.name = 'new_role';
            roleInput.value = newRole;
            
            var submitInput = document.createElement('input');
            submitInput.type = 'hidden';
            submitInput.name = 'update_role';
            submitInput.value = '1';
            
            form.appendChild(idInput);
            form.appendChild(roleInput);
            form.appendChild(submitInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
</html>