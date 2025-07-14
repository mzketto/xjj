<?php
// 文件路径：api/log_visit.php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Shanghai');

// 相对路径引入 config.php
require '../config.php';


$data = json_decode(file_get_contents('php://input'), true);

// 参数验证
if (
  !$data || 
  !isset($data['ip']) || 
  !isset($data['isp']) || 
  !isset($data['device']) || 
  !isset($data['time']) || 
  !isset($data['api_name']) || 
  !isset($data['vod_name']) || 
  !isset($data['vod_id'])
) {
  echo json_encode(['code' => 0, 'msg' => '缺少参数']);
  exit;
}

// 提取字段
$ip       = $data['ip'];
$isp      = $data['isp'];
$device   = $data['device'];
$time     = $data['time'];
$api_name = $data['api_name'];
$vod_name = $data['vod_name'];
$vod_id   = intval($data['vod_id']);

// 时间转换为北京时间
$datetime = strtotime($time);
if ($datetime === false) $datetime = time();
$dateStr = date('Y-m-d', $datetime);
$timeStr = date('H:i:s', $datetime);

// 地理位置（可前端提供，也可后台再次解析）
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

// 插入数据库
$stmt = $conn->prepare("INSERT INTO visit_logs (date, time, ip, isp, geo, device, api_name, vod_name, vod_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('ssssssssi', $dateStr, $timeStr, $ip, $isp, $geo, $device, $api_name, $vod_name, $vod_id);

if ($stmt->execute()) {
  echo json_encode(['code' => 1, 'msg' => '记录成功']);
} else {
  echo json_encode(['code' => 0, 'msg' => '数据库插入失败']);
}

$stmt->close();
$conn->close();
