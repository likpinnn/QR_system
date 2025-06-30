<?php
include_once 'db.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 5;
$offset = ($page - 1) * $records_per_page;

if ($type === 'bai') {
    // 获取总记录数
    $count_sql = "SELECT COUNT(*) as total FROM `bai_no` WHERE `bai_no` LIKE :search";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute([':search' => "%$search%"]);
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_records / $records_per_page);

    // 获取分页数据
    $sql = "SELECT * FROM `bai_no` WHERE `bai_no` LIKE :search ORDER BY `bai_no` ASC LIMIT :offset, :records_per_page";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':records_per_page', $records_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table class="table table-bordered table-striped">
        <tr class="table-dark">
            <th class="w-100">Bai No</th>
            <th></th>
        </tr>';
    
    foreach ($data as $bai) {
        echo '<tr>
            <td>' . htmlspecialchars($bai['bai_no']) . '</td>
            <td>
                <button class="btn text-danger fs-5" onclick="del_bai(' . $bai['id'] . ')">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        </tr>';
    }
    
    echo '</table>';

    // 分页信息
    echo '<div class="d-flex justify-content-between align-items-center">
        <div class="pagination-info">
            Showing ' . min(($page-1)*$records_per_page + 1, $total_records) . ' to 
            ' . min($page*$records_per_page, $total_records) . ' of 
            ' . $total_records . ' entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0">';
    
    // 上一页
    echo '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">
        <a class="page-link search-page-link" href="#" data-page="' . ($page-1) . '" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
        </a>
    </li>';

    // 页码
    $start_page = max(1, $page - 1);
    $end_page = min($total_pages, $page + 1);
    
    if($start_page > 1) {
        echo '<li class="page-item"><a class="page-link search-page-link" href="#" data-page="1">1</a></li>';
        if($start_page > 2) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for($i = $start_page; $i <= $end_page; $i++) {
        echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
            <a class="page-link search-page-link" href="#" data-page="' . $i . '">' . $i . '</a>
        </li>';
    }
    
    if($end_page < $total_pages) {
        if($end_page < $total_pages - 1) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        echo '<li class="page-item"><a class="page-link search-page-link" href="#" data-page="' . $total_pages . '">' . $total_pages . '</a></li>';
    }

    // 下一页
    echo '<li class="page-item ' . ($page >= $total_pages ? 'disabled' : '') . '">
        <a class="page-link search-page-link" href="#" data-page="' . ($page+1) . '" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
        </a>
    </li>';

    echo '</ul></nav></div>';

} elseif ($type === 'rev') {
    // 获取总记录数
    $count_sql = "SELECT COUNT(*) as total FROM `rev` WHERE `rev` LIKE :search";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute([':search' => "%$search%"]);
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_records / $records_per_page);

    // 获取分页数据
    $sql = "SELECT * FROM `rev` WHERE `rev` LIKE :search ORDER BY `rev` ASC LIMIT :offset, :records_per_page";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':records_per_page', $records_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table class="table table-bordered table-striped">
        <tr class="table-dark">
            <th class="w-100">Rev</th>
            <th></th>
        </tr>';
    
    foreach ($data as $rev) {
        echo '<tr>
            <td>' . htmlspecialchars($rev['rev']) . '</td>
            <td>
                <button class="btn text-danger fs-5" onclick="del_rev(' . $rev['id'] . ')">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        </tr>';
    }
    
    echo '</table>';

    // 分页信息
    echo '<div class="d-flex justify-content-between align-items-center">
        <div class="pagination-info">
            Showing ' . min(($page-1)*$records_per_page + 1, $total_records) . ' to 
            ' . min($page*$records_per_page, $total_records) . ' of 
            ' . $total_records . ' entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0">';
    
    // 上一页
    echo '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">
        <a class="page-link search-page-link" href="#" data-page="' . ($page-1) . '" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
        </a>
    </li>';

    // 页码
    $start_page = max(1, $page - 1);
    $end_page = min($total_pages, $page + 1);
    
    if($start_page > 1) {
        echo '<li class="page-item"><a class="page-link search-page-link" href="#" data-page="1">1</a></li>';
        if($start_page > 2) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for($i = $start_page; $i <= $end_page; $i++) {
        echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
            <a class="page-link search-page-link" href="#" data-page="' . $i . '">' . $i . '</a>
        </li>';
    }
    
    if($end_page < $total_pages) {
        if($end_page < $total_pages - 1) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        echo '<li class="page-item"><a class="page-link search-page-link" href="#" data-page="' . $total_pages . '">' . $total_pages . '</a></li>';
    }

    // 下一页
    echo '<li class="page-item ' . ($page >= $total_pages ? 'disabled' : '') . '">
        <a class="page-link search-page-link" href="#" data-page="' . ($page+1) . '" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
        </a>
    </li>';

    echo '</ul></nav></div>';
}
?> 