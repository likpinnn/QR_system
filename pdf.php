<?php
include_once 'db.php';

// 辅助函数：构建分页链接参数
function buildPaginationParams($search, $search_type, $start_date, $end_date) {
    $params = array();
    if (!empty($search)) {
        $params[] = 'search=' . urlencode($search);
    }
    if (!empty($search_type) && $search_type != 'all') {
        $params[] = 'search_type=' . urlencode($search_type);
    }
    if (!empty($start_date)) {
        $params[] = 'start_date=' . urlencode($start_date);
    }
    if (!empty($end_date)) {
        $params[] = 'end_date=' . urlencode($end_date);
    }
    return !empty($params) ? '&' . implode('&', $params) : '';
}

// 设置每页显示的记录数
$records_per_page = 10;

// 获取搜索参数
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'all';
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

// 获取当前页码
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);

// 构建搜索条件
$where_clause = "";
if($_SESSION['role'] == 'user'){
    $where_clause = "WHERE A.user_id = '$_SESSION[userid]'";
}
$params = array();

if (!empty($search)) {
    if (!empty($where_clause)) {
        $where_clause .= " AND ";
    } else {
        $where_clause = "WHERE ";
    }
    
    switch($search_type) {
        case 'bai_no':
            if($_SESSION['role'] == 'user'){
                $where_clause .= "A.user_id = '$_SESSION[userid]' AND A.pdf LIKE :search";
            }else{
                $where_clause .= "A.pdf LIKE :search";
            }
            $params[':search'] = "%$search%";
            break;
        case 'rev':
            if($_SESSION['role'] == 'user'){
                $where_clause .= "A.user_id = '$_SESSION[userid]' AND A.pdf LIKE :search";
            }else{
                $where_clause .= "A.pdf LIKE :search";
            }
            $params[':search'] = "%$search%";
            break;
        case 'name':
            $where_clause .= "B.name LIKE :search";
            $params[':search'] = "%$search%";
            break;
        default:
            if($_SESSION['role'] == 'user'){
                $where_clause .= "A.user_id = '$_SESSION[userid]' AND (A.pdf LIKE :search OR B.name LIKE :search)";
            }else{
                $where_clause .= "A.pdf LIKE :search OR B.name LIKE :search";
            }
            $params[':search'] = "%$search%";
    }
}

// 处理日期范围筛选
if (!empty($start_date) || !empty($end_date)) {
    if (!empty($where_clause)) {
        $where_clause .= " AND ";
    } else {
        $where_clause = "WHERE ";
    }
    
    if (!empty($start_date) && !empty($end_date)) {
        $where_clause .= "DATE(A.created_at) BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    } elseif (!empty($start_date)) {
        $where_clause .= "DATE(A.created_at) >= :start_date";
        $params[':start_date'] = $start_date;
    } elseif (!empty($end_date)) {
        $where_clause .= "DATE(A.created_at) <= :end_date";
        $params[':end_date'] = $end_date;
    }
}

// 计算偏移量
$offset = ($page - 1) * $records_per_page;

