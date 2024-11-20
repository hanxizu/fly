<?php 
ini_set('display_errors', 1);  // 显示错误信息，开发阶段可以开启
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// 获取前端请求的 JSON 数据
$data = json_decode(file_get_contents('php://input'), true);

// 获取设备ID（必传）
$deviceId = $data['deviceId'] ?? null;

// 获取按钮A和按钮B的状态
$buttonAStatus = $data['buttonAStatus'] ?? null;
$buttonBStatus = $data['buttonBStatus'] ?? null;

// 验证设备ID是否传递
if ($deviceId === null) {
    echo json_encode(['status' => 'error', 'message' => '设备ID不能为空']);
    exit();
}

// 验证按钮A和按钮B的状态
$validButtonAStatuses = ['started', 'paused', 'resumed'];
$validButtonBStatuses = ['stopped'];

// 只允许更新按钮A状态或按钮B状态中的一个
if ($buttonAStatus !== null && $buttonBStatus !== null) {
    echo json_encode(['status' => 'error', 'message' => '只能同时更新按钮A或按钮B中的一个状态']);
    exit();
}

// 如果提供了按钮A状态，验证其有效性
if ($buttonAStatus !== null && !in_array($buttonAStatus, $validButtonAStatuses)) {
    echo json_encode(['status' => 'error', 'message' => '无效的按钮A状态']);
    exit();
}

// 如果提供了按钮B状态，验证其有效性
if ($buttonBStatus !== null && !in_array($buttonBStatus, $validButtonBStatuses)) {
    echo json_encode(['status' => 'error', 'message' => '无效的按钮B状态']);
    exit();
}

// 获取 URL 中的 project 参数
$project = $_GET['project'] ?? null;

// 白名单：允许的项目列表
$allowedProjects = ['gmailextension'];

// 如果 project 参数无效，返回错误信息
if (!$project || !in_array($project, $allowedProjects)) {
    echo json_encode(['status' => 'error', 'message' => '无效的项目或表名']);
    exit();
}

// 数据库连接设置
$host = 'localhost';
$dbname = 'api_hanspaul';
$username = 'api_hanspaul';
$password = 'api_hanspaul';

try {
    // 使用 PDO 连接数据库
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 动态生成 SQL 查询，更新按钮A和按钮B的状态
    if ($buttonAStatus !== null) {
        // 更新按钮A状态
        $sql = "UPDATE `$project` SET 
                    buttonAStatus = :buttonAStatus,
                    buttonStatusUpdated_at = CURRENT_TIMESTAMP
                WHERE deviceId = :deviceId";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':buttonAStatus', $buttonAStatus, PDO::PARAM_STR);
    } elseif ($buttonBStatus !== null) {
        // 更新按钮B状态
        $sql = "UPDATE `$project` SET 
                    buttonBStatus = :buttonBStatus,
                    buttonStatusUpdated_at = CURRENT_TIMESTAMP
                WHERE deviceId = :deviceId";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':buttonBStatus', $buttonBStatus, PDO::PARAM_STR);
    }

    // 绑定 deviceId 参数
    $stmt->bindParam(':deviceId', $deviceId, PDO::PARAM_STR);

    // 执行查询
    if ($stmt->execute()) {
        // 返回成功的 JSON 响应
        echo json_encode(['status' => 'success', 'message' => '按钮状态更新成功']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '数据库更新失败']);
    }
} catch (PDOException $e) {
    // 返回详细的错误信息
    echo json_encode(['status' => 'error', 'message' => '数据库连接失败', 'error' => $e->getMessage()]);
}
?>
