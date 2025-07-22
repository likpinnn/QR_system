<?php
include_once 'db.php';

// 获取筛选参数
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'all';
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

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

// 获取所有符合条件的PDF文件
$sql = "SELECT A.pdf FROM pdf AS A INNER JOIN users AS B ON A.user_id = B.id $where_clause";
$stmt = $conn->prepare($sql);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$pdfs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 创建临时目录
$temp_dir = 'temp_pdf_' . time();
if (!file_exists($temp_dir)) {
    mkdir($temp_dir, 0777, true);
}

// 复制所有PDF文件到临时目录
foreach ($pdfs as $pdf) {
    $source_file = $pdf['pdf'];
    if (file_exists($source_file)) {
        $filename = basename($source_file);
        copy($source_file, $temp_dir . '/' . $filename);
    }
}

// 创建ZIP文件
$zipname = 'all_pdfs_' . date('Y-m-d_H-i-s') . '.zip';
$zip = new ZipArchive();
$zip->open($zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// 添加文件到ZIP
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($temp_dir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $filename = basename($filePath); // 只用文件名
        $zip->addFile($filePath, $filename);
    }
}

$zip->close();

// 设置下载头
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipname . '"');
header('Content-Length: ' . filesize($zipname));

// 清理输出缓冲，防止多余内容
ob_clean();
flush();

// 输出文件
readfile($zipname);

// 清理临时文件和目录
unlink($zipname);
array_map('unlink', glob("$temp_dir/*.*"));
rmdir($temp_dir); 