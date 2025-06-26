<?php
include_once 'db.php';

// 获取所有PDF文件
$sql = "SELECT pdf FROM pdf";
$stmt = $conn->prepare($sql);
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