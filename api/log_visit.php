<?php
// 文件路径：api/log_visit.php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// 验证字段
if (
  !$data || 
  !isset($data['ip']) || 
  !isset($data['device']) || 
  !isset($data['time']) || 
  !isset($data['api_name']) || 
  !isset($data['vod_name'])
) {
  echo json_encode(['code' => 0, 'msg' => '缺少参数']);
  exit;
}

// 提取字段
$ip = $data['ip'];
$geo = $data['geo'] ?? '未知';
$device = $data['device'];
$time = $data['time'];
$api_name = $data['api_name'];
$vod_name = $data['vod_name'];

// 拆分时间为日期 + 时间
$datetime = strtotime($time);
$dateStr = date('Y-m-d', $datetime);
$timeStr = date('H:i:s', $datetime);

// 拼接日志（以 | 分隔，注意不要使用中括号）
$log = sprintf(
  "%s | %s | %s | %s | %s | %s | %s\n",
  $dateStr,
  $timeStr,
  $ip,
  $geo,
  $device,
  $api_name,
  $vod_name
);

// 保存到 visit_log.txt
file_put_contents(__DIR__ . '/visit_log.txt', $log, FILE_APPEND | LOCK_EX);

echo json_encode(['code' => 1, 'msg' => '记录成功']);
