<?php
include_once 'db.php';

// 设置每页显示的记录数
$records_per_page = 10;

// 获取搜索参数
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'all';

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
    switch($search_type) {
        case 'bai_no':
            if($_SESSION['role'] == 'user'){
                $where_clause = "WHERE A.user_id = '$_SESSION[userid]' AND A.pdf LIKE :search";
            }else{
                $where_clause = "WHERE A.pdf LIKE :search";
            }
            $params[':search'] = "%$search%";
            break;
        case 'rev':
            if($_SESSION['role'] == 'user'){
                $where_clause = "WHERE A.user_id = '$_SESSION[userid]' AND A.pdf LIKE :search";
            }else{
                $where_clause = "WHERE A.pdf LIKE :search";
            }
            $params[':search'] = "%$search%";
            break;
        case 'date':
            if($_SESSION['role'] == 'user'){
                $where_clause = "WHERE A.user_id = '$_SESSION[userid]' AND A.created_at LIKE :search";
            }else{
                $where_clause = "WHERE A.created_at LIKE :search";
            }
            $params[':search'] = "%$search%";
            break;
        case 'name':
            $where_clause = "WHERE B.name LIKE :search";
            $params[':search'] = "%$search%";
            break;
        default:
            if($_SESSION['role'] == 'user'){
                $where_clause = "WHERE A.user_id = '$_SESSION[userid]' AND (A.pdf LIKE :search OR A.created_at LIKE :search OR B.name LIKE :search)";
            }else{
                $where_clause = "WHERE A.pdf LIKE :search OR A.created_at LIKE :search OR B.name LIKE :search";
            }
            $params[':search'] = "%$search%";
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
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .search-input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 200px;
        }
        .search-select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-button {
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-button:hover {
            background-color: #0056b3;
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
<?php include_once 'header.php'; ?>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-5">PDF History</h1>
                
                <!-- 搜索栏 -->
                <div class="search-container">
                    <form method="GET" action="" class="d-flex">
                        <select name="search_type" class="search-select">
                            <option value="all" <?php echo $search_type == 'all' ? 'selected' : ''; ?>>All</option>
                            <option value="bai_no" <?php echo $search_type == 'bai_no' ? 'selected' : ''; ?>>BAI No</option>
                            <option value="rev" <?php echo $search_type == 'rev' ? 'selected' : ''; ?>>Rev</option>
                            <option value="date" <?php echo $search_type == 'date' ? 'selected' : ''; ?>>Date</option>
                            <?php if($_SESSION['role'] == 'admin'): ?>
                                <option value="name" <?php echo $search_type == 'name' ? 'selected' : ''; ?>>Name</option>
                            <?php endif; ?>
                        </select>
                        <input type="text" name="search" class="search-input" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search...">
                        <button type="submit" class="search-button">Search</button>
                        <?php if(!empty($search)): ?>
                            <a href="pdf.php" class="search-button" style="background-color: #6c757d; text-decoration: none;">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>

                <table class="table table-bordered table-striped">
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
                        <a href="?page=1<?php echo !empty($search) ? '&search='.urlencode($search).'&search_type='.$search_type : ''; ?>">First</a>
                        <a href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search).'&search_type='.$search_type : ''; ?>"> < </a>
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
                                echo '<a href="?page=' . $i . (!empty($search) ? '&search='.urlencode($search).'&search_type='.$search_type : '') . '">' . $i . '</a>';
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
                                    echo '<a href="?page=' . $i . (!empty($search) ? '&search='.urlencode($search).'&search_type='.$search_type : '') . '">' . $i . '</a>';
                                }
                            }
                            echo '<span>...</span>';
                            echo '<a href="?page=' . $total_pages . (!empty($search) ? '&search='.urlencode($search).'&search_type='.$search_type : '') . '">' . $total_pages . '</a>';
                        } elseif($page >= $total_pages - 1) {
                            // 当前页在最后两页
                            echo '<a href="?page=1' . (!empty($search) ? '&search='.urlencode($search).'&search_type='.$search_type : '') . '">1</a>';
                            echo '<span>...</span>';
                            for($i = $total_pages - 2; $i <= $total_pages; $i++) {
                                if($i == $page) {
                                    echo '<span class="active">' . $i . '</span>';
                                } else {
                                    echo '<a href="?page=' . $i . (!empty($search) ? '&search='.urlencode($search).'&search_type='.$search_type : '') . '">' . $i . '</a>';
                                }
                            }
                        } else {
                            // 当前页在中间
                            echo '<a href="?page=1' . (!empty($search) ? '&search='.urlencode($search).'&search_type='.$search_type : '') . '">1</a>';
                            echo '<span>...</span>';
                            for($i = $page - 1; $i <= $page + 1; $i++) {
                                if($i == $page) {
                                    echo '<span class="active">' . $i . '</span>';
                                } else {
                                    echo '<a href="?page=' . $i . (!empty($search) ? '&search='.urlencode($search).'&search_type='.$search_type : '') . '">' . $i . '</a>';
                                }
                            }
                            echo '<span>...</span>';
                            echo '<a href="?page=' . $total_pages . (!empty($search) ? '&search='.urlencode($search).'&search_type='.$search_type : '') . '">' . $total_pages . '</a>';
                        }
                    }
                    ?>

                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search).'&search_type='.$search_type : ''; ?>"> > </a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search='.urlencode($search).'&search_type='.$search_type : ''; ?>">Last</a>
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
</script>
</html>