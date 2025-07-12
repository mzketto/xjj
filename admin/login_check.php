<?php
session_start();
require '../config.php';  // 请确保路径正确，且数据库连接成功

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $_SESSION['error'] = '用户名和密码不能为空';
        header('Location: login.php');
        exit;
    }

    // 查询数据库
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // 登录成功，保存session
            $_SESSION['user'] = ['id' => $row['id'], 'username' => $username];
            header('Location: index.php'); // 后台首页
            exit;
        }
    }

    $_SESSION['error'] = '用户名或密码错误';
    header('Location: login.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}
