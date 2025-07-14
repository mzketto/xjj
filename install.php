<?php
// install.php 安装脚本示例

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 接收表单提交的数据
    $dbhost = trim($_POST['dbhost'] ?? '');
    $dbuser = trim($_POST['dbuser'] ?? '');
    $dbpass = trim($_POST['dbpass'] ?? '');
    $dbname = trim($_POST['dbname'] ?? '');

    // 简单验证
    if (!$dbhost || !$dbuser || !$dbname) {
        die('数据库连接信息不能为空');
    }

    // 连接数据库
    $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
    if ($conn->connect_errno) {
        die("数据库连接失败: " . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');

    // 创建 invalid_logs 表
    $sql_invalid_logs = "CREATE TABLE IF NOT EXISTS `invalid_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `api_name` VARCHAR(100) NOT NULL,
        `invalid_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    if (!$conn->query($sql_invalid_logs)) {
        die("创建 invalid_logs 表失败: " . $conn->error);
    }

    // 创建 visit_logs 表
    $sql_visit_logs = "CREATE TABLE IF NOT EXISTS `visit_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `date` DATE NOT NULL,
        `time` TIME NOT NULL,
        `ip` VARCHAR(45) NOT NULL,
        `isp` VARCHAR(100) NOT NULL,
        `geo` TEXT NOT NULL,
        `device` VARCHAR(50) NOT NULL,
        `api_name` VARCHAR(100) NOT NULL,
        `vod_name` VARCHAR(255) NOT NULL,
        `vod_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    if (!$conn->query($sql_visit_logs)) {
        die("创建 visit_logs 表失败: " . $conn->error);
    }

    // 创建 users 表
    $sql_users = "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    if (!$conn->query($sql_users)) {
        die("创建 users 表失败: " . $conn->error);
    }

    // 创建 video_apis 表
    $sql_video_apis = "CREATE TABLE IF NOT EXISTS `video_apis` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `api_url` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    if (!$conn->query($sql_video_apis)) {
        die("创建 video_apis 表失败: " . $conn->error);
    }

    // 生成 config.php 文件内容
    $config_content = <<<PHP
<?php
// 自动生成的数据库配置文件

\$dbhost = '$dbhost';
\$dbuser = '$dbuser';
\$dbpass = '$dbpass';
\$dbname = '$dbname';

\$conn = new mysqli(\$dbhost, \$dbuser, \$dbpass, \$dbname);
if (\$conn->connect_errno) {
    die("数据库连接失败: " . \$conn->connect_error);
}
\$conn->set_charset('utf8mb4');
PHP;

    // 写入 config.php，建议写入项目根目录或指定目录
    $config_file_path = __DIR__ . '/config.php';
    if (file_put_contents($config_file_path, $config_content) === false) {
        die('生成 config.php 文件失败，请检查目录权限');
    }

    // 生成一个随机 24 位字符作为新文件名
    $new_filename = bin2hex(random_bytes(12)) . '.php';

    // 重命名 install.php 为新生成的文件名
    $old_filename = __FILE__; // 当前脚本文件的路径
    if (!rename($old_filename, __DIR__ . '/' . $new_filename)) {
        die('重命名安装脚本失败，请检查目录权限');
    }

    echo "安装完成，表创建成功，数据库配置文件生成成功。<br>";
    echo "安装脚本已重命名为：$new_filename<br>";
    echo "请删除或重命名 install.php 以保障安全。<br>";
    echo '<a href="admin/login.php">前往登录</a>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>安装向导</title>
<!-- 引入 Google 字体（Roboto）和苹果系统字体 -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
<style>
    body { 
        font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
        background: #f9f9f9;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    form { 
        background: #fff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 450px;
        box-sizing: border-box;
    }

    h2 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
    }

    label { 
        display: block;
        margin: 15px 0 5px;
        font-weight: 500;
        color: #555;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 12px;
        margin: 8px 0;
        border-radius: 8px;
        border: 1px solid #ddd;
        background-color: #f5f5f5;
        font-size: 16px;
        color: #333;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
        border-color: #007aff;
        outline: none;
    }

    button {
        background: #007aff;
        color: white;
        font-size: 16px;
        padding: 12px;
        width: 100%;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 700;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #005bb5;
    }

    hr {
        border: 1px solid #ddd;
        margin: 20px 0;
    }

    a {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #007aff;
        font-weight: 600;
    }

    a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<form method="POST">
    <h2>安装向导</h2>
    <label>数据库主机</label>
    <input type="text" name="dbhost" value="localhost" required />

    <label>数据库用户名</label>
    <input type="text" name="dbuser" required />

    <label>数据库密码</label>
    <input type="password" name="dbpass" />

    <label>数据库名</label>
    <input type="text" name="dbname" required />

    <hr>

    <label>管理员用户名</label>
    <input type="text" name="admin_user" required />

    <label>管理员密码</label>
    <input type="password" name="admin_pass" required />

    <label>确认管理员密码</label>
    <input type="password" name="admin_pass_confirm" required />

    <button type="submit">开始安装</button>
</form>
</body>
</html>
