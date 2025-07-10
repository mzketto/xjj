<?php
$host = 'localhost';
$dbname = '数据库名';
$user = '数据库用户';
$pass = '数据库密码';
$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset("utf8mb4");
if ($conn->connect_error) die("数据库连接失败: " . $conn->connect_error);
?>
