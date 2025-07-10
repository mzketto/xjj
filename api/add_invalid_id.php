<?php
// 设置响应为 JSON 格式
header('Content-Type: application/json');

// 只允许 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['code' => 0, 'msg' => '只支持POST请求']);
    exit;
}

// 读取 POST 请求的原始数据
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 校验字段是否齐全且合法
if (
    !isset($data['id'], $data['api_name']) ||
    !is_numeric($data['id']) ||
    !is_string($data['api_name'])
) {
    echo json_encode(['code' => 0, 'msg' => '缺少id或api_name']);
    exit;
}

// 获取数据
$id = intval($data['id']);
$apiName = trim($data['api_name']);
$filename = __DIR__ . '/invalid_ids.txt';

// 当前时间（格式：2025-07-10 03:14:15）
$timeStr = date('Y-m-d H:i:s');

// 读取已有记录，避免重复写入（只判断 api_name + id 的组合）
$existing = [];
if (file_exists($filename)) {
    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // 拆分出前两段，忽略时间部分
        $parts = explode(' ', $line, 3); // 第三个参数限制分成最多3段
        if (count($parts) >= 2) {
            $key = $parts[0] . ' ' . $parts[1]; // 组合为 apiName id
            $existing[$key] = true;
        }
    }
}

// 组合新的一行：api名称 + id + 时间
$entryKey = $apiName . ' ' . $id;
$entryFull = $entryKey . ' ' . $timeStr;

// 如果该 entry 不存在，则写入
if (!isset($existing[$entryKey])) {
    file_put_contents($filename, $entryFull . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// 返回成功响应
echo json_encode(['code' => 1, 'msg' => '添加成功']);
