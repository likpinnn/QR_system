<?php
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM report WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $result = $stmt->execute();
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => '删除成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '没有找到要删除的记录']);
    }
    // 设置 JSON 头部
header('Content-Type: application/json');
} 

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['rev_id'])) {
    $rev_id = $_GET['rev_id'];
    
    $stmt = $conn->prepare("DELETE FROM revision_history WHERE id = :id");
    $stmt->bindParam(':id', $rev_id);
    $result = $stmt->execute();

    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => '删除成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '没有找到要删除的记录']);
    }
    // 设置 JSON 头部
header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['bai_id'])) {
    $bai_id = $_GET['bai_id'];
    
    $stmt = $conn->prepare("DELETE FROM bai_no WHERE id = :id");
    $stmt->bindParam(':id', $bai_id);
    $result = $stmt->execute();

    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => '删除成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '没有找到要删除的记录']);
    }
    // 设置 JSON 头部
header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['bairev_id'])) {
    $bairev_id = $_GET['bairev_id'];

    $stmt = $conn->prepare("DELETE FROM rev WHERE id = :id");
    $stmt->bindParam(':id', $bairev_id);
    $result = $stmt->execute();

    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => '删除成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '没有找到要删除的记录']);
    }
    // 设置 JSON 头部
header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['pdf'])) {
    $pdf_id = $_GET['pdf'];
    
    // 首先获取PDF文件路径
    $pdf_path = $conn->prepare("SELECT pdf FROM pdf WHERE id = :id");
    $pdf_path->bindParam(':id', $pdf_id);
    $pdf_path->execute();
    $pdf_path = $pdf_path->fetch(PDO::FETCH_ASSOC);

    if ($pdf_path && file_exists($pdf_path['pdf'])) {
        // 删除PDF文件
        unlink($pdf_path['pdf']);

         // 删除数据库记录
        $stmt = $conn->prepare("DELETE FROM pdf WHERE id = :id");
        $stmt->bindParam(':id', $pdf_id);
        $result = $stmt->execute();
    }

    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => '删除成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '没有找到要删除的记录']);
    }
    // 设置 JSON 头部
    header('Content-Type: application/json'); 
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user'])) {
    $user_id = $_GET['user'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $result = $stmt->execute();

    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => '删除成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '没有找到要删除的记录']);
    }
    // 设置 JSON 头部
header('Content-Type: application/json');
}   
?> 