<?php
include_once 'db.php';


if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['userid'];

    $hash = password_hash($new_password, PASSWORD_DEFAULT);

    if($new_password == $confirm_password){
        $sql = "UPDATE users SET password = '$hash' WHERE id = '$user_id'";
        $conn->query($sql);
        $_SESSION['success'] = "Password reset successfully";

    }else{
        $_SESSION['error'] = "Password not match";
    }

    header('Location: setting.php');
    exit();
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
                <h1 class="mb-4 mt-2"><i class="fa-solid fa-gear me-2"></i>Setting</h1>
                <?php if(isset($_SESSION['success'])){?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php 
                    unset($_SESSION['success']);
                }else if(isset($_SESSION['error'])){ ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php 
                    unset($_SESSION['error']);
                } ?>
                
                <div class="card shadow">
                    <div class="card-body">
                        <h4><i class="fa-solid fa-key me-2"></i>Reset Password</h4>
                        <form action="" method="post">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="form-group">
                                        <label for="new_password">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password">
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password">
                                    </div>
                                </div>
                                <div class="col-md-12  text-end">
                                    <button type="submit" class="btn btn-primary">Reset</button>
                                </div>
                            </div>
                           
                        </form>
                    </div>
                </div>              
            </div>
        </div>
    </div>
</body>
</html>