// 获取总记录数
$count_sql = "SELECT COUNT(*) as total FROM pdf as A
INNER JOIN users as B ON A.user_id = B.id
$where_clause";
$count_stmt = $conn->prepare($count_sql);
foreach($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// 计算总页数
$total_pages = ceil($total_records / $records_per_page);

// 获取当前页的记录
if($_SESSION['role'] == 'user'){
    $sql = "SELECT A.*, B.name as created_by_name FROM pdf A
    INNER JOIN users B ON A.user_id = B.id
    $where_clause
    ORDER BY created_at DESC LIMIT :offset, :records_per_page";
}else{
    $sql = "SELECT A.*, B.name as created_by_name FROM pdf A
    INNER JOIN users B ON A.user_id = B.id
    $where_clause
    ORDER BY created_at DESC LIMIT :offset, :records_per_page";
}
$stmt = $conn->prepare($sql);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$pdfs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Report System</title>
    <style>
        .search-container {
            margin-bottom: 20px;
        }
        .search-input, .date-input, .search-select {
            min-width: 120px;
        }
        .input-group-text {
            background: #f8f9fa;
        }
        .search-button, .clear-button {
            min-width: 90px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a, .pagination span {
            padding: 8px 16px;
            margin: 0 4px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
        }
        .pagination a:hover {
            background-color: #f5f5f5;
        }
        .pagination .active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .pagination .disabled {
            color: #999;
            pointer-events: none;
        }
    </style>
</head>
<?php 
    include_once 'header.php'; 
    include_once 'loading.php';
?>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4 mt-2"><i class="fa-solid fa-file-pdf me-3"></i>PDF History</h1>
                
                <!-- 搜索栏 -->
                <div class="search-container">
                    <form method="GET" action="">
                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <select name="search_type" class="form-select search-select">
                                    <option value="all" <?php echo $search_type == 'all' ? 'selected' : ''; ?>>All</option>
                                    <option value="bai_no" <?php echo $search_type == 'bai_no' ? 'selected' : ''; ?>>BAI No</option>
                                    <option value="rev" <?php echo $search_type == 'rev' ? 'selected' : ''; ?>>Rev</option>
                                    <?php if($_SESSION['role'] == 'admin'): ?>
                                        <option value="name" <?php echo $search_type == 'name' ? 'selected' : ''; ?>>Name</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-auto">
                                <input type="text" name="search" class="form-control search-input" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search...">
                            </div>
                            <div class="col-auto">
                                <div class="input-group">
                                    <span class="input-group-text">From</span>
                                    <input type="date" name="start_date" class="form-control date-input" value="<?php echo htmlspecialchars($start_date); ?>">
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="input-group">
                                    <span class="input-group-text">To</span>
                                    <input type="date" name="end_date" class="form-control date-input" value="<?php echo htmlspecialchars($end_date); ?>">
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary search-button">Search</button>
                            </div>
                            <?php if(!empty($search) || !empty($start_date) || !empty($end_date)): ?>
                            <div class="col-auto">
                                <a href="pdf.php" class="btn btn-secondary clear-button">Clear</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <?php if($role != 'user'){?>
                <div class="row mb-2">
                    <div class="col-md-12">
                        <button class="btn btn-danger" onclick="deleteAllPDF()">Delete All</button>
                        <a href="download_all_pdf.php?<?php echo http_build_query($_GET); ?>" class="btn btn-success">Download All PDFs</a>
                    </div>
                </div>
                <?php }?>

                <table class="table table-bordered table-striped shadow">
                    <tr class="table-dark">
                        <th style="width: 25%">Bai No</th>
                        <th style="width: 25%">Rev</th>
                        <th style="width: 20%">Created At</th>
                        <th style="width: 20%">Created By</th>
                        <th style="width: 10%"></th>
                    </tr>
                    <?php foreach($pdfs as $pdf): 
                        $split_pdf = explode('/', $pdf['pdf']);
                        $pdf_name = $split_pdf[count($split_pdf) - 1];
                        $split_pdf_name = explode('.', $pdf_name);
                        list($bai_no, $rev, $date) = explode('_', $split_pdf_name[0]);
                    ?>
                    <tr>
                        <td><?php echo $bai_no; ?></td>
                        <td><?php echo $rev; ?></td>
                        <td><?php echo $pdf['created_at']; ?></td>
                        <td><?php echo $pdf['created_by_name']; ?></td>
                        <td>
                            <a href="<?=$pdf['pdf']?>" target="_blank" class="link-dark link-underline link-underline-opacity-0">
                                <i class="fa-regular fa-file-pdf"></i> View
                            </a>
                            <a href="#" onclick="deletePDF(<?php echo $pdf['id']; ?>)" class="link-danger link-underline link-underline-opacity-0 ms-2">
                                <i class="fa-regular fa-trash-can"></i> Del
                            </a>   
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <!-- 分页导航 -->
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?page=1<?php echo buildPaginationParams($search, $search_type, $start_date, $end_date); ?>">First</a>
                        <a href="?page=<?php echo $page-1; ?><?php echo buildPaginationParams($search, $search_type, $start_date, $end_date); ?>"> < </a>
                    <?php else: ?>
                        <span class="disabled">First</span>
                        <span class="disabled"> < </span>
                    <?php endif; ?>

                    <?php
                    // 显示页码
                    if($total_pages <= 3) {
                        // 如果总页数小于等于3，显示所有页码
                        for($i = 1; $i <= $total_pages; $i++) {
                            if($i == $page) {
                                echo '<span class="active">' . $i . '</span>';
                            } else {
                                echo '<a href="?page=' . $i . buildPaginationParams($search, $search_type, $start_date, $end_date) . '">' . $i . '</a>';
                            }
                        }
                    } else {
                        // 如果总页数大于3，使用省略号
                        if($page <= 2) {
                            // 当前页在前两页
                            for($i = 1; $i <= 3; $i++) {
                                if($i == $page) {
                                    echo '<span class="active">' . $i . '</span>';
                                } else {
                                    echo '<a href="?page=' . $i . buildPaginationParams($search, $search_type, $start_date, $end_date) . '">' . $i . '</a>';
                                }
                            }
                            echo '<span>...</span>';
                            echo '<a href="?page=' . $total_pages . buildPaginationParams($search, $search_type, $start_date, $end_date) . '">' . $total_pages . '</a>';
                        } elseif($page >= $total_pages - 1) {
                            // 当前页在最后两页
                            echo '<a href="?page=1' . buildPaginationParams($search, $search_type, $start_date, $end_date) . '">1</a>';
                            echo '<span>...</span>';
                            for($i = $total_pages - 2; $i <= $total_pages; $i++) {
                                if($i == $page) {
                                    echo '<span class="active">' . $i . '</span>';
                                } else {
                                    echo '<a href="?page=' . $i . buildPaginationParams($search, $search_type, $start_date, $end_date) . '">' . $i . '</a>';
                                }
                            }
                        } else {
                            // 当前页在中间
                            echo '<a href="?page=1' . buildPaginationParams($search, $search_type, $start_date, $end_date) . '">1</a>';
                            echo '<span>...</span>';
                            for($i = $page - 1; $i <= $page + 1; $i++) {
                                if($i == $page) {
                                    echo '<span class="active">' . $i . '</span>';
                                } else {
                                    echo '<a href="?page=' . $i . buildPaginationParams($search, $search_type, $start_date, $end_date) . '">' . $i . '</a>';
                                }
                            }
                            echo '<span>...</span>';
                            echo '<a href="?page=' . $total_pages . buildPaginationParams($search, $search_type, $start_date, $end_date) . '">' . $total_pages . '</a>';
                        }
                    }
                    ?>

                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?><?php echo buildPaginationParams($search, $search_type, $start_date, $end_date); ?>"> > </a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo buildPaginationParams($search, $search_type, $start_date, $end_date); ?>">Last</a>
                    <?php else: ?>
                        <span class="disabled"> > </span>
                        <span class="disabled">Last</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    function deletePDF(id) {
        if(confirm('Are you sure you want to delete this PDF?')) {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    // 删除成功后刷新页面
                    window.location.reload();
                }
            };
            xhttp.open("GET", "delete_report.php?pdf=" + id, true);
            xhttp.send();
        }
    }

    function deleteAllPDF() {
        var confirmText = prompt('Please type "DELETE ALL" to confirm deletion:');
        if(confirmText === "DELETE ALL") {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    // 删除成功后刷新页面
                    window.location.reload();
                }
            };
            xhttp.open("GET", "delete_report.php?delete_all=true", true);
            xhttp.send();
        } else if(confirmText !== null) {
            alert('Incorrect text. Please type "DELETE ALL" to confirm deletion.');
        }
    }


</script>
</html>