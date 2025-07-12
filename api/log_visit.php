<?php
// 文件路径：api/log_visit.php
header('Content-Type: application/json');

// 设置为北京时间
date_default_timezone_set('Asia/Shanghai');

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
$device = $data['device'];
$time = $data['time'];
$api_name = $data['api_name'];
$vod_name = $data['vod_name'];

// 解析并转换为北京时间
$datetime = strtotime($time);
$dateStr = date('Y-m-d', $datetime);   // 日期：2025-07-12
$timeStr = date('H:i:s', $datetime);   // 时间：13:47:59

// 使用 ip-api 获取中文物理地址
function getChineseLocation($ip) {
    $url = "http://ip-api.com/json/{$ip}?lang=zh-CN";
    $res = @file_get_contents($url);
    if ($res === false) return '未知';

    $json = json_decode($res, true);
    if (!isset($json['status']) || $json['status'] !== 'success') return '未知';

    $location = $json['country'] ?? '';
    if (!empty($json['regionName'])) {
        $location .= " " . $json['regionName'];
    }
    if (!empty($json['city']) && $json['city'] !== $json['regionName']) {
        $location .= " " . $json['city'];
    }
    return $location ?: '未知';
}

$geo = getChineseLocation($ip);

// 记录日志到 visit_log.txt
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

file_put_contents(__DIR__ . '/visit_log.txt', $log, FILE_APPEND | LOCK_EX);

echo json_encode(['code' => 1, 'msg' => '记录成功']);
