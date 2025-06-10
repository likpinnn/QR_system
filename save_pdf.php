<?php
// 确保在输出任何内容之前设置header
header('Content-Type: application/json');


// 确保没有之前的输出
ob_start();

try {
    include_once 'db.php';

    // 检查并添加unique_id列
    $check_column_sql = "PRAGMA table_info(pdf)";
    $columns = $conn->query($check_column_sql)->fetchAll(PDO::FETCH_ASSOC);
    $has_unique_id = false;
    
    foreach ($columns as $column) {
        if ($column['name'] === 'unique_id') {
            $has_unique_id = true;
            break;
        }
    }
    
    if (!$has_unique_id) {
        $add_column_sql = "ALTER TABLE pdf ADD COLUMN unique_id VARCHAR(255)";
        $conn->exec($add_column_sql);
    }

    // 检查是否收到PDF文件
    if (!isset($_FILES['pdf']) || !isset($_POST['filename'])) {
        throw new Exception('缺少必要的数据');
    }

    // 获取PDF文件和文件名
    $pdfFile = $_FILES['pdf'];
    $filename = $_POST['filename'];

    // 检查文件上传错误
    if ($pdfFile['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('文件上传错误: ' . $pdfFile['error']);
    }

    // 确保assets/pdf目录存在
    $pdfDir = 'assets/pdf';
    if (!file_exists($pdfDir)) {
        if (!mkdir($pdfDir, 0777, true)) {
            throw new Exception('无法创建PDF目录');
        }
    }

    // 检查目录权限
    if (!is_writable($pdfDir)) {
        throw new Exception('PDF目录没有写入权限');
    }

    // 生成唯一文件名
    $filepath = $pdfDir . '/' . $filename;

    // 先保存文件
    if (!move_uploaded_file($pdfFile['tmp_name'], $filepath)) {
        throw new Exception('保存PDF文件失败');
    }

    // 然后保存到数据库
    $sql = "INSERT INTO pdf (pdf, user_id) VALUES (:pdf_path, :user_id)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':pdf_path', $filepath);
    $stmt->bindParam(':user_id', $_SESSION['userid']);
    
    if (!$stmt->execute()) {
        throw new Exception('数据库插入失败');
    }

    // 清除之前的输出
    ob_clean();
    
    // 输出成功响应
    echo json_encode([
        'success' => true, 
        'message' => 'PDF saved successfully',
        'path' => $filepath
    ]);

} catch (Exception $e) {
    // 清除之前的输出
    ob_clean();
    
    // 记录错误到日志
    error_log('PDF save error: ' . $e->getMessage());
    
    // 输出错误响应
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// 结束输出缓冲
ob_end_flush();
?>