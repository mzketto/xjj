<?php
// api/log_not_found.php
date_default_timezone_set('Asia/Shanghai'); // 请根据服务器时区调整

// 接收 POST 参数
$api_name = $_POST['api_name'] ?? '';
$video_id = $_POST['video_id'] ?? '';
$time = date('Y-m-d H:i:s');

if ($api_name && $video_id) {
    $log_line = "[$time] API: $api_name, 视频ID: $video_id 未找到\n";
    $log_dir = __DIR__ . '/logs';
    $log_file = $log_dir . '/not_found.log';

    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true);
    }

    file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
    echo json_encode(['code' => 1, 'msg' => '日志写入成功']);
} else {
    echo json_encode(['code' => 0, 'msg' => '缺少参数']);
}
