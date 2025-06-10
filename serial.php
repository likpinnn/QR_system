<?php
include_once 'db.php';

// 获取当前页码
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rev_page = isset($_GET['rev_page']) ? (int)$_GET['rev_page'] : 1;
$records_per_page = 5;
$offset = ($page - 1) * $records_per_page;
$rev_offset = ($rev_page - 1) * $records_per_page;

// 获取总记录数
$bai_count_sql = "SELECT COUNT(*) as total FROM `bai_no`";
$bai_count_result = $conn->query($bai_count_sql);
$bai_total_records = $bai_count_result->fetch(PDO::FETCH_ASSOC)['total'];
$bai_total_pages = ceil($bai_total_records / $records_per_page);

$rev_count_sql = "SELECT COUNT(*) as total FROM `rev`";
$rev_count_result = $conn->query($rev_count_sql);
$rev_total_records = $rev_count_result->fetch(PDO::FETCH_ASSOC)['total'];
$rev_total_pages = ceil($rev_total_records / $records_per_page);

// 获取分页数据
$bai_sql = "SELECT * FROM `bai_no` ORDER BY `bai_no` ASC LIMIT :offset, :records_per_page";
$bai_stmt = $conn->prepare($bai_sql);
$bai_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$bai_stmt->bindValue(':records_per_page', $records_per_page, PDO::PARAM_INT);
$bai_stmt->execute();
$bai_data = $bai_stmt->fetchAll(PDO::FETCH_ASSOC);

$rev_sql = "SELECT * FROM `rev` ORDER BY `rev` ASC LIMIT :offset, :records_per_page";
$rev_stmt = $conn->prepare($rev_sql);
$rev_stmt->bindValue(':offset', $rev_offset, PDO::PARAM_INT);
$rev_stmt->bindValue(':records_per_page', $records_per_page, PDO::PARAM_INT);
$rev_stmt->execute();
$rev_data = $rev_stmt->fetchAll(PDO::FETCH_ASSOC);

if(isset($_SESSION['serial_display'])) {
    $serial_display = $_SESSION['serial_display'];
    $serial_update_time = $_SESSION['serial_update_time'];
    unset($_SESSION['serial_display']);
    unset($_SESSION['serial_update_time']);
} else {
    $serial_display = 'none';
    $serial_update_time = '';
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['serial_display'] = 'block';
    $_SESSION['serial_update_time'] = date('Y-m-d H:i:s');

    $bai_no = $_POST['bai_no'];
    $rev = $_POST['rev'];
    $bai_no = array_filter($bai_no);
    $rev = array_filter($rev);
    foreach($bai_no as $key => $value) {
        $sql = "INSERT INTO `bai_no` (`bai_no`) VALUES (:bai_no)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':bai_no' => $bai_no[$key]
        ]);
    }

    foreach($rev as $key => $value) {
        $sql = "INSERT INTO `rev` (`rev`) VALUES (:rev)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':rev' => $rev[$key]
        ]);
    }

    header('Location: serial.php');
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
<style>
    .step-title {
        margin-top: 2rem;
        color: #0d6efd;
    }
    /* 分页样式 */
    .pagination {
        --bs-pagination-padding-x: 0.5rem;
        --bs-pagination-padding-y: 0.25rem;
        --bs-pagination-font-size: 0.7rem;
    }
    .pagination .page-link {
        padding: var(--bs-pagination-padding-y) var(--bs-pagination-padding-x);
        font-size: var(--bs-pagination-font-size);
    }
    .pagination-info {
        font-size: 0.7rem;
        color: #6c757d;
    }
