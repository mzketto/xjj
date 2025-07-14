<?php
require '../config.php';
$data = ["apis" => [], "parse_url" => "https://your-parse-url.com/parse?url="]; // 直接使用默认解析地址

// 获取视频API列表
$res = $conn->query("SELECT * FROM video_apis ORDER BY id DESC");
while ($row = $res->fetch_assoc()) {
  $data['apis'][] = ["id" => $row['id'], "name" => $row['name'], "api_url" => $row['api_url']];
}


echo json_encode($data);
?>
