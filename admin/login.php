<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: index.php'); // 登录后主页
    exit;
}
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8" />
<title>后台登录</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif; background:#f5f6fa; }
  .login-box { width: 320px; margin: 100px auto; background:#fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
  input { width: 100%; padding: 10px; margin-bottom: 15px; border:1.5px solid #ddd; border-radius: 10px; }
  button { width: 100%; padding: 12px; background: #007aff; color: white; border: none; border-radius: 14px; font-size: 1rem; cursor: pointer; }
  .error { color: red; margin-bottom: 15px; }
</style>
</head>
<body>
<div class="login-box">
  <h2>后台登录</h2>
  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST" action="login_check.php">
    <input type="text" name="username" placeholder="用户名" required autofocus />
    <input type="password" name="password" placeholder="密码" required />
    <button type="submit">登录</button>
  </form>
</div>
</body>
</html>
