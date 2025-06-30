<?php
include_once 'db.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM users WHERE `role` != 'superAdmin' AND (username LIKE :search OR name LIKE :search) ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


if($_SERVER['REQUEST_METHOD'] == 'POST'){

    if(isset($_POST['add_user'])){
        $username = $_POST['username'];
        $password = $_POST['password'];
        $name = $_POST['name'];
        $role = $_POST['role'];
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, password, name, role) VALUES ('$username', '$hash', '$name', '$role')";
        $conn->query($sql);

        header('Location: user.php');
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

        header('Location: user.php');
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
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-5">User Management</h1>
                <form method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search by username or name..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary" type="submit">Search</button>
                        <?php if($search): ?>
                            <a href="user.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                <table class="table table-bordered table-striped">
                    <tr class="table-dark">
                        <th>Username</th>
                        <th>Password</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td><?php echo $user['username']; ?></td>
                            <td>
                                <form action="" method="post">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <div class="input-group">
                                        <input class="form-control" type="password" name="reset_password" placeholder="New Password" required>
                                        <input class="btn btn-primary" type="submit" value="Reset">
                                    </div>
                                </form>
                            </td>
                            <td><?php echo $user['name']; ?></td>
                            <td><?php echo $user['role']; ?></td>
                            <td class="text-center fs-5">
                                <a href="#" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo $user['name']; ?>')" class="link-danger link-offset-2 link-underline link-underline-opacity-0">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-dark">
                        <th colspan="5">Add User</th>
                    </tr>
                    <form action="" method="post">
                        <tr>
                            <td>
                                <input class="form-control" type="text" name="username" placeholder="Username" required>
                            </td>
                            <td>
                                <input class="form-control" type="password" name="password" placeholder="Password" required>
                            </td>
                            <td>
                                <input class="form-control" type="text" name="name" placeholder="Full Name" required>
                            </td>
                            <td>
                                <select class="form-control" name="role">
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                </select>
                            </td>
                            <td>
                                <input class="col-12 btn btn-primary" type="submit" value="Add User" name="add_user">
                            </td>
                        </tr>
                    </form>
                </table>
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
                    // 删除成功后刷新页面
                    window.location.reload();
                }
            };
            xhttp.open("GET", "delete_report.php?user=" + id, true);
            xhttp.send();
        }
    }
</script>
</html>