<?php
// 引入数据库连接配置文件
require '../config.php';

// 获取 POST 请求中的 name 和 api_url 参数，如果没有提供则默认为空字符串
$name = $_POST['name'] ?? '';     // 获取视频 API 名称
$api = $_POST['api_url'] ?? '';   // 获取视频 API URL

// 判断 name 和 api 是否都提供了有效的值
if ($name && $api) {
    // 判断 $api 是否已经包含 '/api.php/provide/vod/at/json/?ac=detail' 这个字符串
    $check_str = '/api.php/provide/vod/at/json/?ac=detail';

    // strpos() 用来检查 $api 中是否包含 $check_str 子字符串
    if (strpos($api, $check_str) === false) {  // 如果没有找到该字符串（返回 false）
        // 如果 $api 没有包含该字符串，则自动将其添加到 $api 末尾
        // rtrim() 函数会去除字符串末尾的斜杠，确保只在没有斜杠时添加
        $api = rtrim($api, '/') . $check_str;  // 如果 $api 末尾没有斜杠，就添加
    }

    // 使用准备语句进行 SQL 插入操作，防止 SQL 注入攻击
    $stmt = $conn->prepare("INSERT INTO video_apis (name, api_url) VALUES (?, ?)");  // SQL 插入语句
    // 将参数绑定到 SQL 语句中，'ss' 表示两个字符串类型的参数
    $stmt->bind_param("ss", $name, $api);  // 绑定 name 和 api 到 SQL 语句
    $stmt->execute();  // 执行 SQL 语句，插入数据到数据库中

    // 插入成功后，使用 header() 函数重定向到 /admin 页面
    header('Location: ../admin');  // 重定向到管理后台页面
    exit;  // 确保程序在重定向后停止执行后续代码
} else {
    // 如果没有提供 name 或 api，则输出错误信息
    echo '缺少参数';  // 输出提示信息，告诉用户参数缺失
}
?>
