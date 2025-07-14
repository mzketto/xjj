<?php
// api/log_not_found.php
date_default_timezone_set('Asia/Shanghai'); // 根据服务器调整时区

header('Content-Type: application/json');

require '../config.php';// 根据实际路径修改，包含数据库连接，$conn

$api_name = $_POST['api_name'] ?? '';
$video_id = $_POST['video_id'] ?? '';
$time = date('Y-m-d H:i:s');

if ($api_name && $video_id) {
    $stmt = $conn->prepare("INSERT INTO not_found_logs (api_name, video_id, created_at) VALUES (?, ?, ?)");
    if ($stmt === false) {
        echo json_encode(['code' => 0, 'msg' => '数据库准备失败']);
        exit;
    }

    $stmt->bind_param('sss', $api_name, $video_id, $time);
    $execRes = $stmt->execute();
    $stmt->close();

    if ($execRes) {
        echo json_encode(['code' => 1, 'msg' => '日志写入成功']);
    } else {
        echo json_encode(['code' => 0, 'msg' => '数据库写入失败']);
    }
} else {
    echo json_encode(['code' => 0, 'msg' => '缺少参数']);
}
