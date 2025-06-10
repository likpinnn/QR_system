<?php
include_once 'db.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['type']) && isset($_GET['step'])) {
        $type = $_GET['type'];
        $step = $_GET['step'];
        $action = 'NA'; // 添加默认的空 action

        $stmt = $conn->prepare("DELETE FROM report WHERE type = :type AND step = :step");
        $stmt->bindParam(':step', $step);
        $stmt->bindParam(':type', $type);
        $result = $stmt->execute();

        if ($result) {
            $response = ['success' => true, 'message' => 'Step deleted successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to delete step'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Invalid request parameters'];
    }
} catch (PDOException $e) {
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
}

// 确保没有其他输出
ob_clean();
echo json_encode($response);
exit;
?>