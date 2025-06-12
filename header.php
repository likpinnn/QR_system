<?php
include_once "db.php";

if(!isset($_SESSION['userid'])) {
   echo "<script>window.location.href = 'login.php';</script>";
   exit();
}

$sql = "SELECT * FROM `users` WHERE id = :id";
$qry = $conn->prepare($sql);
$qry->bindParam(':id', $_SESSION['userid'], PDO::PARAM_INT);
$qry->execute();
$result = $qry->fetchAll(PDO::FETCH_ASSOC);

$row = $result[0];
$role = $row['role'];



?>
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/bootstrap-icons.css">
<link rel="stylesheet" href="assets/fontawesome/css/all.min.css">

<script src="assets/js/bootstrap.bundle.min.js"></script>

<header class="bg-body-secondary">
    <nav class="navbar navbar-expand-lg">
        <div class="container">

            <?php if(isset($_SESSION['userid'])): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <?php endif; ?>

            <h1 class="navbar-brand" style="font-size: 30px;">
                <a href="index.php" class="text-decoration-none text-dark">
                    QR Report
                </a>
            </h1>

            <?php if(isset($_SESSION['userid'])): ?>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    
                    <li class="nav-item me-3">
                        <a class="nav-link" href="index.php">
                            <i class="fa-solid fa-house me-1"></i>Home
                        </a>
                    </li>
                    
                    <!-- Admin -->
                    <?php if($role == 'superAdmin' || $role == 'admin'){?>
                        <li class="nav-item me-3">
                            <a class="nav-link" href="form.php">
                                <i class="fas fa-file-alt me-1"></i>Form
                            </a>
                        </li>
                        <li class="nav-item me-3">
                            <a class="nav-link" href="serial.php">
                                <i class="fa-solid fa-list-ol me-1"></i>Serial
                            </a>
                        </li>
                        <li class="nav-item me-3">
                            <a class="nav-link" href="user.php">
                                <i class="fa-solid fa-users me-1"></i>User
                            </a>
                        </li>
                    <?php }?>
                    
                    <li class="nav-item me-3">
                        <a class="nav-link" href="pdf.php">
                            <i class="fa-solid fa-file-pdf me-1"></i>PDF
                        </a>
                    </li>

                    <li class="nav-item me-3">
                        <a class="nav-link" href="setting.php">
                            <i class="fa-solid fa-gear me-1"></i>Setting
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fa-solid fa-right-from-bracket me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </nav>
</header>

<style>
@media (max-width: 991.98px) {
    .navbar-brand {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        margin: 0 !important;
    }
    .navbar-toggler {
        margin-right: 1rem;
        z-index: 1;
    }
    .navbar-collapse {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--bs-body-bg);
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
}
</style>