</style>
<?php include_once 'header.php'; ?>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Technical Information</h1>
                <div class="alert alert-success" role="alert" id="success-alert" style="display: <?php echo $serial_display; ?>">
                    <h4 class="alert-heading">Success</h4>
                    <p>The form has been updated successfully.</p>
                    <hr>
                    <p class="mb-0">Last updated: <?php echo $serial_update_time; ?></p>
                </div>

                <form action="" method="post">
                    <div class="row mt-5">
                        <div class="col-md-6">
                            <table class="table table-bordered table-striped">
                                <tr class="table-dark">
                                    <th class="w-100">Bai No</th>
                                    <th></th>
                                </tr>
                                <?php foreach ($bai_data as $bai) { ?>
                                <tr>
                                    <td>
                                        <?php echo $bai['bai_no']; ?>
                                    </td>
                                    <td>
                                        <button class="btn text-danger fs-5" onclick="del_bai(<?php echo $bai['id']; ?>)">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php } ?>
                                <tr>
                                    <td>
                                        <input type="text" name="bai_no[]" class="form-control" placeholder="Bai No">
                                    </td>
                                    <td>
                                        <button class="btn text-success fs-5" type="button" id="add-bai" title="Add Row">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr id="new_bai" style="display: none;">
                                    <td>
                                        <input type="text" name="bai_no[]" class="form-control" placeholder="Bai No">
                                    </td>
                                    <td class="text-center fs-5">
                                        <a href="javascript:void(0)" class="link-danger link-underline link-underline-opacity-0 delete-row">
                                            <i class="fa-solid fa-minus mt-2"></i>
                                        </a>
                                    </td>
                                </tr>
                            </table>    
                            <!-- Bai No 分页 -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="pagination-info">
                                    Showing <?php echo min(($page-1)*$records_per_page + 1, $bai_total_records); ?> to 
                                    <?php echo min($page*$records_per_page, $bai_total_records); ?> of 
                                    <?php echo $bai_total_records; ?> entries
                                </div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination mb-0">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page-1; ?>&rev_page=<?php echo $rev_page; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <?php
                                        $start_page = max(1, $page - 1);
                                        $end_page = min($bai_total_pages, $page + 1);
                                        
                                        if($start_page > 1) {
                                            echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                            if($start_page > 2) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                        }
                                        
                                        for($i = $start_page; $i <= $end_page; $i++): ?>
                                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&rev_page=<?php echo $rev_page; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor;
                                        
                                        if($end_page < $bai_total_pages) {
                                            if($end_page < $bai_total_pages - 1) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                            echo '<li class="page-item"><a class="page-link" href="?page='.$bai_total_pages.'">'.$bai_total_pages.'</a></li>';
                                        }
                                        ?>
                                        <li class="page-item <?php echo $page >= $bai_total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?>&rev_page=<?php echo $rev_page; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered table-striped">
                                <tr class="table-dark">
                                    <th class="w-100">Rev</th>
                                    <th></th>
                                </tr>
                                <?php foreach ($rev_data as $rev) { ?>
                                <tr>
                                    <td>
                                        <?php echo $rev['rev']; ?>
                                    </td>
                                    <td>
                                        <button class="btn text-danger fs-5" onclick="del_rev(<?php echo $rev['id']; ?>)">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php } ?>
                                <tr>
                                    <td>
                                        <input type="text" name="rev[]" class="form-control" placeholder="Rev">
                                    </td>
                                    <td>
                                        <button class="btn text-success fs-5" type="button" id="add-rev" title="Add Row">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr id="new_rev" style="display: none;">
                                    <td>
                                        <input type="text" name="rev[]" class="form-control" placeholder="Rev">
                                    </td>
                                    <td class="text-center fs-5">
                                        <a href="javascript:void(0)" class="link-danger link-underline link-underline-opacity-0 delete-row">
                                            <i class="fa-solid fa-minus mt-2"></i>
                                        </a>
                                    </td>
                                </tr>
                            </table>    
                            <!-- Rev 分页 -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="pagination-info">
                                    Showing <?php echo min(($rev_page-1)*$records_per_page + 1, $rev_total_records); ?> to 
                                    <?php echo min($rev_page*$records_per_page, $rev_total_records); ?> of 
                                    <?php echo $rev_total_records; ?> entries
                                </div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination mb-0">
                                        <li class="page-item <?php echo $rev_page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?rev_page=<?php echo $rev_page-1; ?>&page=<?php echo $page; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <?php
                                        $start_page = max(1, $rev_page - 1);
                                        $end_page = min($rev_total_pages, $rev_page + 1);
                                        
                                        if($start_page > 1) {
                                            echo '<li class="page-item"><a class="page-link" href="?rev_page=1">1</a></li>';
                                            if($start_page > 2) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                        }
                                        
                                        for($i = $start_page; $i <= $end_page; $i++): ?>
                                            <li class="page-item <?php echo $rev_page == $i ? 'active' : ''; ?>">
                                                <a class="page-link" href="?rev_page=<?php echo $i; ?>&page=<?php echo $page; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor;
                                        
                                        if($end_page < $rev_total_pages) {
                                            if($end_page < $rev_total_pages - 1) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                            echo '<li class="page-item"><a class="page-link" href="?rev_page='.$rev_total_pages.'">'.$rev_total_pages.'</a></li>';
                                        }
                                        ?>
                                        <li class="page-item <?php echo $rev_page >= $rev_total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?rev_page=<?php echo $rev_page+1; ?>&page=<?php echo $page; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-end">
                            <button class="col-12 btn btn-success mt-3" type="submit" title="Save">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<script>
    function del_bai(id) {
        if(confirm('Are you sure you want to delete this data?')) {
            var xhttp = new XMLHttpRequest();
            xhttp.open("GET", "delete_report.php?bai_id=" + id, true);
            xhttp.onreadystatechange = function() {
                if(this.readyState == 4 && this.status == 200) {
                    window.location.reload();
                }
            };
            xhttp.send();
        }
    }

    function del_rev(id) {
        if(confirm('Are you sure you want to delete this data?')) {
            var xhttp = new XMLHttpRequest();
            xhttp.open("GET", "delete_report.php?bairev_id=" + id, true);
            xhttp.onreadystatechange = function() {
                if(this.readyState == 4 && this.status == 200) {
                    window.location.reload();
                }
            };
            xhttp.send();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // 修订历史表格的添加按钮
        const addButtonRev = document.getElementById('add-rev');
        const addButtonBai = document.getElementById('add-bai');
        const templateRowRev = document.getElementById('new_rev');
        const templateRowBai = document.getElementById('new_bai');
        
        if(addButtonRev && templateRowRev) {
            addButtonRev.addEventListener('click', function() {
                const newRow = templateRowRev.cloneNode(true);
                newRow.style.display = 'table-row';
                templateRowRev.parentNode.insertBefore(newRow, templateRowRev);
            });
        }

        if(addButtonBai && templateRowBai) {
            addButtonBai.addEventListener('click', function() {
                const newRow = templateRowBai.cloneNode(true);
                newRow.style.display = 'table-row';
                templateRowBai.parentNode.insertBefore(newRow, templateRowBai);
            });
        }
        // 为删除按钮添加事件委托
        document.addEventListener('click', function(e) {
            if(e.target.closest('.delete-row')) {
                e.target.closest('tr').remove();
            }
        });
    });
</script>