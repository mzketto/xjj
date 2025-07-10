<?php
require '../config.php';

$id = $_POST['id'] ?? 0;
$name = $_POST['name'] ?? '';
$api = $_POST['api_url'] ?? '';

if ($id && $name && $api) {
    $check_str = '/api.php/provide/vod/at/json/?ac=detail';

    if (strpos($api, $check_str) === false) {
        $api = rtrim($api, '/') . $check_str;
    }

    $stmt = $conn->prepare("UPDATE video_apis SET name=?, api_url=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $api, $id);
    $stmt->execute();

    // 更新成功，跳转到 /admin
    header('Location: /admin');
    exit;
} else {
    echo '缺少参数';
}
?>
