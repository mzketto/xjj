<?php
header('Content-Type: application/json');
require '../config.php'; // 请根据你的目录结构调整路径

// 获取用户IP（可用来过滤）
$user_ip = $_SERVER['REMOTE_ADDR'] ?? '';

$sql = "SELECT date, time, ip, api_name, vod_name, vod_id FROM visit_logs WHERE ip = ? ORDER BY created_at DESC LIMIT 100";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_ip);
$stmt->execute();
$result = $stmt->get_result();

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

echo json_encode($logs);
