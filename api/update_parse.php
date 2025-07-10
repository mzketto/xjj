<?php
require '../config.php';
$parse = $_POST['parse_url'] ?? '';
if ($parse) {
  $stmt = $conn->prepare("REPLACE INTO config (key_name, value) VALUES ('player_parse_url', ?)");
  $stmt->bind_param("s", $parse);
  $stmt->execute();
  echo 'ok';
} else echo '缺少参数';
?>
