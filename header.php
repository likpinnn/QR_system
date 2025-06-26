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
<!-- 浏览器图标 -->
<link rel="icon" type="image/x-icon" href="assets/ico/QR.ico">
<link rel="shortcut icon" type="image/x-icon" href="assets/ico/QR.ico">

<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/bootstrap-icons.css">
<link rel="stylesheet" href="assets/fontawesome/css/all.min.css">

<script src="assets/js/bootstrap.bundle.min.js"></script>

<header class="bg-dark">
    <nav class="navbar navbar-expand-lg">
        <div class="container">

            <?php if(isset($_SESSION['userid'])): ?>
            <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <?php endif; ?>

            <h1 class="navbar-brand" style="font-size: 30px;">
                <a href="index.php" class="text-decoration-none text-white">
                    QR Report
                </a>
            </h1>

            <?php if(isset($_SESSION['userid'])): ?>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    
                    <li class="nav-item me-3">
                        <a class="nav-link link-light menu-item" href="index.php">
                            <i class="fa-solid fa-house me-1"></i>Home
                        </a>
                    </li>
                    
                    <!-- Admin -->
                    <?php if($role == 'superAdmin' || $role == 'admin'){?>
                        <li class="nav-item me-3">
                            <a class="nav-link link-light menu-item" href="form.php">
                                <i class="fas fa-file-alt me-1"></i>Form
                            </a>
                        </li>
                        <li class="nav-item me-3">
                            <a class="nav-link link-light menu-item" href="serial.php">
                                <i class="fa-solid fa-list-ol me-1"></i>Serial
                            </a>
                        </li>
                        <li class="nav-item me-3">
                            <a class="nav-link link-light menu-item" href="user.php">
                                <i class="fa-solid fa-users me-1"></i>User
                            </a>
                        </li>
                    <?php }?>
                    
                    <li class="nav-item me-3">
                        <a class="nav-link link-light menu-item" href="pdf.php">
                            <i class="fa-solid fa-file-pdf me-1"></i>PDF
                        </a>
                    </li>

                    <li class="nav-item me-3">
                        <a class="nav-link link-light menu-item" href="setting.php">
                            <i class="fa-solid fa-gear me-1"></i>Setting
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link link-light menu-item logout-item" href="logout.php">
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
/* 自定义汉堡菜单按钮 */
.custom-toggler {
    border: none;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.1);
}

.custom-toggler:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.05);
}

.custom-toggler:focus {
    box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
}

.custom-toggler .navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* 菜单项样式 */
.menu-item {
    position: relative;
    padding: 0.75rem 1rem !important;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.menu-item:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.menu-item::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: #0d6efd;
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.menu-item:hover::before {
    width: 80%;
}

/* 登出按钮特殊样式 */
.logout-item {
    background: rgba(220, 53, 69, 0.1);
    border: 1px solid rgba(220, 53, 69, 0.3);
}

.logout-item:hover {
    background: rgba(220, 53, 69, 0.2) !important;
    border-color: rgba(220, 53, 69, 0.5);
}

.logout-item::before {
    background: #dc3545;
}

/* 响应式设计 */
@media (max-width: 991.98px) {
    .navbar-brand {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        margin: 0 !important;
        z-index: 1030;
    }
    
    .custom-toggler {
        margin-right: 1rem;
        z-index: 1030;
    }
    
    .navbar-collapse {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.95);
        backdrop-filter: blur(10px);
        padding: 2rem 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1020;
        transition: all 0.3s ease;
    }
    
    .navbar-collapse.collapsing {
        transition: all 0.3s ease;
    }
    
    .navbar-nav {
        width: 100%;
        max-width: 300px;
    }
    
    .nav-item {
        margin-bottom: 1rem;
        text-align: center;
    }
    
    .menu-item {
        display: block;
        padding: 1rem 1.5rem !important;
        font-size: 1.1rem;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        margin: 0.5rem 0;
        transition: all 0.3s ease;
    }
    
    .menu-item:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }
    
    .menu-item::before {
        display: none;
    }
    
    .logout-item {
        background: rgba(220, 53, 69, 0.2);
        border-color: rgba(220, 53, 69, 0.4);
        margin-top: 2rem;
    }
    
    .logout-item:hover {
        background: rgba(220, 53, 69, 0.3) !important;
        border-color: rgba(220, 53, 69, 0.6);
    }
    
    /* 菜单图标动画 */
    .menu-item i {
        transition: transform 0.3s ease;
    }
    
    .menu-item:hover i {
        transform: scale(1.2);
    }
    
    /* 关闭按钮 */
    .navbar-collapse::before {
        content: '×';
        position: absolute;
        top: 1rem;
        right: 1rem;
        font-size: 2rem;
        color: rgba(255, 255, 255, 0.7);
        cursor: pointer;
        z-index: 1030;
        transition: all 0.3s ease;
    }
    
    .navbar-collapse::before:hover {
        color: white;
        transform: scale(1.1);
    }
}

/* 动画效果 */
@keyframes slideInFromTop {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.navbar-collapse.show {
    animation: slideInFromTop 0.3s ease;
}

/* 当前页面高亮 */
.nav-link.active {
    background: rgba(13, 110, 253, 0.2) !important;
    border-color: rgba(13, 110, 253, 0.4) !important;
}

.nav-link.active::before {
    width: 80%;
}
</style>

<script>
// 添加当前页面高亮功能
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        }
    });
    
    // 移动端菜单关闭功能
    const navbarCollapse = document.querySelector('.navbar-collapse');
    const menuItems = document.querySelectorAll('.menu-item');
    
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 991.98) {
                const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                bsCollapse.hide();
            }
        });
    });
    
    // 点击背景关闭菜单
    navbarCollapse.addEventListener('click', function(e) {
        if (e.target === this) {
            const bsCollapse = new bootstrap.Collapse(this);
            bsCollapse.hide();
        }
    });
});
</script>

