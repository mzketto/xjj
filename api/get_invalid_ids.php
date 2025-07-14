<?php
header('Content-Type: application/json');
require '../config.php';
 // 连接数据库，变量 $conn

$invalidEntries = [];

// 查询数据库所有无效日志
$sql = "SELECT api_name, invalid_id FROM invalid_logs ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $invalidEntries[] = [
            'api_name' => $row['api_name'],
            'id' => intval($row['invalid_id'])
        ];
    }
}

echo json_encode([
    'code' => 1,
    'invalid_entries' => $invalidEntries,
]);
