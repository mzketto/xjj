<?php
// 安全检查：仅允许以 http(s) 开头
$url = $_GET['url'] ?? '';
if (!$url || !preg_match('#^https?://#', $url)) {
  http_response_code(400);
  echo json_encode(['error' => '无效的URL']);
  exit;
}

// 设置跨域响应头
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// 代理请求
$opts = ["http" => ["timeout" => 5]];
$ctx = stream_context_create($opts);
$data = file_get_contents($url, false, $ctx);

if ($data === false) {
  http_response_code(500);
  echo json_encode(['error' => '代理请求失败']);
} else {
  echo $data;
}
