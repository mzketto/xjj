<?php
require '../config.php';

$id = $_POST['id'] ?? 0;

if ($id) {
  $conn->query("DELETE FROM video_apis WHERE id=" . intval($id));

  // 删除成功，重定向到 /admin
  header('Location: /admin');
  exit;
} else {
  echo '缺少参数';
}
?>
