<?php
require '../config.php';

$name = $_POST['name'] ?? '';
$api = $_POST['api_url'] ?? '';

if ($name && $api) {
    // 判断 $api 是否已经包含 /api.php/provide/vod/at/json/?ac=detail 字符串
    $check_str = '/api.php/provide/vod/at/json/?ac=detail';

    if (strpos($api, $check_str) === false) {
        // 如果没有包含，则自动拼接（如果 $api 末尾没有 /，先补一个）
        $api = rtrim($api, '/') . $check_str;
    }

    $stmt = $conn->prepare("INSERT INTO video_apis (name, api_url) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $api);
    $stmt->execute();

    // 插入成功，重定向到 /admin
    header('Location: /admin');
    exit;
} else {
    echo '缺少参数';
}
?>
