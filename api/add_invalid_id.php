<?php
// api/add_invalid_id.php
header('Content-Type: application/json');
require '../config.php';
 // $conn
 
 
 date_default_timezone_set('Asia/Shanghai');

$now = date('Y-m-d H:i:s'); // 当前北京时间

$stmt = $conn->prepare("INSERT IGNORE INTO invalid_logs (api_name, invalid_id, created_at) VALUES (?, ?, ?)");
$stmt->bind_param('sis', $api_name, $vod_id, $now);
$success = $stmt->execute();
$stmt->close();

$data = json_decode(file_get_contents('php://input'), true);
$api_name = $data['api_name'] ?? '';
$vod_id = $data['id'] ?? null;

if (!$api_name || !$vod_id) {
    echo json_encode(['code' => 0, 'msg' => '缺少参数']);
    exit;
}

$vod_id = intval($vod_id);

$stmt = $conn->prepare("INSERT IGNORE INTO invalid_logs (api_name, invalid_id) VALUES (?, ?)");
$stmt->bind_param('si', $api_name, $vod_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo json_encode(['code' => 1, 'msg' => '无效ID记录成功']);
} else {
    echo json_encode(['code' => 0, 'msg' => '数据库写入失败']);
}
