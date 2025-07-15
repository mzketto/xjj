<?php
// install.php 安装脚本示例

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 接收表单提交的数据
    $dbhost = trim($_POST['dbhost'] ?? '');
    $dbuser = trim($_POST['dbuser'] ?? '');
    $dbpass = trim($_POST['dbpass'] ?? '');
    $dbname = trim($_POST['dbname'] ?? '');

    $admin_user = trim($_POST['admin_user'] ?? '');
    $admin_pass = trim($_POST['admin_pass'] ?? '');
    $admin_pass_confirm = trim($_POST['admin_pass_confirm'] ?? '');

    // 简单验证
    if (!$dbhost || !$dbuser || !$dbname) {
        die('数据库连接信息不能为空');
    }
    if (!$admin_user || !$admin_pass) {
        die('管理员账号密码不能为空');
    }
    if ($admin_pass !== $admin_pass_confirm) {
        die('管理员密码和确认密码不一致');
    }

    // 连接数据库
    $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
    if ($conn->connect_errno) {
        die("数据库连接失败: " . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');

    // 创建 config 表
    $sql_config = "CREATE TABLE IF NOT EXISTS `config` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `key_name` VARCHAR(100) NOT NULL UNIQUE,
        `value` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    if (!$conn->query($sql_config)) {
        die("创建 config 表失败: " . $conn->error);
    }

    // 创建 video_apis 表
    $sql_apis = "CREATE TABLE IF NOT EXISTS `video_apis` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `api_url` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    if (!$conn->query($sql_apis)) {
        die("创建 video_apis 表失败: " . $conn->error);
    }

    // 创建 users 管理员表
    $sql_users = "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    if (!$conn->query($sql_users)) {
        die("创建 users 表失败: " . $conn->error);
    }

    // 插入管理员用户（密码使用 password_hash 加密）
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $admin_user);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        $stmt->close();

        $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
        $stmt2 = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt2->bind_param('ss', $admin_user, $hashed_password);
        $stmt2->execute();
        $stmt2->close();
    } else {
        $stmt->close();
    }

    // 插入默认解析地址配置（如果不存在）
    $check = $conn->query("SELECT id FROM config WHERE key_name='player_parse_url' LIMIT 1");
    if ($check->num_rows == 0) {
        $defaultParseUrl = 'http://your_default_parse_url.com/parse?url=';
        $stmt3 = $conn->prepare("INSERT INTO config (key_name, value) VALUES (?, ?)");
        $key = 'player_parse_url';
        $value = $defaultParseUrl;
        $stmt3->bind_param('ss', $key, $value);
        $stmt3->execute();
        $stmt3->close();
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

    // 随机生成一个 20 字符的文件名
    $random_filename = generate_random_string(20) . '.php';

    // 修改文件名（这里假设我们要重命名 'install.php' 文件为随机生成的文件名）
    if (!rename(__DIR__ . '/install.php', __DIR__ . '/' . $random_filename)) {
        die('无法重命名 install.php 文件');
    }

    echo "安装完成，表创建成功，管理员账号已添加。<br>";
    echo "请删除install.php 以保障安全。<br>";
    echo "安装脚本已重命名为：$random_filename <br>";
    echo '<a href="admin/login.php">前往登录</a>';
    exit;
}

// 生成随机字符串的函数
function generate_random_string($length = 20) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $characters_length = strlen($characters);
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, $characters_length - 1)];
    }
    return $random_string;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>安装向导</title>
<style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif; background:#f0f0f0; }
    form { max-width: 400px; margin: 50px auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);}
    label { display:block; margin-top:15px; font-weight:600; }
    input[type=text], input[type=password] { width:100%; padding:8px; margin-top:6px; border:1px solid #ccc; border-radius:6px; }
    button { margin-top:20px; width:100%; padding:10px; background:#007aff; border:none; color:#fff; font-weight:700; border-radius:8px; cursor:pointer; }
</style>
</head>
<body>
<h2 style="text-align:center;">安装向导</h2>
<form method="POST">
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
